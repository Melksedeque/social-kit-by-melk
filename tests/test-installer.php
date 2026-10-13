<?php
use Melk\SocialKitByMelk\Installer;

// Instalação limpa: cria os padrões e grava a versão do banco.
skbm_test_reset();
Installer::install();
skbm_assert_same( [ 'post' ], get_option( 'skbm_enabled_post_types' ), 'install: tipos de post padrão' );
skbm_assert_same( 0, get_option( 'skbm_delete_data_on_uninstall' ), 'install: apagar dados desmarcado por padrão' );
skbm_assert_same( SKBM_DB_VERSION, get_option( 'skbm_db_version' ), 'install: grava skbm_db_version' );

// Reinstalar/atualizar não sobrescreve o que o usuário salvou.
skbm_test_reset();
update_option( 'skbm_enabled_post_types', [ 'page', 'review' ] );
update_option( 'skbm_delete_data_on_uninstall', 1 );
Installer::install();
skbm_assert_same( [ 'page', 'review' ], get_option( 'skbm_enabled_post_types' ), 'install preserva os tipos de post salvos' );
skbm_assert_same( 1, get_option( 'skbm_delete_data_on_uninstall' ), 'install preserva a opção de apagar dados' );

// maybe_upgrade: sem versão guardada (instalação anterior à rotina) roda a instalação.
skbm_test_reset();
Installer::maybe_upgrade();
skbm_assert_same( SKBM_DB_VERSION, get_option( 'skbm_db_version' ), 'maybe_upgrade: sem versão guardada instala' );
skbm_assert_same( [ 'post' ], get_option( 'skbm_enabled_post_types' ), 'maybe_upgrade: cria os padrões ausentes' );

// maybe_upgrade: versão em dia não faz nada (barato a cada carregamento).
delete_option( 'skbm_enabled_post_types' );
Installer::maybe_upgrade();
skbm_assert_same( false, get_option( 'skbm_enabled_post_types', false ), 'maybe_upgrade: versão em dia não executa a instalação' );

// Migrações: rodam uma vez, em ordem, só nas versões menores que a chave.
class Skbm_Test_Installer extends Installer {
	public static $ran = [];

	protected static function migrations() {
		return [
			'0.9.0' => function () { Skbm_Test_Installer::$ran[] = '0.9.0'; },
			'1.0.0' => function () { Skbm_Test_Installer::$ran[] = '1.0.0'; },
			'1.1.0' => function () { Skbm_Test_Installer::$ran[] = '1.1.0'; },
		];
	}
}

skbm_test_reset();
update_option( 'skbm_db_version', '0.9.0' );
Skbm_Test_Installer::$ran = [];
Skbm_Test_Installer::install();
skbm_assert_same( [ '1.0.0', '1.1.0' ], Skbm_Test_Installer::$ran, 'migrações pendentes rodam em ordem e só as novas' );
skbm_assert_same( SKBM_DB_VERSION, get_option( 'skbm_db_version' ), 'versão do banco é atualizada depois das migrações' );

Skbm_Test_Installer::$ran = [];
Skbm_Test_Installer::install();
skbm_assert_same( [ '1.1.0' ], Skbm_Test_Installer::$ran, 'migração acima da versão do banco volta a ser pendente, as já aplicadas não repetem' );
