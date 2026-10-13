<?php
namespace Melk\SocialKitByMelk;

defined( 'ABSPATH' ) || exit;

/**
 * Instalação e upgrade do plugin.
 *
 * O hook de ativação não roda quando o plugin é atualizado (por exemplo, por
 * uma atualização vinda do SVN do WordPress.org). Por isso a mesma rotina
 * idempotente, install(), é chamada na ativação e por maybe_upgrade() sempre
 * que a versão guardada em `skbm_db_version` for diferente de SKBM_DB_VERSION.
 */
class Installer {

	const DB_VERSION_OPTION = 'skbm_db_version';

	/**
	 * Roda a instalação ou as migrações pendentes (idempotente).
	 */
	public static function install() {
		$installed = get_option( self::DB_VERSION_OPTION, '0' );

		// add_option() não sobrescreve valores que o usuário já salvou.
		add_option( 'skbm_enabled_post_types', [ 'post' ] );
		add_option( 'skbm_delete_data_on_uninstall', 0 );

		foreach ( static::migrations() as $version => $migration ) {
			if ( version_compare( (string) $installed, (string) $version, '<' ) ) {
				call_user_func( $migration );
			}
		}

		update_option( self::DB_VERSION_OPTION, SKBM_DB_VERSION );
	}

	/**
	 * Chamada a cada carregamento do plugin: custa uma leitura de opção
	 * (autoload) e só executa algo quando a versão do banco está desatualizada.
	 */
	public static function maybe_upgrade() {
		if ( get_option( self::DB_VERSION_OPTION ) === SKBM_DB_VERSION ) {
			return;
		}

		self::install();
	}

	/**
	 * Migrações de dados por versão do banco: [ '1.1.0' => callable ].
	 *
	 * Cada migração roda uma única vez, em ordem, nas instalações cuja versão
	 * guardada é menor que a chave. Vazio na 1.0.0 (não há o que migrar).
	 *
	 * @return array<string,callable>
	 */
	protected static function migrations() {
		return [];
	}
}
