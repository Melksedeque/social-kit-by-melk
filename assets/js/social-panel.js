/**
 * Painel "Social Kit" no editor (Gutenberg). JS puro, sem build step e sem
 * bibliotecas via CDN (diretriz do WordPress.org) — usa só os pacotes @wordpress
 * já registrados pelo core (wp-element, wp-components, wp-data, wp-plugins...).
 *
 * A contagem ponderada de caracteres (regras do X: URL = 23, emoji = 2,
 * faixas Unicode "leves" = 1, o resto = 2) replica a lógica de
 * includes/class-social-counter.php para o contador refletir em tempo real o
 * que o PHP vai validar. Mantenha as duas implementações em sincronia.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var useState = wp.element.useState;
	var registerPlugin = wp.plugins.registerPlugin;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var CheckboxControl = wp.components.CheckboxControl;
	var Button = wp.components.Button;
	var withSelect = wp.data.withSelect;
	var withDispatch = wp.data.withDispatch;
	var compose = wp.compose.compose;
	var __ = wp.i18n.__;

	// A partir do WP 6.6 o painel vive em wp.editor; wp.editPost fica como fallback (WP 6.2 a 6.5).
	var PluginDocumentSettingPanel = ( wp.editor && wp.editor.PluginDocumentSettingPanel ) || wp.editPost.PluginDocumentSettingPanel;

	var CONFIG = window.skbmPanelData || {};
	var LIMITS = CONFIG.limits || { label: 20, card_title: 30, card_text: 170, caption: 280 };

	var URL_WEIGHT = 23;
	var URL_PATTERN = /https?:\/\/[^\s]+/gi;

	// Faixas [início, fim] com peso 1. Todo o resto pesa 2 (igual ao PHP).
	var SINGLE_WEIGHT_RANGES = [
		[ 0x0000, 0x10FF ],
		[ 0x2000, 0x200D ],
		[ 0x2010, 0x201F ],
		[ 0x2032, 0x2037 ]
	];

	var EMOJI_BASE = '[\\u{2600}-\\u{27BF}\\u{2B00}-\\u{2BFF}\\u{1F300}-\\u{1FAFF}]';
	var EMOJI_MODIFIERS = '[\\u{FE0F}\\u{1F3FB}-\\u{1F3FF}]*';
	var EMOJI_PATTERN = '(?:[\\u{1F1E6}-\\u{1F1FF}]{2}|' + EMOJI_BASE + EMOJI_MODIFIERS + '(?:\\u{200D}' + EMOJI_BASE + EMOJI_MODIFIERS + ')*)';

	function charWeight( char ) {
		var code = char.codePointAt( 0 );

		for ( var i = 0; i < SINGLE_WEIGHT_RANGES.length; i++ ) {
			if ( code >= SINGLE_WEIGHT_RANGES[ i ][ 0 ] && code <= SINGLE_WEIGHT_RANGES[ i ][ 1 ] ) {
				return 1;
			}
		}

		return 2;
	}

	function weightedCount( text ) {
		text = text || '';

		var urls = text.match( URL_PATTERN ) || [];
		var rest = text.replace( URL_PATTERN, '' );

		var emojis = rest.match( new RegExp( EMOJI_PATTERN, 'gu' ) ) || [];
		rest = rest.replace( new RegExp( EMOJI_PATTERN, 'gu' ), '' );

		var length = Array.from( rest ).reduce( function ( total, char ) {
			return total + charWeight( char );
		}, 0 );

		return length + ( emojis.length * 2 ) + ( urls.length * URL_WEIGHT );
	}

	function plainCount( text ) {
		return Array.from( text || '' ).length;
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
			return navigator.clipboard.writeText( text || '' );
		}

		return Promise.reject( new Error( 'clipboard unavailable' ) );
	}

	function Counter( props ) {
		var count = props.weighted ? weightedCount( props.value ) : plainCount( props.value );

		return el(
			'div',
			{ className: 'skbm-panel-field__counter ' + counterClassName( count, props.limit ) },
			count + ' / ' + props.limit
		);
	}

	function OutputField( props ) {
		var Control = props.multiline ? TextareaControl : TextControl;
		var copiedState = useState( false );
		var copied = copiedState[ 0 ];
		var setCopied = copiedState[ 1 ];

		function onCopy() {
			copyToClipboard( props.value ).then( function () {
				setCopied( true );
				window.setTimeout( function () {
					setCopied( false );
				}, 1500 );
			} ).catch( function () {} );
		}

		return el(
			'div',
			{ className: 'skbm-panel-field' },
			el( Control, {
				label: props.label,
				value: props.value || '',
				onChange: props.onChange,
				__nextHasNoMarginBottom: true,
			} ),
			el( Counter, { value: props.value, limit: props.limit, weighted: props.weighted } ),
			el(
				Button,
				{
					variant: 'secondary',
					size: 'small',
					className: 'skbm-panel-field__copy',
					onClick: onCopy,
				},
				copied ? __( 'Copied!', 'social-kit-by-melk' ) : __( 'Copiar', 'social-kit-by-melk' )
			)
		);
	}

	function SocialKitPanel( props ) {
		var meta = props.meta || {};
		var setMeta = props.setMeta;
		var busyState = useState( false );
		var busy = busyState[ 0 ];
		var setBusy = busyState[ 1 ];
		var errorState = useState( '' );
		var error = errorState[ 0 ];
		var setError = errorState[ 1 ];

		// Envia só a chave alterada: o editor mescla o resto, sem sobrescrever com valores antigos.
		function updateOutput( key, value ) {
			var change = { _skbm_locked: true };
			change[ key ] = value;
			setMeta( change );
		}

		function updateInput( key, value ) {
			var change = {};
			change[ key ] = value;
			setMeta( change );
		}

		function regenerate() {
			setBusy( true );
			setError( '' );

			wp.apiFetch( {
				path: '/skbm/v1/regenerate',
				method: 'POST',
				data: {
					post_id: props.postId,
					title: props.title,
					excerpt: props.excerpt,
					subject: meta._skbm_subject,
					hook: meta._skbm_hook,
				},
			} ).then( function ( response ) {
				setMeta( {
					_skbm_label: response.label,
					_skbm_card_title: response.card_title,
					_skbm_card_text: response.card_text,
					_skbm_caption_x: response.caption_x,
					_skbm_hashtags: response.hashtags,
					_skbm_locked: false,
				} );
				setBusy( false );
			} ).catch( function ( err ) {
				setError( ( err && err.message ) ? err.message : __( 'Could not regenerate. Please try again.', 'social-kit-by-melk' ) );
				setBusy( false );
			} );
		}

		function copyAll() {
			// A legenda já inclui as hashtags, então não são repetidas aqui.
			var all = [
				meta._skbm_label,
				meta._skbm_card_title,
				meta._skbm_card_text,
				meta._skbm_caption_x,
			].filter( Boolean ).join( '\n\n' );

			copyToClipboard( all ).catch( function () {} );
		}

		function openInX() {
			var text = meta._skbm_caption_x || '';
			window.open( 'https://x.com/intent/post?text=' + encodeURIComponent( text ), '_blank', 'noopener,noreferrer' );
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
				__nextHasNoMarginBottom: true,
			} ),
			el( TextControl, {
				label: __( 'Gancho (opcional, abre a legenda)', 'social-kit-by-melk' ),
				value: meta._skbm_hook || '',
				onChange: function ( value ) { updateInput( '_skbm_hook', value ); },
				__nextHasNoMarginBottom: true,
			} ),
			el( CheckboxControl, {
				label: __( 'Lock editing (keep my changes)', 'social-kit-by-melk' ),
				help: __( 'When locked, saving the post does not overwrite these fields.', 'social-kit-by-melk' ),
				checked: !! meta._skbm_locked,
				onChange: function ( value ) { updateInput( '_skbm_locked', !! value ); },
				__nextHasNoMarginBottom: true,
			} ),
			error ? el( 'p', { className: 'skbm-panel-error', role: 'alert' }, error ) : null,
			el(
				'div',
				{ className: 'skbm-panel-actions' },
				el( Button, { variant: 'primary', size: 'small', isBusy: busy, disabled: busy, onClick: regenerate }, __( 'Regenerar', 'social-kit-by-melk' ) ),
				el( Button, { variant: 'secondary', size: 'small', onClick: copyAll }, __( 'Copiar tudo', 'social-kit-by-melk' ) ),
				el( Button, { variant: 'secondary', size: 'small', onClick: openInX }, __( 'Abrir no X', 'social-kit-by-melk' ) )
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
