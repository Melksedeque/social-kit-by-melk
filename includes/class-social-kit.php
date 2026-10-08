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

	protected static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	protected function __construct() {
		add_action( 'init', [ $this, 'register_meta' ] );
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

	public function register_meta() {
		$auth_callback = function () {
			return current_user_can( 'edit_posts' );
		};

		$string_fields = [ 'label', 'card_title', 'card_text', 'caption_x', 'source_hash', 'version', 'subject', 'hook' ];

		foreach ( $string_fields as $field ) {
			register_post_meta( '', self::META_PREFIX . $field, [
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => $auth_callback,
			] );
		}

		register_post_meta( '', self::META_PREFIX . 'hashtags', [
			'type'          => 'array',
			'single'        => true,
			'show_in_rest'  => [
				'schema' => [
					'type'  => 'array',
					'items' => [ 'type' => 'string' ],
				],
			],
			'auth_callback' => $auth_callback,
		] );

		register_post_meta( '', self::META_PREFIX . 'locked', [
			'type'          => 'boolean',
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => $auth_callback,
		] );
	}

	public function maybe_generate( $post_id, $post ) {
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! $post instanceof \WP_Post || 'trash' === $post->post_status ) {
			return;
		}

		if ( ! in_array( $post->post_type, Social_Config::enabled_post_types(), true ) ) {
			return;
		}

		if ( get_post_meta( $post_id, self::META_PREFIX . 'locked', true ) ) {
			return;
		}

		$hash = $this->compute_source_hash( $post );

		if ( $hash === get_post_meta( $post_id, self::META_PREFIX . 'source_hash', true ) ) {
			return;
		}

		$url       = $this->resolve_url( $post_id );
		$generator = new Rule_Generator();
		$fields    = $generator->generate( $post, $url );

		foreach ( $fields as $key => $value ) {
			update_post_meta( $post_id, self::META_PREFIX . $key, $value );
		}

		update_post_meta( $post_id, self::META_PREFIX . 'source_hash', $hash );
		update_post_meta( $post_id, self::META_PREFIX . 'version', SKBM_VERSION );
	}

	protected function compute_source_hash( \WP_Post $post ) {
		$categories = wp_list_pluck( get_the_category( $post->ID ), 'term_id' );

		return md5( (string) wp_json_encode( [
			$post->post_title,
			$post->post_excerpt,
			$post->post_content,
			$categories,
			get_post_meta( $post->ID, self::META_PREFIX . 'subject', true ),
			get_post_meta( $post->ID, self::META_PREFIX . 'hook', true ),
		] ) );
	}

	/**
	 * Integração opcional com o URL Shortener by Melk: usa o link curto se o
	 * outro plugin estiver ativo, sem nunca depender dele (seção 4 da espec).
	 */
	public function resolve_url( $post_id ) {
		if ( function_exists( 'urlshbym_get_short_url_for_post' ) ) {
			$url = urlshbym_get_short_url_for_post( $post_id );

			if ( ! empty( $url ) ) {
				return $url;
			}
		}

		return get_permalink( $post_id );
	}

	public function enqueue_editor_assets() {
		$screen = get_current_screen();

		if ( ! $screen || ! in_array( $screen->post_type, Social_Config::enabled_post_types(), true ) ) {
			return;
		}

		wp_enqueue_script(
			'skbm-social-panel',
			SKBM_PLUGIN_URL . 'assets/js/social-panel.js',
			[ 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n', 'wp-compose', 'wp-api-fetch' ],
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
			'limits'  => Social_Config::limits( 'x' ),
			'restUrl' => esc_url_raw( rest_url( 'skbm/v1/regenerate' ) ),
		] );
	}

	public function register_rest_routes() {
		register_rest_route( 'skbm/v1', '/regenerate', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'handle_regenerate' ],
			'permission_callback' => function ( \WP_REST_Request $request ) {
				$post_id = (int) $request->get_param( 'post_id' );

				return $post_id && current_user_can( 'edit_post', $post_id );
			},
			'args'                => [
				'post_id' => [ 'required' => true ],
			],
		] );
	}

	public function handle_regenerate( \WP_REST_Request $request ) {
		$post_id = (int) $request->get_param( 'post_id' );
		$post    = get_post( $post_id );

		if ( ! $post ) {
			return new \WP_Error( 'skbm_invalid_post', __( 'Post inválido.', 'social-kit-by-melk' ), [ 'status' => 404 ] );
		}

		$title   = $request->get_param( 'title' );
		$excerpt = $request->get_param( 'excerpt' );
		$subject = $request->get_param( 'subject' );
		$hook    = $request->get_param( 'hook' );

		$virtual_post = clone $post;

		if ( null !== $title ) {
			$virtual_post->post_title = sanitize_text_field( $title );
		}

		if ( null !== $excerpt ) {
			$virtual_post->post_excerpt = sanitize_textarea_field( $excerpt );
		}

		if ( null !== $subject ) {
			update_post_meta( $post_id, self::META_PREFIX . 'subject', sanitize_text_field( $subject ) );
		}

		if ( null !== $hook ) {
			update_post_meta( $post_id, self::META_PREFIX . 'hook', sanitize_text_field( $hook ) );
		}

		$url       = $this->resolve_url( $post_id );
		$generator = new Rule_Generator();
		$fields    = $generator->generate( $virtual_post, $url );

		return rest_ensure_response( $fields );
	}
}
