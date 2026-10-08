<?php
use Melk\SocialKitByMelk\Generators\Rule_Generator;
use Melk\SocialKitByMelk\Social_Counter;

$GLOBALS['skbm_test_categories'][1] = [ (object) [ 'name' => 'Tutoriais', 'term_id' => 3 ] ];
$GLOBALS['skbm_test_tags'][1]       = [ (object) [ 'name' => 'plugin wordpress' ] ];

$url  = 'https://exemplo.com/abc12';
$post = skbm_test_post( [
	'ID'           => 1,
	'post_title'   => 'Como criar um plugin WordPress para encurtar URLs de forma simples',
	'post_excerpt' => 'Aprenda a criar um plugin do zero. Veja cada passo com exemplos reais. Vale a pena?',
] );

$generator = new Rule_Generator();
$fields    = $generator->generate( $post, $url );

skbm_assert_same( 'TUTORIAIS', $fields['label'], 'rótulo em maiúsculas' );
skbm_assert( mb_strlen( $fields['card_title'] ) <= 30, 'título do card respeita 30' );
skbm_assert( mb_strlen( $fields['card_text'] ) <= 170, 'texto do card respeita 170' );
skbm_assert( false === strpos( $fields['card_text'], 'Vale a pena' ), 'CTA final removido do texto do card' );
skbm_assert( Social_Counter::count( $fields['caption_x'] ) <= 280, 'legenda respeita 280 ponderados' );
skbm_assert( false !== strpos( $fields['caption_x'], $url ), 'legenda contém a URL' );
skbm_assert( false !== strpos( $fields['caption_x'], "\n\n" ), 'legenda mantém blocos separados por linha em branco' );
skbm_assert_same( [ 'PluginWordpress', 'Tutoriais' ], $fields['hashtags'], 'hashtags: primeira tag + categoria (sem plugin de SEO)' );

// Overrides substituem os metas sem gravar nada.
$over = $generator->generate( $post, $url, [ 'subject' => 'Assunto curto', 'hook' => 'NOVO:' ] );
skbm_assert_same( 'Assunto curto', $over['card_title'], 'override de assunto vira título do card' );
skbm_assert( 0 === strpos( $over['caption_x'], 'NOVO: ' ), 'override de gancho abre a legenda' );
skbm_assert_same( [], $GLOBALS['skbm_test_meta'], 'generate() não grava metas' );

// Meta salvo é usado quando não há override.
$GLOBALS['skbm_test_meta'][1]['_skbm_hook'] = 'DICA:';
$saved = $generator->generate( $post, $url );
skbm_assert( 0 === strpos( $saved['caption_x'], 'DICA: ' ), 'gancho salvo no meta é usado' );
unset( $GLOBALS['skbm_test_meta'][1] );

// Excerpt enorme: o link nunca é cortado e o limite é respeitado.
$long = skbm_test_post( [
	'ID'           => 1,
	'post_title'   => 'Título',
	'post_excerpt' => str_repeat( 'Frase muito longa que enche a legenda inteira sem parar nunca. ', 12 ),
] );
$long_fields = $generator->generate( $long, $url );
skbm_assert( Social_Counter::count( $long_fields['caption_x'] ) <= 280, 'legenda longa continua dentro do limite' );
skbm_assert( false !== strpos( $long_fields['caption_x'], $url ), 'link nunca é cortado' );

// Sem excerpt: usa as duas primeiras frases do conteúdo.
$no_excerpt = skbm_test_post( [
	'ID'           => 1,
	'post_title'   => 'Sem resumo',
	'post_content' => '<p>Primeira frase do conteúdo. Segunda frase do conteúdo. Terceira não entra.</p>',
] );
$ne = $generator->generate( $no_excerpt, $url );
skbm_assert_same( 'Primeira frase do conteúdo. Segunda frase do conteúdo.', $ne['card_text'], 'fallback para as 2 primeiras frases do conteúdo' );
