<?php
namespace Melk\SocialKitByMelk;

defined( 'ABSPATH' ) || exit;

/**
 * Contagem ponderada de caracteres igual à do X (regras do twitter-text v3):
 * - qualquer URL conta 23;
 * - cada emoji (inclusive sequências com ZWJ, tons de pele e bandeiras) conta 2;
 * - caracteres nas faixas Unicode "leves" abaixo contam 1 (inclui letras latinas
 *   acentuadas, cirílico, grego e a quebra de linha);
 * - qualquer outro caractere (CJK, "…", símbolos etc.) conta 2.
 *
 * A mesma lógica existe em assets/js/social-panel.js para o painel do editor.
 */
class Social_Counter {

	const URL_WEIGHT = 23;

	/**
	 * Faixas [início, fim] de pontos de código com peso 1. Todo o resto pesa 2.
	 */
	protected static $single_weight_ranges = [
		[ 0x0000, 0x10FF ],
		[ 0x2000, 0x200D ],
		[ 0x2010, 0x201F ],
		[ 0x2032, 0x2037 ],
	];

	/**
	 * Um emoji (ou sequência) inteiro vira um único item de peso 2: bandeira
	 * (2 indicadores regionais) ou base + modificadores unidos por ZWJ.
	 */
	const EMOJI_PATTERN = '/(?:[\x{1F1E6}-\x{1F1FF}]{2}|[\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{1F300}-\x{1FAFF}][\x{FE0F}\x{1F3FB}-\x{1F3FF}]*(?:\x{200D}[\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{1F300}-\x{1FAFF}][\x{FE0F}\x{1F3FB}-\x{1F3FF}]*)*)/u';

	const URL_PATTERN = '#https?://[^\s]+#iu';

	public static function count( $text ) {
		$text = (string) $text;

		preg_match_all( self::URL_PATTERN, $text, $urls );
		$url_count = isset( $urls[0] ) ? count( $urls[0] ) : 0;
		$text      = preg_replace( self::URL_PATTERN, '', $text );

		$emoji_count = (int) preg_match_all( self::EMOJI_PATTERN, $text );
		$text        = preg_replace( self::EMOJI_PATTERN, '', $text );

		$chars  = preg_split( '//u', (string) $text, -1, PREG_SPLIT_NO_EMPTY );
		$length = 0;

		if ( false === $chars ) {
			$chars = [];
		}

		foreach ( $chars as $char ) {
			$length += self::char_weight( $char );
		}

		return $length + ( $emoji_count * 2 ) + ( $url_count * self::URL_WEIGHT );
	}

	protected static function char_weight( $char ) {
		$code = self::codepoint( $char );

		foreach ( self::$single_weight_ranges as $range ) {
			if ( $code >= $range[0] && $code <= $range[1] ) {
				return 1;
			}
		}

		return 2;
	}

	protected static function codepoint( $char ) {
		$bytes = unpack( 'C*', $char );
		$first = $bytes[1];

		if ( $first < 0x80 ) {
			return $first;
		}

		if ( $first < 0xE0 ) {
			return ( ( $first & 0x1F ) << 6 ) | ( $bytes[2] & 0x3F );
		}

		if ( $first < 0xF0 ) {
			return ( ( $first & 0x0F ) << 12 ) | ( ( $bytes[2] & 0x3F ) << 6 ) | ( $bytes[3] & 0x3F );
		}

		return ( ( $first & 0x07 ) << 18 ) | ( ( $bytes[2] & 0x3F ) << 12 ) | ( ( $bytes[3] & 0x3F ) << 6 ) | ( $bytes[4] & 0x3F );
	}
}
