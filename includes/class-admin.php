<?php
namespace Melk\SocialKitByMelk;

defined( 'ABSPATH' ) || exit;

/**
 * Tela Configurações > Social Kit: tipos de conteúdo habilitados e o card
 * "Outros plugins by Melk" (propaganda cruzada não-forçada, seção 5 de
 * 02-estrutura-do-projeto.md).
 */
class Admin {

	protected static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	protected function __construct() {
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	public function add_settings_page() {
		add_options_page(
			__( 'Social Kit', 'social-kit-by-melk' ),
			__( 'Social Kit', 'social-kit-by-melk' ),
			'manage_options',
			'social-kit-by-melk',
			[ $this, 'render_settings_page' ]
		);
	}

	public function register_settings() {
		register_setting( 'skbm_settings', 'skbm_enabled_post_types', [
			'type'              => 'array',
			'sanitize_callback' => [ $this, 'sanitize_post_types' ],
			'default'           => [ 'post' ],
		] );
	}

	public function sanitize_post_types( $value ) {
		if ( ! is_array( $value ) ) {
			return [];
		}

		return array_values( array_map( 'sanitize_key', $value ) );
	}

	public function enqueue_assets( $hook ) {
		if ( 'settings_page_social-kit-by-melk' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'skbm-admin', SKBM_PLUGIN_URL . 'assets/css/admin.css', [], SKBM_VERSION );
	}

	public function render_settings_page() {
		$enabled    = Social_Config::enabled_post_types();
		$post_types = get_post_types( [ 'public' => true ], 'objects' );

		require SKBM_PLUGIN_DIR . 'admin/settings-page.php';
	}

	public function other_plugins() {
		$plugins = [
			[
				'name'        => 'URL Shortener by Melk',
				'description' => __( 'Gera URLs curtas para seus posts automaticamente. Instale-o e o Social Kit passa a usar o link curto nas legendas automaticamente — sem configuração adicional.', 'social-kit-by-melk' ),
				'url'         => 'https://github.com/Melksedeque/url-shortener',
			],
		];

		return apply_filters( 'skbm_other_plugins', $plugins );
	}
}
