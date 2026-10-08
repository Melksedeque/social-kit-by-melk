<?php
namespace Melk\SocialKitByMelk;

defined( 'ABSPATH' ) || exit;

/**
 * Contagem ponderada de caracteres igual à do X: URL = 23, CJK/emoji = 2,
 * qualquer outro caractere (incluindo quebra de linha) = 1.
 *
 * A mesma lógica existe em assets/js/social-panel.js para o painel do editor.
 */
class Social_Counter {

	const URL_WEIGHT = 23;

	protected static $double_width_ranges = [
		[ 0x1100, 0x115F ],
		[ 0x2E80, 0xA4CF ],
		[ 0xAC00, 0xD7A3 ],
		[ 0xF900, 0xFAFF ],
		[ 0xFF00, 0xFF60 ],
		[ 0xFFE0, 0xFFE6 ],
		[ 0x2600, 0x27BF ],
		[ 0x1F1E6, 0x1F1FF ],
		[ 0x1F300, 0x1FAFF ],
	];

	public static function count( $text ) {
		$text = (string) $text;

		$url_pattern = '#https?://[^\s]+#iu';
		preg_match_all( $url_pattern, $text, $urls );
		$url_count = isset( $urls[0] ) ? count( $urls[0] ) : 0;

		$without_urls = preg_replace( $url_pattern, '', $text );
		$chars        = preg_split( '//u', $without_urls, -1, PREG_SPLIT_NO_EMPTY );

		$length = 0;

		foreach ( $chars as $char ) {
			$length += self::is_double_width( $char ) ? 2 : 1;
		}

		return $length + ( $url_count * self::URL_WEIGHT );
	}

	protected static function is_double_width( $char ) {
		$code = self::codepoint( $char );

		if ( null === $code ) {
			return false;
		}

		foreach ( self::$double_width_ranges as $range ) {
			if ( $code >= $range[0] && $code <= $range[1] ) {
				return true;
			}
		}

		return false;
	}

	protected static function codepoint( $char ) {
		$converted = @mb_convert_encoding( $char, 'UTF-32BE', 'UTF-8' );

		if ( false === $converted || '' === $converted ) {
			return null;
		}

		return hexdec( bin2hex( $converted ) );
	}
}
