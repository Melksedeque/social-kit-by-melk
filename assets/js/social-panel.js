/**
 * Painel "Social Kit" no editor (Gutenberg). JS puro, sem build step e sem
 * bibliotecas via CDN (diretriz do WordPress.org) — usa só os pacotes @wordpress
 * já registrados pelo core (wp-element, wp-components, wp-data, wp-plugins...).
 *
 * A contagem ponderada de caracteres (URL = 23, CJK/emoji = 2) replica a
 * lógica de includes/class-social-counter.php para o contador refletir em
 * tempo real o que o PHP vai validar.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var registerPlugin = wp.plugins.registerPlugin;
	var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var Button = wp.components.Button;
	var withSelect = wp.data.withSelect;
	var withDispatch = wp.data.withDispatch;
	var compose = wp.compose.compose;
	var __ = wp.i18n.__;

	var CONFIG = window.skbmPanelData || {};
	var LIMITS = CONFIG.limits || { label: 20, card_title: 30, card_text: 170, caption: 280 };

	var DOUBLE_WIDTH_RANGES = [
		[ 0x1100, 0x115F ], [ 0x2E80, 0xA4CF ], [ 0xAC00, 0xD7A3 ],
		[ 0xF900, 0xFAFF ], [ 0xFF00, 0xFF60 ], [ 0xFFE0, 0xFFE6 ],
		[ 0x2600, 0x27BF ], [ 0x1F1E6, 0x1F1FF ], [ 0x1F300, 0x1FAFF ]
	];

	function isDoubleWidth( char ) {
		var code = char.codePointAt( 0 );

		for ( var i = 0; i < DOUBLE_WIDTH_RANGES.length; i++ ) {
			if ( code >= DOUBLE_WIDTH_RANGES[ i ][ 0 ] && code <= DOUBLE_WIDTH_RANGES[ i ][ 1 ] ) {
				return true;
			}
		}

		return false;
	}

	function weightedCount( text ) {
		text = text || '';

		var urlPattern = /https?:\/\/[^\s]+/gi;
		var urls = text.match( urlPattern ) || [];
		var withoutUrls = text.replace( urlPattern, '' );
		var chars = Array.from( withoutUrls );

		var length = chars.reduce( function ( total, char ) {
			return total + ( isDoubleWidth( char ) ? 2 : 1 );
		}, 0 );

		return length + ( urls.length * 23 );
	}

	function counterClassName( count, limit ) {
		if ( count > limit ) {
			return 'skbm-panel-field__counter--danger';
		}

		if ( count >= limit * 0.9 ) {
			return 'skbm-panel-field__counter--warning';
		}

		return 'skbm-panel-field__counter--ok';
	}

	function copyToClipboard( text ) {
		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			navigator.clipboard.writeText( text || '' );
		}
	}

	function Counter( props ) {
		var count = props.weighted ? weightedCount( props.value ) : ( props.value || '' ).length;

		return el(
			'div',
			{ className: 'skbm-panel-field__counter ' + counterClassName( count, props.limit ) },
			count + ' / ' + props.limit
		);
	}

	function OutputField( props ) {
		var Control = props.multiline ? TextareaControl : TextControl;

		return el(
			'div',
			{ className: 'skbm-panel-field' },
			el( Control, {
				label: props.label,
				value: props.value || '',
				onChange: props.onChange,
			} ),
			el( Counter, { value: props.value, limit: props.limit, weighted: props.weighted } ),
			el(
				Button,
				{
					variant: 'secondary',
					isSmall: true,
					className: 'skbm-panel-field__copy',
					onClick: function () {
						copyToClipboard( props.value );
					},
				},
				__( 'Copiar', 'social-kit-by-melk' )
			)
		);
	}

	function SocialKitPanel( props ) {
		var meta = props.meta || {};
		var setMeta = props.setMeta;

		function updateOutput( key, value ) {
			var next = Object.assign( {}, meta );
			next[ key ] = value;
			next._skbm_locked = true;
			setMeta( next );
		}

		function updateInput( key, value ) {
			var next = Object.assign( {}, meta );
			next[ key ] = value;
			setMeta( next );
		}

		function regenerate() {
			if ( ! CONFIG.restUrl ) {
				return;
			}

			wp.apiFetch( {
				url: CONFIG.restUrl,
				method: 'POST',
				data: {
					post_id: props.postId,
					title: props.title,
					excerpt: props.excerpt,
					subject: meta._skbm_subject,
					hook: meta._skbm_hook,
				},
			} ).then( function ( response ) {
				setMeta( Object.assign( {}, meta, {
					_skbm_label: response.label,
					_skbm_card_title: response.card_title,
					_skbm_card_text: response.card_text,
					_skbm_caption_x: response.caption_x,
					_skbm_hashtags: response.hashtags,
					_skbm_locked: false,
				} ) );
			} );
		}

		function copyAll() {
			var hashtags = ( meta._skbm_hashtags || [] ).map( function ( tag ) {
				return '#' + tag;
			} ).join( ' ' );

			var all = [
				meta._skbm_label,
				meta._skbm_card_title,
				meta._skbm_card_text,
				meta._skbm_caption_x,
				hashtags,
			].filter( Boolean ).join( '\n\n' );

			copyToClipboard( all );
		}

		function openInX() {
			var text = meta._skbm_caption_x || '';
			window.open( 'https://x.com/intent/post?text=' + encodeURIComponent( text ), '_blank' );
		}

		return el(
			PluginDocumentSettingPanel,
			{ name: 'skbm-social-kit', title: __( 'Social Kit', 'social-kit-by-melk' ) },
			el( OutputField, {
				label: __( 'Rótulo', 'social-kit-by-melk' ),
				value: meta._skbm_label,
				limit: LIMITS.label,
				onChange: function ( value ) { updateOutput( '_skbm_label', value ); },
			} ),
			el( OutputField, {
				label: __( 'Título do card', 'social-kit-by-melk' ),
				value: meta._skbm_card_title,
				limit: LIMITS.card_title,
				onChange: function ( value ) { updateOutput( '_skbm_card_title', value ); },
			} ),
			el( OutputField, {
				label: __( 'Texto do card', 'social-kit-by-melk' ),
				value: meta._skbm_card_text,
				limit: LIMITS.card_text,
				multiline: true,
				onChange: function ( value ) { updateOutput( '_skbm_card_text', value ); },
			} ),
			el( OutputField, {
				label: __( 'Legenda (X)', 'social-kit-by-melk' ),
				value: meta._skbm_caption_x,
				limit: LIMITS.caption,
				multiline: true,
				weighted: true,
				onChange: function ( value ) { updateOutput( '_skbm_caption_x', value ); },
			} ),
			el( TextControl, {
				label: __( 'Assunto (opcional, substitui o título do post no card)', 'social-kit-by-melk' ),
				value: meta._skbm_subject || '',
				onChange: function ( value ) { updateInput( '_skbm_subject', value ); },
			} ),
			el( TextControl, {
				label: __( 'Gancho (opcional, abre a legenda)', 'social-kit-by-melk' ),
				value: meta._skbm_hook || '',
				onChange: function ( value ) { updateInput( '_skbm_hook', value ); },
			} ),
			el(
				'div',
				{ className: 'skbm-panel-actions' },
				el( Button, { variant: 'primary', isSmall: true, onClick: regenerate }, __( 'Regenerar', 'social-kit-by-melk' ) ),
				el( Button, { variant: 'secondary', isSmall: true, onClick: copyAll }, __( 'Copiar tudo', 'social-kit-by-melk' ) ),
				el( Button, { variant: 'secondary', isSmall: true, onClick: openInX }, __( 'Abrir no X', 'social-kit-by-melk' ) )
			)
		);
	}

	var SocialKitPanelWithData = compose(
		withSelect( function ( select ) {
			var editor = select( 'core/editor' );

			return {
				meta: editor.getEditedPostAttribute( 'meta' ) || {},
				postId: editor.getCurrentPostId(),
				title: editor.getEditedPostAttribute( 'title' ),
				excerpt: editor.getEditedPostAttribute( 'excerpt' ),
			};
		} ),
		withDispatch( function ( dispatch ) {
			return {
				setMeta: function ( meta ) {
					dispatch( 'core/editor' ).editPost( { meta: meta } );
				},
			};
		} )
	)( SocialKitPanel );

	registerPlugin( 'skbm-social-kit', {
		render: SocialKitPanelWithData,
	} );
} )( window.wp );
