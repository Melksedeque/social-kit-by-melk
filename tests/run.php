<?php
/**
 * Runner mínimo: carrega o bootstrap, executa cada tests/test-*.php e sai com
 * código 1 se algum assert falhar. Sem Composer, sem PHPUnit.
 *
 * Uso: php tests/run.php
 */

require __DIR__ . '/bootstrap.php';

foreach ( glob( __DIR__ . '/test-*.php' ) as $file ) {
	echo basename( $file ) . "\n";
	require $file;
}

printf(
	"\n%d assertions OK, %d falhas\n",
	$GLOBALS['skbm_test_passes'],
	$GLOBALS['skbm_test_failures']
);

exit( $GLOBALS['skbm_test_failures'] ? 1 : 0 );
