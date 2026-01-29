( function( blocks, element, components, editor, i18n ) {
	const el = element.createElement;
	const __ = i18n.__;
	const InspectorControls = editor.InspectorControls;
	const PanelBody = components.PanelBody;
	const TextControl = components.TextControl;
	const SelectControl = components.SelectControl;
	const CheckboxControl = components.CheckboxControl;

	blocks.registerBlockType( 'wpodds/odds-comparison', {
		title: __( 'Odds Comparison', 'wp-odds-comparison' ),
		icon: 'chart-line',
		category: 'widgets',
		attributes: {
			eventSlug: {
				type: 'string',
				default: '',
			},
			bookmakers: {
				type: 'array',
				default: [],
				items: {
					type: 'string',
				},
			},
			markets: {
				type: 'array',
				default: [],
				items: {
					type: 'string',
				},
			},
			oddsFormat: {
				type: 'string',
				default: 'decimal',
			},
		},

		edit: function( props ) {
			const attrs = props.attributes;

			// Bookmaker options will be hydrated via localized data if needed.
			const availableBookmakers = ( window.wpoddsBlockData && window.wpoddsBlockData.bookmakers ) || [];

			function toggleBookmaker( id ) {
				const current = attrs.bookmakers.slice();
				const index = current.indexOf( id );

				if ( index === -1 ) {
					current.push( id );
				} else {
					current.splice( index, 1 );
				}

				props.setAttributes( { bookmakers: current } );
			}

			function onMarketsChange( value ) {
				const markets = value
					.split( ',' )
					.map( function( m ) {
						return m.trim();
					} )
					.filter( function( m ) {
						return m.length > 0;
					} );

				props.setAttributes( { markets: markets } );
			}

			const marketsString = attrs.markets.join( ', ' );

			return [
				el(
					InspectorControls,
					{ key: 'inspector' },
					el(
						PanelBody,
						{
							title: __( 'Odds Settings', 'wp-odds-comparison' ),
							initialOpen: true,
						},
						el( TextControl, {
							label: __( 'Event slug', 'wp-odds-comparison' ),
							help: __( 'Use a consistent slug for the event, e.g. team-a-vs-team-b.', 'wp-odds-comparison' ),
							value: attrs.eventSlug,
							onChange: function( value ) {
								props.setAttributes( { eventSlug: value } );
							},
						} ),
						el( SelectControl, {
							label: __( 'Odds format', 'wp-odds-comparison' ),
							value: attrs.oddsFormat,
							options: [
								{ label: __( 'Decimal', 'wp-odds-comparison' ), value: 'decimal' },
								{ label: __( 'Fractional', 'wp-odds-comparison' ), value: 'fractional' },
								{ label: __( 'American', 'wp-odds-comparison' ), value: 'american' },
							],
							onChange: function( value ) {
								props.setAttributes( { oddsFormat: value } );
							},
						} ),
						el( 'div', { className: 'wpodds-inspector-section' },
							el( 'p', null, __( 'Bookmakers (override global selection):', 'wp-odds-comparison' ) ),
							availableBookmakers.map( function( bookmaker ) {
								return el( CheckboxControl, {
									key: bookmaker.id,
									label: bookmaker.name,
									checked: attrs.bookmakers.indexOf( bookmaker.id ) !== -1,
									onChange: function() {
										toggleBookmaker( bookmaker.id );
									},
								} );
							} )
						),
						el( TextControl, {
							label: __( 'Markets (comma-separated, overrides global)', 'wp-odds-comparison' ),
							value: marketsString,
							onChange: onMarketsChange,
						} )
					)
				),
				el(
					'div',
					{ className: props.className + ' wpodds-block-preview' },
					el( 'h3', null, __( 'Odds Comparison', 'wp-odds-comparison' ) ),
					attrs.eventSlug
						? el( 'p', null, __( 'Event:', 'wp-odds-comparison' ) + ' ' + attrs.eventSlug )
						: el( 'p', null, __( 'Set an event slug in the block settings.', 'wp-odds-comparison' ) ),
					el( 'p', null, __( 'Odds table will render on the front-end.', 'wp-odds-comparison' ) )
				),
			];
		},

		save: function() {
			// Rendered dynamically in PHP.
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.components, window.wp.editor || window.wp.blockEditor, window.wp.i18n );

