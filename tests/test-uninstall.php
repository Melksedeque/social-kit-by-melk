<?php
use Melk\SocialKitByMelk\Social_Kit;

define( 'WP_UNINSTALL_PLUGIN', 'social-kit-by-melk/social-kit-by-melk.php' );

$uninstall = dirname( __DIR__ ) . '/uninstall.php';

// Padrão (opção desmarcada): nada é apagado.
skbm_test_reset();
update_option( 'skbm_enabled_post_types', [ 'post' ] );
update_option( 'skbm_delete_data_on_uninstall', 0 );
update_option( 'skbm_db_version', '1.0.0' );
require $uninstall;
skbm_assert_same( [], $GLOBALS['skbm_test_deleted_meta'], 'desinstalar sem opt-in não apaga metas' );
skbm_assert_same( [ 'post' ], get_option( 'skbm_enabled_post_types' ), 'desinstalar sem opt-in mantém as opções' );
skbm_assert_same( '1.0.0', get_option( 'skbm_db_version' ), 'desinstalar sem opt-in mantém a versão do banco' );

// Sem a opção gravada (instalação antiga): também não apaga nada.
skbm_test_reset();
update_option( 'skbm_enabled_post_types', [ 'post' ] );
require $uninstall;
skbm_assert_same( [ 'post' ], get_option( 'skbm_enabled_post_types' ), 'sem a opção gravada, nada é apagado' );

// Com opt-in: apaga todas as opções e todos os metas _skbm_*.
skbm_test_reset();
update_option( 'skbm_enabled_post_types', [ 'post' ] );
update_option( 'skbm_delete_data_on_uninstall', 1 );
update_option( 'skbm_db_version', '1.0.0' );
update_option( 'outra_opcao_de_terceiros', 'fica' );
require $uninstall;

$deleted  = $GLOBALS['skbm_test_deleted_meta'][1];
$expected = Social_Kit::meta_keys();
sort( $deleted );
sort( $expected );
skbm_assert_same( $expected, $deleted, 'uninstall.php apaga exatamente as chaves de Social_Kit::meta_keys()' );
skbm_assert( 10 === count( $deleted ), 'são 10 chaves de meta' );
foreach ( $deleted as $key ) {
	skbm_assert( 0 === strpos( $key, '_skbm_' ), "chave apagada tem o prefixo do plugin: {$key}" );
}
skbm_assert_same( false, get_option( 'skbm_enabled_post_types', false ), 'opt-in apaga skbm_enabled_post_types' );
skbm_assert_same( false, get_option( 'skbm_delete_data_on_uninstall', false ), 'opt-in apaga a própria opção' );
skbm_assert_same( false, get_option( 'skbm_db_version', false ), 'opt-in apaga skbm_db_version' );
skbm_assert_same( 'fica', get_option( 'outra_opcao_de_terceiros' ), 'não toca em opções de outros plugins' );

// Multisite: cada site decide por si.
skbm_test_reset();
$GLOBALS['skbm_test_multisite']    = [ 1, 2, 3 ];
$GLOBALS['skbm_test_options']      = [
	1 => [ 'skbm_delete_data_on_uninstall' => 1, 'skbm_enabled_post_types' => [ 'post' ] ],
	2 => [ 'skbm_delete_data_on_uninstall' => 0, 'skbm_enabled_post_types' => [ 'post' ] ],
	3 => [ 'skbm_delete_data_on_uninstall' => 1, 'skbm_enabled_post_types' => [ 'page' ] ],
];
require $uninstall;
skbm_assert_same( false, $GLOBALS['skbm_test_options'][1]['skbm_enabled_post_types'] ?? false, 'multisite: site 1 (opt-in) foi limpo' );
skbm_assert_same( [ 'post' ], $GLOBALS['skbm_test_options'][2]['skbm_enabled_post_types'], 'multisite: site 2 (sem opt-in) foi preservado' );
skbm_assert_same( false, $GLOBALS['skbm_test_options'][3]['skbm_enabled_post_types'] ?? false, 'multisite: site 3 (opt-in) foi limpo' );
skbm_assert( ! isset( $GLOBALS['skbm_test_deleted_meta'][2] ), 'multisite: nenhum meta apagado no site sem opt-in' );
skbm_assert( 10 === count( $GLOBALS['skbm_test_deleted_meta'][1] ) && 10 === count( $GLOBALS['skbm_test_deleted_meta'][3] ), 'multisite: metas apagados nos sites com opt-in' );
