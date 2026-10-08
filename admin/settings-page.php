<?php
/**
 * Template da tela Configurações > Social Kit.
 * Variáveis disponíveis: $enabled (array), $post_types (array de WP_Post_Type),
 * $seo_plugin_active (bool).
 * Renderizado via require dentro de Admin::render_settings_page(), então $this é a instância de Admin.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap skbm-settings">
	<h1><?php esc_html_e( 'Social Kit by Melk', 'social-kit-by-melk' ); ?></h1>

	<?php if ( ! $seo_plugin_active ) : ?>
		<div class="notice notice-info inline skbm-seo-notice">
			<p>
				<strong><?php esc_html_e( 'Dica:', 'social-kit-by-melk' ); ?></strong>
				<?php esc_html_e( 'O Social Kit funciona melhor com o apoio de um plugin de SEO (Yoast SEO, Rank Math ou equivalente). Quando um deles já define a palavra-chave principal do post, o Social Kit reaproveita essa palavra-chave nas hashtags, em vez de aproximar com a primeira tag do post. Nenhum deles é obrigatório — sem eles, o Social Kit continua funcionando normalmente com o fallback da primeira tag.', 'social-kit-by-melk' ); ?>
			</p>
		</div>
	<?php endif; ?>

	<form method="post" action="options.php">
		<?php settings_fields( 'skbm_settings' ); ?>

		<h2><?php esc_html_e( 'Tipos de conteúdo habilitados', 'social-kit-by-melk' ); ?></h2>
		<p><?php esc_html_e( 'Escolha em quais tipos de post o Social Kit deve gerar rótulo, título, texto e legenda automaticamente ao salvar.', 'social-kit-by-melk' ); ?></p>

		<table class="form-table" role="presentation">
			<tbody>
			<?php foreach ( $post_types as $post_type ) : ?>
				<tr>
					<th scope="row"><?php echo esc_html( $post_type->labels->name ); ?></th>
					<td>
						<label>
							<input
								type="checkbox"
								name="skbm_enabled_post_types[]"
								value="<?php echo esc_attr( $post_type->name ); ?>"
								<?php checked( in_array( $post_type->name, $enabled, true ) ); ?>
							/>
							<?php esc_html_e( 'Habilitar Social Kit', 'social-kit-by-melk' ); ?>
						</label>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<?php submit_button(); ?>
	</form>

	<hr />

	<div class="skbm-other-plugins">
		<h2><?php esc_html_e( 'Outros plugins by Melk', 'social-kit-by-melk' ); ?></h2>
		<div class="skbm-other-plugins__list">
			<?php foreach ( $this->other_plugins() as $plugin ) : ?>
				<div class="skbm-other-plugins__card">
					<h3><?php echo esc_html( $plugin['name'] ); ?></h3>
					<p><?php echo esc_html( $plugin['description'] ); ?></p>
					<a href="<?php echo esc_url( $plugin['url'] ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Ver no WordPress.org', 'social-kit-by-melk' ); ?> &rarr;
					</a>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
