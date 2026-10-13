<?php
/**
 * Executado quando o plugin é excluído pelo painel do WordPress.
 *
 * Os dados só são removidos se o administrador marcou a opção
 * "Delete all plugin data when the plugin is deleted" nas configurações
 * (Configurações > Social Kit). Por padrão nada é apagado, para não perder
 * dados em uma reinstalação.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Remove os dados do plugin do site atual.
 *
 * A lista de metas é a mesma de Social_Kit::meta_keys(); este arquivo roda
 * sem o plugin carregado, então a lista fica aqui (um teste em tests/ garante
 * que as duas continuem iguais).
 */
if ( ! function_exists( 'skbm_uninstall_site' ) ) {
function skbm_uninstall_site() {
	if ( ! (int) get_option( 'skbm_delete_data_on_uninstall', 0 ) ) {
		return;
	}

	$meta_keys = [
		'_skbm_label',
		'_skbm_card_title',
		'_skbm_card_text',
		'_skbm_caption_x',
		'_skbm_hashtags',
		'_skbm_subject',
		'_skbm_hook',
		'_skbm_locked',
		'_skbm_source_hash',
		'_skbm_version',
	];

	foreach ( $meta_keys as $meta_key ) {
		delete_post_meta_by_key( $meta_key );
	}

	delete_option( 'skbm_enabled_post_types' );
	delete_option( 'skbm_delete_data_on_uninstall' );
	delete_option( 'skbm_db_version' );
}
}

if ( is_multisite() ) {
	foreach ( get_sites( [ 'fields' => 'ids', 'number' => 0 ] ) as $skbm_blog_id ) {
		switch_to_blog( $skbm_blog_id );
		skbm_uninstall_site();
		restore_current_blog();
	}
} else {
	skbm_uninstall_site();
}
