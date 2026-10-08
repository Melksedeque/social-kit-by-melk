<?php
namespace Melk\SocialKitByMelk;

defined( 'ABSPATH' ) || exit;

/**
 * Utilitários de texto usados pelos generators: corte por palavra/frase
 * respeitando stopwords, divisão em frases e PascalCase para hashtags.
 */
class Text_Utils {

	public static function default_stopwords() {
		return [
			'a', 'o', 'as', 'os', 'de', 'do', 'da', 'dos', 'das', 'em', 'no', 'na', 'nos', 'nas',
			'um', 'uma', 'uns', 'umas', 'e', 'ou', 'que', 'para', 'por', 'com', 'ao', 'aos', 'à', 'às',
			'the', 'an', 'of', 'in', 'on', 'at', 'to', 'for', 'and', 'or', 'with', 'by',
		];
	}

	public static function stopwords() {
		return apply_filters( 'skbm_stopwords', self::default_stopwords() );
	}

	public static function truncate_words( $text, $limit ) {
		$text = trim( (string) $text );

		if ( '' === $text ) {
			return '';
		}

		if ( mb_strlen( $text ) <= $limit ) {
			return self::trim_trailing_stopword( $text );
		}

		$words  = preg_split( '/\s+/u', $text );
		$result = '';

		foreach ( $words as $word ) {
			$candidate = '' === $result ? $word : $result . ' ' . $word;

			if ( mb_strlen( $candidate ) > $limit ) {
				break;
			}

			$result = $candidate;
		}

		return self::trim_trailing_stopword( $result );
	}

	protected static function trim_trailing_stopword( $text ) {
		$stopwords = self::stopwords();
		$words     = preg_split( '/\s+/u', trim( $text ) );

		while ( count( $words ) > 1 && in_array( mb_strtolower( end( $words ) ), $stopwords, true ) ) {
			array_pop( $words );
		}

		return implode( ' ', $words );
	}

	public static function split_sentences( $text ) {
		$text = trim( (string) $text );

		if ( '' === $text ) {
			return [];
		}

		preg_match_all( '/[^.!?]+[.!?]*/u', $text, $matches );

		return array_values( array_filter( array_map( 'trim', $matches[0] ) ) );
	}

	public static function truncate_sentences( $text, $limit ) {
		$text = trim( (string) $text );

		if ( '' === $text ) {
			return '';
		}

		if ( mb_strlen( $text ) <= $limit ) {
			return $text;
		}

		$sentences = self::split_sentences( $text );
		$result    = '';

		foreach ( $sentences as $sentence ) {
			$candidate = '' === $result ? $sentence : $result . ' ' . $sentence;

			if ( mb_strlen( $candidate ) > $limit ) {
				break;
			}

			$result = $candidate;
		}

		if ( '' === $result ) {
			$result = self::truncate_words( $text, max( 0, $limit - 1 ) );
		}

		$result = rtrim( $result, " \t\n\r\0\x0B.!?" );

		return '' === $result ? '' : $result . '.';
	}

	public static function to_pascal_case( $text ) {
		$words = preg_split( '/[^\p{L}\p{N}]+/u', (string) $text, -1, PREG_SPLIT_NO_EMPTY );

		$pascal = array_map( function ( $word ) {
			return mb_strtoupper( mb_substr( $word, 0, 1 ) ) . mb_strtolower( mb_substr( $word, 1 ) );
		}, $words );

		return implode( '', $pascal );
	}
}
