<?php
namespace Melk\SocialKitByMelk;

use Melk\SocialKitByMelk\Generators\Rule_Generator;

defined( 'ABSPATH' ) || exit;

/**
 * Orquestra o plugin: registra os post meta, gera os campos no save_post
 * (seção 8 da espec) e expõe a rota REST usada pelo botão "Regenerar" do
 * painel do editor.
 */
class Social_Kit {

	const META_PREFIX = '_skbm_';

	/**
	 * Campos de uma linha só (sanitize_text_field).
	 */
	const SINGLE_LINE_FIELDS = [ 'label', 'card_title', 'subject', 'hook' ];

	/**
	 * Campos que precisam preservar quebras de linha (sanitize_textarea_field).
	 */
	const MULTILINE_FIELDS = [ 'card_text', 'caption_x' ];

	protected static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	protected function __construct() {
		add_action( 'init', [ $this, 'register_meta' ], 20 );
		add_action( 'save_post', [ $this, 'maybe_generate' ], 20, 2 );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_assets' ] );
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
	}

	public static function activate() {
		add_option( 'skbm_enabled_post_types', [ 'post' ] );
	}

	public static function deactivate() {
		// Nada a limpar: as opções e os post meta ficam, para não perder dados em uma reativação.
	}

	/**
	 * Registra os metas só nos tipos de conteúdo habilitados (não expõe nada
	 * nos demais) e garante o suporte a custom-fields que a REST exige para
	 * mostrar metas de um tipo de post.
	 */
	public function register_meta() {
		$auth_callback = function ( $allowed, $meta_key, $post_id ) {
			return $post_id ? current_user_can( 'edit_post', $post_id ) : current_user_can( 'edit_posts' );
		};

		foreach ( Social_Config::enabled_post_types() as $post_type ) {
			if ( ! post_type_exists( $post_type ) ) {
				continue;
			}

			if ( ! post_type_supports( $post_type, 'custom-fields' ) ) {
				add_post_type_support( $post_type, 'custom-fields' );
			}

			foreach ( self::SINGLE_LINE_FIELDS as $field ) {
				register_post_meta( $post_type, self::META_PREFIX . $field, [
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => $auth_callback,
				] );
			}

			foreach ( self::MULTILINE_FIELDS as $field ) {
				register_post_meta( $post_type, self::META_PREFIX . $field, [
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_textarea_field',
					'auth_callback'     => $auth_callback,
				] );
			}

			register_post_meta( $post_type, self::META_PREFIX . 'hashtags', [
				'type'              => 'array',
				'single'            => true,
				'show_in_rest'      => [
					'schema' => [
						'type'  => 'array',
						'items' => [ 'type' => 'string' ],
					],
				],
				'sanitize_callback' => [ $this, 'sanitize_hashtags' ],
				'auth_callback'     => $auth_callback,
			] );

			register_post_meta( $post_type, self::META_PREFIX . 'locked', [
				'type'          => 'boolean',
				'single'        => true,
				'show_in_rest'  => true,
				'auth_callback' => $auth_callback,
			] );

			// Controle interno: gravado só pelo servidor, nunca exposto à REST.
			foreach ( [ 'source_hash', 'version' ] as $field ) {
				register_post_meta( $post_type, self::META_PREFIX . $field, [
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => false,
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => $auth_callback,
				] );
			}
		}
	}

	public function sanitize_hashtags( $value ) {
		if ( ! is_array( $value ) ) {
			return [];
		}

		return array_values( array_filter( array_map( 'sanitize_text_field', $value ) ) );
	}

	public function maybe_generate( $post_id, $post ) {
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! $post instanceof \WP_Post || in_array( $post->post_status, [ 'trash', 'auto-draft' ], true ) ) {
			return;
		}

		if ( ! in_array( $post->post_type, Social_Config::enabled_post_types(), true ) ) {
			return;
		}

		if ( get_post_meta( $post_id, self::META_PREFIX . 'locked', true ) ) {
			return;
		}

		$url  = $this->resolve_url( $post_id );
		$hash = $this->compute_source_hash( $post, $url );

		if ( $hash === get_post_meta( $post_id, self::META_PREFIX . 'source_hash', true ) ) {
			return;
		}

		$generator = new Rule_Generator();
		$fields    = $generator->generate( $post, $url );

		// update_post_meta() faz wp_unslash(); wp_slash() preserva barras invertidas do conteúdo.
		foreach ( $fields as $key => $value ) {
			update_post_meta( $post_id, self::META_PREFIX . $key, wp_slash( $value ) );
		}

