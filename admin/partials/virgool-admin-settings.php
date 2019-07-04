<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the settings page.
 *
 * @link       https://milad.nekofar.com
 * @since      1.0.0
 *
 * @package    Virgool
 * @subpackage Virgool/admin/partials
 */

?>
<div class="wrap virgool">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<form action="options.php" method="post">
		<?php

		settings_fields( 'virgool_options' );

		do_settings_sections( 'virgool' );

		submit_button();

		?>
	</form>
</div>
