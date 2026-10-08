<?php
namespace Melk\SocialKitByMelk\Generators;

use Melk\SocialKitByMelk\Social_Config;
use Melk\SocialKitByMelk\Text_Utils;
use Melk\SocialKitByMelk\Social_Counter;

defined( 'ABSPATH' ) || exit;

/**
 * Fase 1 do roadmap: geração determinística, sem IA, seguindo as regras da
 * seção 7 de _notas-internas/social-kit-by-melk/01-ideias-e-especificacao.md.
 */
class Rule_Generator implements Generator {

	public function generate( \WP_Post $post, $url ) {
		$limits = Social_Config::limits( 'x' );

		$category_name = $this->get_primary_category_name( $post );
		$label         = $this->build_label( $category_name, $limits['label'] );
		$card_title    = $this->build_card_title( $post, $limits['card_title'] );
		$card_text     = $this->build_card_text( $post, $limits['card_text'] );
		$hashtags      = $this->build_hashtags( $post, $category_name );
		$caption       = $this->build_caption( $post, $url, $category_name, $hashtags, $limits['caption'] );

		return [
			'label'      => $label,
			'card_title' => $card_title,
			'card_text'  => $card_text,
			'caption_x'  => $caption,
			'hashtags'   => $hashtags,
		];
	}

	protected function get_primary_category_name( \WP_Post $post ) {
		$primary_id = (int) get_post_meta( $post->ID, '_yoast_wpseo_primary_category', true );

		if ( ! $primary_id ) {
			$primary_id = (int) get_post_meta( $post->ID, 'rank_math_primary_category', true );
		}

		if ( $primary_id ) {
			$term = get_term( $primary_id, 'category' );

			if ( $term && ! is_wp_error( $term ) ) {
				return $term->name;
			}
		}

		$categories = get_the_category( $post->ID );

		return ! empty( $categories ) ? $categories[0]->name : '';
	}

	protected function build_label( $category_name, $limit ) {
		if ( '' === $category_name ) {
			return '';
		}

		$map   = Social_Config::label_map();
		$label = isset( $map[ $category_name ] ) ? $map[ $category_name ] : $category_name;

		return Text_Utils::truncate_words( mb_strtoupper( $label ), $limit );
	}

	protected function build_card_title( \WP_Post $post, $limit ) {
		$subject = get_post_meta( $post->ID, '_skbm_subject', true );
		$base    = '' !== trim( (string) $subject ) ? $subject : $post->post_title;

		return Text_Utils::truncate_words( wp_strip_all_tags( $base ), $limit );
	}

	protected function build_card_text( \WP_Post $post, $limit ) {
		$excerpt = $this->get_excerpt( $post );

		foreach ( Social_Config::cta_trim_list() as $cta ) {
			$excerpt = preg_replace( '/\s*' . preg_quote( $cta, '/' ) . '\s*$/u', '', $excerpt );
		}

		return Text_Utils::truncate_sentences( trim( $excerpt ), $limit );
	}

	protected function get_excerpt( \WP_Post $post ) {
		if ( ! empty( $post->post_excerpt ) ) {
			return wp_strip_all_tags( $post->post_excerpt );
		}

		$content   = wp_strip_all_tags( $post->post_content );
		$sentences = Text_Utils::split_sentences( $content );

		return implode( ' ', array_slice( $sentences, 0, 2 ) );
	}

	protected function build_hashtags( \WP_Post $post, $category_name ) {
		$max      = Social_Config::max_hashtags( 'x' );
		$hashtags = [];

		$keyword = $this->get_primary_keyword( $post );

		if ( '' !== $keyword ) {
			$hashtags[] = Text_Utils::to_pascal_case( $keyword );
		} else {
			$tags = get_the_tags( $post->ID );

			if ( ! empty( $tags ) ) {
				$hashtags[] = Text_Utils::to_pascal_case( $tags[0]->name );
			}
		}

		if ( '' !== $category_name ) {
			$hashtags[] = Text_Utils::to_pascal_case( $category_name );
		}

		$hashtags = array_values( array_unique( array_filter( $hashtags ) ) );

		return array_slice( $hashtags, 0, $max );
	}

