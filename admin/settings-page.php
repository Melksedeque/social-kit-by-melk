<?php
/**
 * Template da tela Configurações > Social Kit.
 * Variáveis disponíveis: $enabled (array), $post_types (array de WP_Post_Type),
 * $seo_plugin_active (bool), $delete_data_on_uninstall (int, 0 ou 1).
 * Renderizado via require dentro de Admin::render_settings_page(), então $this é a instância de Admin.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap skbm-settings">
	<h1><?php esc_html_e( 'Social Kit by Melk', 'social-kit-by-melk' ); ?></h1>

	<?php if ( ! $seo_plugin_active ) : ?>
		<div class="notice notice-info inline skbm-seo-notice">
			<p>
				<strong><?php esc_html_e( 'Tip:', 'social-kit-by-melk' ); ?></strong>
				<?php esc_html_e( 'Social Kit works best alongside an SEO plugin (Yoast SEO, Rank Math or similar). When one of them already defines the focus keyword of a post, Social Kit reuses that keyword in the hashtags instead of approximating it with the first tag. None of them is required: without one, Social Kit keeps working normally, falling back to the first tag.', 'social-kit-by-melk' ); ?>
			</p>
		</div>
	<?php endif; ?>

	<form method="post" action="options.php">
		<?php settings_fields( 'skbm_settings' ); ?>

		<h2><?php esc_html_e( 'Enabled post types', 'social-kit-by-melk' ); ?></h2>
		<p><?php esc_html_e( 'Choose the post types for which Social Kit automatically generates the label, card title, card text and caption when a post is saved.', 'social-kit-by-melk' ); ?></p>

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
							<?php esc_html_e( 'Enable Social Kit', 'social-kit-by-melk' ); ?>
						</label>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<div class="skbm-danger-zone">
			<h2><?php esc_html_e( 'Danger Zone', 'social-kit-by-melk' ); ?></h2>

			<strong><?php esc_html_e( 'When the plugin is deleted', 'social-kit-by-melk' ); ?></strong>
			<p>
				<label>
					<input
						type="checkbox"
						name="skbm_delete_data_on_uninstall"
						value="1"
						<?php checked( 1, $delete_data_on_uninstall ); ?>
					/>
					<?php esc_html_e( 'Delete all plugin data (settings and the label, title, text and caption saved in your posts)', 'social-kit-by-melk' ); ?>
				</label>
			</p>
			<p class="description">
				<?php esc_html_e( 'Only takes effect when you delete the plugin from the Plugins screen. Leave unchecked to keep your data if you reinstall the plugin.', 'social-kit-by-melk' ); ?>
			</p>
		</div>

		<?php submit_button(); ?>
	</form>

	<hr />

	<div class="skbm-other-plugins">
		<h2><?php esc_html_e( 'Other plugins by Melk', 'social-kit-by-melk' ); ?></h2>
		<div class="skbm-other-plugins__list">
			<?php foreach ( $this->other_plugins() as $plugin ) : ?>
				<div class="skbm-other-plugins__card">
					<h3><?php echo esc_html( $plugin['name'] ); ?></h3>
					<p><?php echo esc_html( $plugin['description'] ); ?></p>
					<a href="<?php echo esc_url( $plugin['url'] ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'View on WordPress.org', 'social-kit-by-melk' ); ?> &rarr;
					</a>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
