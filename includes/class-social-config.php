<?php
namespace Melk\SocialKitByMelk;

defined( 'ABSPATH' ) || exit;

/**
 * Configuração central: cada rede social é uma entrada neste array, não um
 * código novo. Rótulos, CTAs, hashtags e limites são filtráveis via skbm_*
 * para que nada fique hardcoded para um nicho específico.
 */
class Social_Config {

	public static function networks() {
		$networks = [
			'x' => [
				'label'        => 'X (Twitter)',
				'limits'       => [
					'label'      => 20,
					'card_title' => 30,
					'card_text'  => 170,
					'caption'    => 280,
				],
				'max_hashtags' => 3,
			],
		];

		return apply_filters( 'skbm_networks', $networks );
	}

	public static function limits( $network = 'x' ) {
		$networks = self::networks();

		return isset( $networks[ $network ]['limits'] ) ? $networks[ $network ]['limits'] : [];
	}

	public static function max_hashtags( $network = 'x' ) {
		$networks = self::networks();

		return isset( $networks[ $network ]['max_hashtags'] ) ? (int) $networks[ $network ]['max_hashtags'] : 3;
	}

	public static function label_map() {
		return apply_filters( 'skbm_label_map', [] );
	}

	public static function cta_map() {
		return apply_filters( 'skbm_cta_map', [] );
	}

	public static function default_cta() {
		return apply_filters( 'skbm_default_cta', __( 'Leia mais', 'social-kit-by-melk' ) . ' 👇' );
	}

	public static function cta_trim_list() {
		$default = [
			__( 'Vale a pena?', 'social-kit-by-melk' ),
			__( 'Confira!', 'social-kit-by-melk' ),
			__( 'Saiba mais!', 'social-kit-by-melk' ),
			__( 'Não perca!', 'social-kit-by-melk' ),
		];

		return apply_filters( 'skbm_cta_trim_list', $default );
	}

	public static function enabled_post_types() {
		$saved = get_option( 'skbm_enabled_post_types', [ 'post' ] );

		if ( ! is_array( $saved ) ) {
			$saved = [ 'post' ];
		}

		return apply_filters( 'skbm_enabled_post_types', $saved );
	}
}