	/**
	 * Reaproveita a palavra-chave principal de um plugin de SEO (Yoast,
	 * Rank Math ou qualquer outro via o filtro skbm_primary_keyword), em vez
	 * de aproximar com a primeira tag. Nenhum desses plugins é obrigatório:
	 * sem eles, build_hashtags() cai no fallback da primeira tag.
	 */
	protected function get_primary_keyword( \WP_Post $post ) {
		$keyword = get_post_meta( $post->ID, '_yoast_wpseo_focuskw', true );

		if ( empty( $keyword ) ) {
			$rank_math_keyword = get_post_meta( $post->ID, 'rank_math_focus_keyword', true );

			if ( ! empty( $rank_math_keyword ) ) {
				$parts   = explode( ',', $rank_math_keyword );
				$keyword = trim( $parts[0] );
			}
		}

		$keyword = apply_filters( 'skbm_primary_keyword', $keyword, $post );

		return is_string( $keyword ) ? trim( $keyword ) : '';
	}

	protected function build_caption( \WP_Post $post, $url, $category_name, array $hashtags, $limit ) {
		$hook      = trim( (string) get_post_meta( $post->ID, '_skbm_hook', true ) );
		$excerpt   = $this->get_excerpt( $post );
		$sentences = Text_Utils::split_sentences( $excerpt );

		$gancho = isset( $sentences[0] ) ? trim( $sentences[0] ) : trim( $post->post_title );
		$valor  = isset( $sentences[1] ) ? trim( $sentences[1] ) : trim( $post->post_title );

		$cta_map = Social_Config::cta_map();
		$cta     = isset( $cta_map[ $category_name ] ) ? $cta_map[ $category_name ] : Social_Config::default_cta();

		$hashtag_text = implode( ' ', array_map( function ( $tag ) {
			return '#' . $tag;
		}, $hashtags ) );

		return $this->assemble_and_trim( $hook, $gancho, $valor, $cta, $url, $hashtag_text, $limit );
	}

	/**
	 * Monta a legenda em blocos (gancho / valor+cta / link / hashtags),
	 * pulando blocos vazios. Usado tanto na montagem cheia quanto nos
	 * cortes progressivos de assemble_and_trim().
	 */
	protected function assemble_caption( $hook, $gancho, $valor, $cta, $url, $hashtag_text ) {
		$parts = [];

		$top = trim( $hook . ' ' . $gancho );
		if ( '' !== $top ) {
			$parts[] = $top;
		}

		$valor_clean = trim( $valor, " \t\n\r\0\x0B.!?" );
		$middle      = '' !== $valor_clean ? trim( $valor_clean . '. ' . $cta ) : trim( $cta );
		if ( '' !== $middle ) {
			$parts[] = $middle;
		}

		$parts[] = $url;

		if ( '' !== $hashtag_text ) {
			$parts[] = $hashtag_text;
		}

		return implode( "\n\n", $parts );
	}

	/**
	 * Ordem de corte da seção 7.5: hashtags -> frase de valor -> frase de
	 * gancho (com o hook). O link nunca é cortado.
	 */
	protected function assemble_and_trim( $hook, $gancho, $valor, $cta, $url, $hashtag_text, $limit ) {
		$attempts = [
			[ $hook, $gancho, $valor, $cta, $hashtag_text ],
			[ $hook, $gancho, $valor, $cta, '' ],
			[ $hook, $gancho, '', $cta, '' ],
			[ '', '', '', $cta, '' ],
			[ '', '', '', '', '' ],
		];

		foreach ( $attempts as $attempt ) {
			list( $a_hook, $a_gancho, $a_valor, $a_cta, $a_hashtags ) = $attempt;
			$caption = $this->assemble_caption( $a_hook, $a_gancho, $a_valor, $a_cta, $url, $a_hashtags );

			if ( Social_Counter::count( $caption ) <= $limit ) {
				return $caption;
			}
		}

		return $url;
	}
}
