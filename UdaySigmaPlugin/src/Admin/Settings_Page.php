<?php

namespace WPOddsComparison\Admin;
use WPOddsComparison\Odds\Odds_Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings_Page {

	const OPTION_KEY = 'wpodds_settings';

	private $odds_service;

	public function __construct( Odds_Service $odds_service ) {
		$this->odds_service = $odds_service;
	}

	public function register_settings() {

		register_setting(
			'wpodds_settings_group',
			self::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(
					'bookmakers' => array(),
					'markets'    => array(),
					'links'      => array(),
				),
			)
		);

		add_settings_section(
			'wpodds_main_section',
			__( 'Odds Comparison Settings', 'wp-odds-comparison' ),
			function () {
				echo '<p>' . esc_html__( 'Control which bookmakers and markets are shown and manage outbound links.', 'wp-odds-comparison' ) . '</p>';
			},
			'wpodds_settings_page'
		);

		add_settings_field(
			'wpodds_bookmakers',
			__( 'Enabled Bookmakers', 'wp-odds-comparison' ),
			array( $this, 'render_bookmakers_field' ),
			'wpodds_settings_page',
			'wpodds_main_section'
		);

		add_settings_field(
			'wpodds_markets',
			__( 'Markets', 'wp-odds-comparison' ),
			array( $this, 'render_markets_field' ),
			'wpodds_settings_page',
			'wpodds_main_section'
		);

		add_settings_field(
			'wpodds_links',
			__( 'Bookmaker Links', 'wp-odds-comparison' ),
			array( $this, 'render_links_field' ),
			'wpodds_settings_page',
			'wpodds_main_section'
		);
	}

	public function register_menu() {
		add_menu_page(
			__( 'Odds Comparison', 'wp-odds-comparison' ),
			__( 'Odds Comparison', 'wp-odds-comparison' ),
			'manage_options',
			'wpodds_settings_page',
			array( $this, 'render_page' ),
			'dashicons-chart-line',
			60
		);
	}

	public function sanitize_settings( $input ) {

		$output = array(
			'bookmakers' => array(),
			'markets'    => array(),
			'links'      => array(),
		);

		if ( ! empty( $input['bookmakers'] ) && is_array( $input['bookmakers'] ) ) {
			foreach ( $input['bookmakers'] as $bookmaker_id ) {
				$output['bookmakers'][] = sanitize_text_field( $bookmaker_id );
			}
		}

		if ( isset( $input['markets'] ) ) {

			$source = $input['markets'];

			if ( is_string( $source ) ) {
				$markets = explode( ',', $source );
			} elseif ( is_array( $source ) ) {
				$markets = $source;
			} else {
				$markets = array();
			}

			foreach ( $markets as $market ) {
				$market = trim( (string) $market );
				if ( $market !== '' ) {
					$output['markets'][] = sanitize_text_field( $market );
				}
			}
		}

		if ( ! empty( $input['links'] ) && is_array( $input['links'] ) ) {
			foreach ( $input['links'] as $bookmaker_id => $url ) {
				$output['links'][ sanitize_text_field( $bookmaker_id ) ] = esc_url_raw( $url );
			}
		}

		return $output;
	}

	public function render_page() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings    = get_option( self::OPTION_KEY );
		$bookmakers  = $this->odds_service->get_available_bookmakers();
		$markets_str = ! empty( $settings['markets'] )
			? implode( ', ', (array) $settings['markets'] )
			: '';
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Odds Comparison Settings', 'wp-odds-comparison' ); ?></h1>

			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'wpodds_settings_group' );
				do_settings_sections( 'wpodds_settings_page' );
				?>

				<h2><?php esc_html_e( 'Global Settings', 'wp-odds-comparison' ); ?></h2>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="wpodds_markets">
								<?php esc_html_e( 'Markets (comma-separated)', 'wp-odds-comparison' ); ?>
							</label>
						</th>
						<td>
							<input
								type="text"
								id="wpodds_markets"
								name="<?php echo esc_attr( self::OPTION_KEY ); ?>[markets]"
								class="regular-text"
								value="<?php echo esc_attr( $markets_str ); ?>"
							/>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	public function render_bookmakers_field() {

		$settings   = get_option( self::OPTION_KEY );
		$enabled    = isset( $settings['bookmakers'] ) ? (array) $settings['bookmakers'] : array();
		$bookmakers = $this->odds_service->get_available_bookmakers();

		foreach ( $bookmakers as $id => $bookmaker ) {
			?>
			<label>
				<input
					type="checkbox"
					name="<?php echo esc_attr( self::OPTION_KEY ); ?>[bookmakers][]"
					value="<?php echo esc_attr( $id ); ?>"
					<?php checked( in_array( $id, $enabled, true ) ); ?>
				/>
				<?php echo esc_html( $bookmaker['name'] ); ?>
			</label><br/>
			<?php
		}
	}

	public function render_markets_field() {
		echo '<p class="description">' .
			esc_html__( 'Configured in the Global Settings section below.', 'wp-odds-comparison' ) .
			'</p>';
	}

	public function render_links_field() {

		$settings   = get_option( self::OPTION_KEY );
		$links      = isset( $settings['links'] ) ? (array) $settings['links'] : array();
		$bookmakers = $this->odds_service->get_available_bookmakers();

		foreach ( $bookmakers as $id => $bookmaker ) {
			?>
			<p>
				<label for="wpodds_link_<?php echo esc_attr( $id ); ?>">
					<?php echo esc_html( $bookmaker['name'] ); ?>
				</label><br/>
				<input
					type="url"
					id="wpodds_link_<?php echo esc_attr( $id ); ?>"
					name="<?php echo esc_attr( self::OPTION_KEY ); ?>[links][<?php echo esc_attr( $id ); ?>]"
					value="<?php echo esc_attr( $links[ $id ] ?? '' ); ?>"
					class="regular-text"
				/>
			</p>
			<?php
		}
	}
}