		update_post_meta( $post_id, self::META_PREFIX . 'source_hash', $hash );
		update_post_meta( $post_id, self::META_PREFIX . 'version', SKBM_VERSION );
	}

	/**
	 * A URL entra no hash: um rascunho gera a legenda com ?p=ID, e ao publicar
	 * a URL muda (permalink ou link curto), o que força a regeneração.
	 */
	protected function compute_source_hash( \WP_Post $post, $url ) {
		$categories = wp_list_pluck( get_the_category( $post->ID ), 'term_id' );

		return md5( (string) wp_json_encode( [
			$post->post_title,
			$post->post_excerpt,
			$post->post_content,
			$categories,
			get_post_meta( $post->ID, self::META_PREFIX . 'subject', true ),
			get_post_meta( $post->ID, self::META_PREFIX . 'hook', true ),
			$url,
		] ) );
	}

	/**
	 * Integração opcional com o URL Shortener by Melk: usa o link curto se o
	 * outro plugin estiver ativo, sem nunca depender dele (seção 4 da espec).
	 */
	public function resolve_url( $post_id ) {
		$url = '';

		if ( function_exists( 'urlshbym_get_short_url_for_post' ) ) {
			$short = urlshbym_get_short_url_for_post( $post_id );

			if ( ! empty( $short ) && is_string( $short ) ) {
				$url = $short;
			}
		}

		if ( '' === $url ) {
			$permalink = get_permalink( $post_id );
			$url       = $permalink ? $permalink : '';
		}

		/**
		 * Permite que outro plugin troque a URL usada na legenda.
		 *
		 * @param string $url     URL resolvida (link curto, se disponível, ou permalink).
		 * @param int    $post_id ID do post.
		 */
		$filtered = apply_filters( 'skbm_resolve_url', $url, $post_id );

		return is_string( $filtered ) ? $filtered : $url;
	}

	public function enqueue_editor_assets() {
		$screen = get_current_screen();

		if ( ! $screen || ! in_array( $screen->post_type, Social_Config::enabled_post_types(), true ) ) {
			return;
		}

		wp_enqueue_script(
			'skbm-social-panel',
			SKBM_PLUGIN_URL . 'assets/js/social-panel.js',
			[ 'wp-plugins', 'wp-editor', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n', 'wp-compose', 'wp-api-fetch' ],
			SKBM_VERSION,
			true
		);

		wp_enqueue_style(
			'skbm-social-panel',
			SKBM_PLUGIN_URL . 'assets/css/social-panel.css',
			[],
			SKBM_VERSION
		);

		wp_localize_script( 'skbm-social-panel', 'skbmPanelData', [
			'limits' => Social_Config::limits( 'x' ),
		] );
	}

	public function register_rest_routes() {
		register_rest_route( 'skbm/v1', '/regenerate', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'handle_regenerate' ],
			'permission_callback' => function ( \WP_REST_Request $request ) {
				$post_id = absint( $request->get_param( 'post_id' ) );

				return $post_id && current_user_can( 'edit_post', $post_id );
			},
			'args'                => [
				'post_id' => [
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				],
				'title'   => [
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'excerpt' => [
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_textarea_field',
				],
				'subject' => [
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'hook'    => [
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		] );
	}

	/**
	 * Gera os campos em memória a partir do que está no editor (ainda não
	 * salvo) e devolve o resultado. Não grava nada no banco: quem persiste é
	 * o salvamento normal do post.
	 */
	public function handle_regenerate( \WP_REST_Request $request ) {
		$post_id = absint( $request->get_param( 'post_id' ) );
		$post    = get_post( $post_id );

		if ( ! $post ) {
			return new \WP_Error( 'skbm_invalid_post', __( 'Post inválido.', 'social-kit-by-melk' ), [ 'status' => 404 ] );
		}

		if ( ! in_array( $post->post_type, Social_Config::enabled_post_types(), true ) ) {
			return new \WP_Error( 'skbm_post_type_disabled', __( 'O Social Kit não está habilitado para este tipo de conteúdo.', 'social-kit-by-melk' ), [ 'status' => 400 ] );
		}

		$virtual_post = clone $post;
		$overrides    = [];

		if ( null !== $request->get_param( 'title' ) ) {
			$virtual_post->post_title = $request->get_param( 'title' );
		}

		if ( null !== $request->get_param( 'excerpt' ) ) {
			$virtual_post->post_excerpt = $request->get_param( 'excerpt' );
		}

		foreach ( [ 'subject', 'hook' ] as $key ) {
			if ( null !== $request->get_param( $key ) ) {
				$overrides[ $key ] = $request->get_param( $key );
			}
		}

		$generator = new Rule_Generator();
		$fields    = $generator->generate( $virtual_post, $this->resolve_url( $post_id ), $overrides );

		return rest_ensure_response( $fields );
	}
}
