<?php

namespace WPOddsComparison\Blocks;

use WPOddsComparison\Odds\Odds_Service;
use WPOddsComparison\Admin\Settings_Page;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Odds_Comparison_Block {

	private $odds_service;

	public function __construct( Odds_Service $odds_service ) {
		$this->odds_service = $odds_service;
	}

	public function register() {
		add_action( 'init', array( $this, 'register_block_type' ) );
	}

	public function register_block_type() {
		wp_register_script(
			'wpodds-block',
			WPODDS_PLUGIN_URL . 'assets/block/index.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'wp-i18n' ),
			WPODDS_VERSION,
			true
		);

		$bookmakers = array();
		foreach ( $this->odds_service->get_available_bookmakers() as $id => $meta ) {
			$bookmakers[] = array(
				'id'   => $id,
				'name' => $meta['name'] ?? $id,
			);
		}

		wp_localize_script(
			'wpodds-block',
			'wpoddsBlockData',
			array(
				'bookmakers' => $bookmakers,
			)
		);

		wp_register_style(
			'wpodds-block-editor',
			WPODDS_PLUGIN_URL . 'assets/block/editor.css',
			array( 'wp-edit-blocks' ),
			WPODDS_VERSION
		);

		wp_register_style(
			'wpodds-block-frontend',
			WPODDS_PLUGIN_URL . 'assets/block/style.css',
			array(),
			WPODDS_VERSION
		);

		register_block_type(
			'wpodds/odds-comparison',
			array(
				'editor_script'   => 'wpodds-block',
				'editor_style'    => 'wpodds-block-editor',
				'style'           => 'wpodds-block-frontend',
				'render_callback' => array( $this, 'render' ),
				'attributes'      => array(
					'eventSlug'  => array(
						'type'    => 'string',
						'default' => '',
					),
					'bookmakers' => array(
						'type'    => 'array',
						'default' => array(),
						'items'   => array(
							'type' => 'string',
						),
					),
					'markets'    => array(
						'type'    => 'array',
						'default' => array(),
						'items'   => array(
							'type' => 'string',
						),
					),
					'oddsFormat' => array(
						'type'    => 'string',
						'default' => 'decimal',
					),
				),
			)
		);
	}

	public function render( $attributes ) {
		$event_slug  = isset( $attributes['eventSlug'] ) ? sanitize_title( $attributes['eventSlug'] ) : '';
		$odds_format = isset( $attributes['oddsFormat'] ) ? sanitize_text_field( $attributes['oddsFormat'] ) : 'decimal';

		if ( empty( $event_slug ) ) {
			return '<p class="wpodds-message">' . esc_html__( 'Odds comparison: please set an event slug in the block settings.', 'wp-odds-comparison' ) . '</p>';
		}

		$settings         = get_option( Settings_Page::OPTION_KEY );
		$global_bookmakers = isset( $settings['bookmakers'] ) ? (array) $settings['bookmakers'] : array();
		$global_markets    = isset( $settings['markets'] ) ? (array) $settings['markets'] : array();

		$bookmakers = ! empty( $attributes['bookmakers'] ) ? (array) $attributes['bookmakers'] : $global_bookmakers;
		$markets    = ! empty( $attributes['markets'] ) ? (array) $attributes['markets'] : $global_markets;

		if ( empty( $bookmakers ) || empty( $markets ) ) {
			return '<p class="wpodds-message">' . esc_html__( 'Odds comparison: please enable bookmakers and configure markets in Odds Comparison settings (or set them in the block).', 'wp-odds-comparison' ) . '</p>';
		}

		$odds_data  = $this->odds_service->get_odds( $event_slug, $bookmakers, $markets, $odds_format );
		$bookmaker_meta = $this->odds_service->get_available_bookmakers();

		$has_any_rows = false;
		foreach ( $markets as $market ) {
			if ( ! empty( $odds_data[ $market ] ) ) {
				$has_any_rows = true;
				break;
			}
		}

		if ( ! $has_any_rows ) {
			return '<p class="wpodds-message">' . esc_html__( 'Odds are currently unavailable for this event. Please try again shortly.', 'wp-odds-comparison' ) . '</p>';
		}

		ob_start();
		?>
		<div class="wpodds-table-wrapper" data-event="<?php echo esc_attr( $event_slug ); ?>">
			<?php foreach ( $markets as $market ) : ?>
				<?php if ( empty( $odds_data[ $market ] ) ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<div class="wpodds-market">
					<h3 class="wpodds-market__title">
						<?php echo esc_html( $market ); ?>
					</h3>
					<table class="wpodds-table">
						<thead>
						<tr>
							<th class="wpodds-table__selection"><?php esc_html_e( 'Selection', 'wp-odds-comparison' ); ?></th>
							<?php foreach ( $bookmakers as $bookmaker_id ) : ?>
								<th class="wpodds-table__bookmaker">
									<?php echo esc_html( $bookmaker_meta[ $bookmaker_id ]['name'] ?? $bookmaker_id ); ?>
								</th>
							<?php endforeach; ?>
						</tr>
						</thead>
						<tbody>
						<?php foreach ( $odds_data[ $market ] as $selection => $bookmaker_odds ) : ?>
							<tr>
								<td class="wpodds-table__selection">
									<?php echo esc_html( $selection ); ?>
								</td>
								<?php foreach ( $bookmakers as $bookmaker_id ) : ?>
									<?php $cell = $bookmaker_odds[ $bookmaker_id ] ?? null; ?>
									<td class="wpodds-table__odd">
										<?php if ( $cell ) : ?>
											<?php echo esc_html( $cell['formatted'] ); ?>
										<?php else : ?>
											<span class="wpodds-table__odd--na"><?php esc_html_e( 'N/A', 'wp-odds-comparison' ); ?></span>
										<?php endif; ?>
									</td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endforeach; ?>
		</div>
		<?php

		return ob_get_clean();
	}
}
