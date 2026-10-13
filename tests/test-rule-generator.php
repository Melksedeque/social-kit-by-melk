<?php
use Melk\SocialKitByMelk\Generators\Rule_Generator;
use Melk\SocialKitByMelk\Social_Counter;

$GLOBALS['skbm_test_categories'][1] = [ (object) [ 'name' => 'Tutoriais', 'term_id' => 3 ] ];
$GLOBALS['skbm_test_tags'][1]       = [ (object) [ 'name' => 'plugin wordpress' ] ];

$url  = 'https://exemplo.com/abc12';
$post = skbm_test_post( [
	'ID'           => 1,
	'post_title'   => 'Como criar um plugin WordPress para encurtar URLs de forma simples',
	'post_excerpt' => 'Aprenda a criar um plugin do zero. Veja cada passo com exemplos reais. Is it worth it?',
] );

$generator = new Rule_Generator();
$fields    = $generator->generate( $post, $url );

skbm_assert_same( 'TUTORIAIS', $fields['label'], 'rótulo em maiúsculas' );
skbm_assert( mb_strlen( $fields['card_title'] ) <= 30, 'título do card respeita 30' );
skbm_assert( mb_strlen( $fields['card_text'] ) <= 170, 'texto do card respeita 170' );
skbm_assert( false === strpos( $fields['card_text'], 'Is it worth it' ), 'CTA final removido do texto do card' );
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

// Regressão (NerdWho/ArtCraft): primeiro período do resumo maior que o espaço da legenda.
// Antes, o gancho inteiro era descartado e a legenda virava só "CTA + URL".
$artcraft = skbm_test_post( [
	'ID'           => 1,
	'post_title'   => 'O fim do Monopólio da Adobe? Conheça o ArtCraft, a Suíte Open Source feita em Rust',
	'post_excerpt' => 'Se você trabalha com design, edição de vídeo ou ilustração, provavelmente já sentiu essa dor na pele: abrir o aplicativo da Creative Cloud, encarar uma mensalidade cada vez mais salgada e, de quebra, ver o computador sofrer com softwares pesados que carregam décadas de código legado. Felizmente, uma resposta de peso finalmente começou a tomar forma.',
] );
$art_url     = 'https://www.nerdwho.com.br/0038W';
$art_caption = $generator->generate( $artcraft, $art_url )['caption_x'];

skbm_assert( Social_Counter::count( $art_caption ) <= 280, 'gancho longo: legenda dentro do limite' );
skbm_assert( 0 === strpos( $art_caption, 'Se você trabalha com design' ), 'gancho longo: começa com o início do gancho (encurtado, não descartado)' );
skbm_assert( false !== strpos( $art_caption, "👇\n\n" . $art_url ), 'gancho longo: CTA e URL preservados' );
skbm_assert( false === strpos( $art_caption, 'código legado' ), 'gancho longo: foi realmente encurtado' );
$first_block = explode( "\n\n", $art_caption )[0];
skbm_assert( '.' === substr( $first_block, -1 ), 'gancho encurtado termina com ponto, sem reticências' );
skbm_assert( Social_Counter::count( $art_caption ) > 200, 'gancho longo: aproveita o espaço disponível' );

// Gancho curto e valor longo: o valor é descartado (ordem do corte), o gancho fica inteiro.
$short_hook = skbm_test_post( [
	'ID'           => 1,
	'post_title'   => 'Título',
	'post_excerpt' => 'Gancho curto. ' . str_repeat( 'Valor muito longo que não cabe. ', 12 ),
] );
$sh = $generator->generate( $short_hook, $url )['caption_x'];
skbm_assert( 0 === strpos( $sh, 'Gancho curto.' ), 'gancho curto permanece inteiro' );
skbm_assert( Social_Counter::count( $sh ) <= 280, 'gancho curto + valor longo respeita o limite' );

// Sem espaço nenhum para o gancho (gancho enorme + hook gigante): cai para CTA + URL, link intacto.
$no_room = $generator->generate( $post, $url, [ 'hook' => str_repeat( 'x', 260 ) ] )['caption_x'];
skbm_assert( false !== strpos( $no_room, $url ), 'sem espaço: URL continua na legenda' );
skbm_assert( Social_Counter::count( $no_room ) <= 280, 'sem espaço: continua dentro do limite' );
