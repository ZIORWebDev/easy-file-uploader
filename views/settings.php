<?php
/**
 * Settings page HTML markup for Easy DragDrop File Uploader.
 *
 * @package ZIOR\DragDrop
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h2><?php esc_html_e( 'Easy DragDrop File Uploader Settings', 'easy-file-uploader' ); ?></h2>
	<div class="easy-dragdrop-settings-container">
		<form method="post" action="options.php">
			<?php
			settings_fields( $args['options_group'] );
			do_settings_sections( $args['page_slug'] );
			submit_button();
			?>
		</form>
		<?php do_action( 'easy_dragdrop_settings_after' ); ?>
	</div>
</div>

