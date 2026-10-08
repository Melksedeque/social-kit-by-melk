<?php
use Melk\SocialKitByMelk\Text_Utils as T;

skbm_assert_same( 'Como criar um plugin WordPress', T::truncate_words( 'Como criar um plugin WordPress para encurtar URLs de forma simples', 30 ), 'corta por palavra no limite' );
skbm_assert_same( 'Guia completo', T::truncate_words( 'Guia completo de', 30 ), 'remove stopword final mesmo sem estourar' );
skbm_assert_same( 'Guia completo', T::truncate_words( 'Guia completo de segurança', 16 ), 'não termina com preposição após o corte' );
skbm_assert_same( '', T::truncate_words( '', 30 ), 'vazio continua vazio' );
skbm_assert( mb_strlen( T::truncate_words( 'Palavraenormesemespaconenhumquepassadolimite', 10 ) ) <= 10, 'nunca passa do limite' );

$texto = 'Primeira frase curta. Segunda frase um pouco maior para testar o corte por sentenca no limite. Terceira frase bem longa que deve estourar o limite do card.';
skbm_assert_same( 'Primeira frase curta. Segunda frase um pouco maior para testar o corte por sentenca no limite.', T::truncate_sentences( $texto, 110 ), 'corta por frase inteira' );
skbm_assert( '.' === substr( T::truncate_sentences( $texto, 30 ), -1 ), 'termina com ponto final' );
skbm_assert( false === strpos( T::truncate_sentences( $texto, 30 ), '…' ), 'sem reticências' );

skbm_assert_same( [ 'Uma frase.', 'Outra frase!' ], T::split_sentences( 'Uma frase. Outra frase!' ), 'divide frases' );
skbm_assert_same( 'PluginDeSegurançaWordpress', T::to_pascal_case( 'plugin de segurança wordpress' ), 'PascalCase preserva acentos' );
