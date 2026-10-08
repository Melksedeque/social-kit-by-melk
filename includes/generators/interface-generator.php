<?php
namespace Melk\SocialKitByMelk\Generators;

defined( 'ABSPATH' ) || exit;

/**
 * Contrato comum a qualquer estratégia de geração (regras, IA...).
 */
interface Generator {

	/**
	 * @param \WP_Post $post Post de origem.
	 * @param string   $url  URL já resolvida (link curto ou permalink).
	 * @param array    $overrides Valores que substituem os metas salvos (chaves: subject, hook), sem gravar nada.
	 *
	 * @return array{label:string,card_title:string,card_text:string,caption_x:string,hashtags:array}
	 */
	public function generate( \WP_Post $post, $url, array $overrides = [] );
}
