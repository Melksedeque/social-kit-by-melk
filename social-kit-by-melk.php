<?php
/**
 * Plugin Name:       Social Kit by Melk
 * Plugin URI:        https://github.com/Melksedeque/social-kit-by-melk
 * Description:       Gera automaticamente título, texto e legenda para divulgar seus posts nas redes sociais (começando pelo X), com contador de caracteres e painel no editor.
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            Melk
 * Author URI:        https://github.com/Melksedeque
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       social-kit-by-melk
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'SKBM_VERSION', '1.0.0' );
define( 'SKBM_PLUGIN_FILE', __FILE__ );
define( 'SKBM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SKBM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

spl_autoload_register( function ( $class ) {
	$prefix = 'Melk\\SocialKitByMelk\\';

	if ( 0 !== strpos( $class, $prefix ) ) {
		return;
	}

	$relative = substr( $class, strlen( $prefix ) );

	// As classes dentro de sub-namespaces (ex.: Generators\) são carregadas via require_once explícito abaixo.
	if ( false !== strpos( $relative, '\\' ) ) {
		return;
	}

	$file = SKBM_PLUGIN_DIR . 'includes/class-' . strtolower( str_replace( '_', '-', $relative ) ) . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

require_once SKBM_PLUGIN_DIR . 'includes/generators/interface-generator.php';
require_once SKBM_PLUGIN_DIR . 'includes/generators/class-rule-generator.php';

register_activation_hook( SKBM_PLUGIN_FILE, [ 'Melk\\SocialKitByMelk\\Social_Kit', 'activate' ] );
register_deactivation_hook( SKBM_PLUGIN_FILE, [ 'Melk\\SocialKitByMelk\\Social_Kit', 'deactivate' ] );

add_action( 'plugins_loaded', function () {
	load_plugin_textdomain( 'social-kit-by-melk', false, dirname( plugin_basename( SKBM_PLUGIN_FILE ) ) . '/languages' );

	\Melk\SocialKitByMelk\Social_Kit::instance();
	\Melk\SocialKitByMelk\Admin::instance();
} );
