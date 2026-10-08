<?php
/**
 * Bootstrap dos testes: stubs mínimos do WordPress para exercitar as classes
 * PHP puras (Text_Utils, Social_Counter, Rule_Generator) sem instalar o WP.
 * Rode com: php tests/run.php
 */

define( 'ABSPATH', __DIR__ . '/' );

$GLOBALS['skbm_test_meta']       = [];
$GLOBALS['skbm_test_categories'] = [];
$GLOBALS['skbm_test_tags']       = [];

function apply_filters( $tag, $value ) {
	return $value;
}

function __( $text, $domain = '' ) {
	return $text;
}

function wp_strip_all_tags( $text ) {
	return trim( strip_tags( (string) $text ) );
}

function get_post_meta( $post_id, $key, $single = false ) {
	return $GLOBALS['skbm_test_meta'][ $post_id ][ $key ] ?? '';
}

function get_term( $id, $taxonomy ) {
	return null;
}

function is_wp_error( $thing ) {
	return false;
}

function get_the_category( $post_id ) {
	return $GLOBALS['skbm_test_categories'][ $post_id ] ?? [];
}

function get_the_tags( $post_id ) {
	return $GLOBALS['skbm_test_tags'][ $post_id ] ?? [];
}

class WP_Post {
	public $ID           = 1;
	public $post_title   = '';
	public $post_excerpt = '';
	public $post_content = '';
}

function skbm_test_post( array $props ) {
	$post = new WP_Post();

	foreach ( $props as $key => $value ) {
		$post->$key = $value;
	}

	return $post;
}

$root = dirname( __DIR__ ) . '/includes/';

require_once $root . 'class-text-utils.php';
require_once $root . 'class-social-counter.php';
require_once $root . 'class-social-config.php';
require_once $root . 'generators/interface-generator.php';
require_once $root . 'generators/class-rule-generator.php';

$GLOBALS['skbm_test_failures'] = 0;
$GLOBALS['skbm_test_passes']   = 0;

function skbm_assert( $condition, $message ) {
	if ( $condition ) {
		$GLOBALS['skbm_test_passes']++;
		return;
	}

	$GLOBALS['skbm_test_failures']++;
	echo "  FAIL: {$message}\n";
}

function skbm_assert_same( $expected, $actual, $message ) {
	skbm_assert(
		$expected === $actual,
		$message . ' (esperado ' . var_export( $expected, true ) . ', obtido ' . var_export( $actual, true ) . ')'
	);
}
