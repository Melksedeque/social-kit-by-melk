<?php
use Melk\SocialKitByMelk\Social_Counter as C;

$cases = [
	[ 'abc', 3, 'ASCII conta 1 por caractere' ],
	[ "a\nb", 3, 'quebra de linha conta 1' ],
	[ 'áéíõç', 5, 'letras latinas acentuadas contam 1' ],
	[ 'Привет', 6, 'cirílico conta 1' ],
	[ 'https://exemplo.com/um/caminho/muito/longo/de/url', 23, 'URL conta 23' ],
	[ 'a https://x.com/b c', 2 + 23 + 2, 'URL no meio do texto ("a " + URL + " c")' ],
	[ '日本語', 6, 'CJK conta 2' ],
	[ '…', 2, 'reticências Unicode contam 2 (como no X)' ],
	[ '—', 1, 'travessão conta 1' ],
	[ '👇', 2, 'emoji conta 2' ],
	[ '❤️', 2, 'emoji com variation selector conta 2, não 3' ],
	[ '👍🏽', 2, 'emoji com tom de pele conta 2' ],
	[ '👨‍👩‍👧', 2, 'sequência ZWJ (família) conta 2' ],
	[ '🇧🇷', 2, 'bandeira conta 2' ],
	[ "Olá 👇\n\nhttps://x.com/y", 4 + 2 + 2 + 23, 'mistura de texto, emoji, quebras e URL' ],
	[ '', 0, 'string vazia' ],
];

foreach ( $cases as $case ) {
	skbm_assert_same( $case[1], C::count( $case[0] ), $case[2] );
}

skbm_assert_same( 0, C::count( "\xff\xfe" ), 'UTF-8 inválido não gera erro fatal' );
