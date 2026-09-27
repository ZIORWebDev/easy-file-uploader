<?php
use ZIORWebDev\DragDrop\Helpers;

$easy_dragdrop_field_types   = $args['field_types'] ?? array();
$easy_dragdrop_tag_generator = $args['tgg'] ?? null;
$easy_dragdrop_max_file_size = Helpers::get_default_max_file_size();
?>
<header class="description-box">
	<h3>
	<?php
		echo esc_html( $easy_dragdrop_field_types['easy_dragdrop_upload']['heading'] );
	?>
	</h3>

	<p>
	<?php
		$easy_dragdrop_description = wp_kses(
			$easy_dragdrop_field_types['easy_dragdrop_upload']['description'],
			array(
				'a'      => array( 'href' => true ),
				'strong' => array(),
			),
			array( 'http', 'https' )
		);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The description is restricted to approved markup by wp_kses().
		echo $easy_dragdrop_description;
		?>
	</p>
</header>

<div class="control-box">
	<?php
		$easy_dragdrop_tag_generator->print(
			'field_type',
			array(
				'with_required'  => true,
				'select_options' => array(
					'easy_dragdrop_upload' => $easy_dragdrop_field_types['easy_dragdrop_upload']['display_name'],
				),
			)
		);

		$easy_dragdrop_tag_generator->print( 'field_name' );
		$easy_dragdrop_tag_generator->print( 'class_attr' );
		?>

	<fieldset>
		<legend id="<?php echo esc_attr( $easy_dragdrop_tag_generator->ref( 'buttonlabel-option-legend' ) ); ?>">
		<?php
			echo esc_html( __( 'Button Label', 'easy-file-uploader' ) );
		?>
		</legend>
		<label>
		<?php
		printf(
			'<span id="%1$s">%2$s</span><br />',
			esc_attr( trim( $easy_dragdrop_tag_generator->ref( 'buttonlabel-option-description' ) ) ),
			esc_html( __( "Use underscores to separate the words since CF7 doesn't support spaces on field attribute value.", 'easy-file-uploader' ) )
		);

		$easy_dragdrop_button_label = get_option( 'easy_dragdrop_button_label', 'Browse Files' );

		// Replace spaces with underscores. In the field option, we use underscores to separate the words since CF7 doesn't support spaces on field attribute value.
		$easy_dragdrop_button_label = str_replace( ' ', '_', $easy_dragdrop_button_label );

		printf(
			'<input type="text" value="%1$s" aria-labelledby="%2$s" aria-describedby="%3$s" data-tag-part="option" data-tag-option="buttonlabel:" />',
			esc_attr( trim( $easy_dragdrop_button_label ) ),
			esc_attr( trim( $easy_dragdrop_tag_generator->ref( 'buttonlabel-option-legend' ) ) ),
			esc_attr( trim( $easy_dragdrop_tag_generator->ref( 'buttonlabel-option-description' ) ) )
		);
		?>
		</label>
	</fieldset>

	<fieldset>
		<legend id="<?php echo esc_attr( $easy_dragdrop_tag_generator->ref( 'filetypes-option-legend' ) ); ?>">
		<?php
			echo esc_html( __( 'Acceptable file types', 'easy-file-uploader' ) );
		?>
		</legend>
		<label>
		<?php
		printf(
			'<span id="%1$s">%2$s</span><br />',
			esc_attr( trim( $easy_dragdrop_tag_generator->ref( 'filetypes-option-description' ) ) ),
			esc_html( __( 'Pipe-separated file types list. Please can use file extensions.', 'easy-file-uploader' ) )
		);

		$easy_dragdrop_raw_file_types = get_option( 'easy_dragdrop_file_types_allowed', '' );
		$easy_dragdrop_raw_file_types = array_map( 'trim', explode( ',', $easy_dragdrop_raw_file_types ) );

		printf(
			'<input type="text" value="%1$s" aria-labelledby="%2$s" aria-describedby="%3$s" data-tag-part="option" data-tag-option="filetypes:" />',
			esc_attr( trim( implode( '|', $easy_dragdrop_raw_file_types ) ) ),
			esc_attr( trim( $easy_dragdrop_tag_generator->ref( 'filetypes-option-legend' ) ) ),
			esc_attr( trim( $easy_dragdrop_tag_generator->ref( 'filetypes-option-description' ) ) )
		);
		?>
		</label>
	</fieldset>

	<fieldset>
		<legend id="<?php echo esc_attr( $easy_dragdrop_tag_generator->ref( 'limit-option-legend' ) ); ?>">
		<?php
			echo esc_html( __( 'File size limit (MB)', 'easy-file-uploader' ) );
		?>
		</legend>
		<label>
		<?php
		printf(
			'<span id="%1$s">%2$s</span><br />',
			esc_attr( trim( $easy_dragdrop_tag_generator->ref( 'limit-option-description' ) ) ),
			esc_html( __( 'File maximum size in MB.', 'easy-file-uploader' ) )
		);

		printf(
			'<input type="number" value="%1$s" aria-labelledby="%2$s" aria-describedby="%3$s" data-tag-part="option" data-tag-option="limit:" />',
			esc_attr( trim( (string) $easy_dragdrop_max_file_size ) ),
			esc_attr( trim( $easy_dragdrop_tag_generator->ref( 'limit-option-legend' ) ) ),
			esc_attr( trim( $easy_dragdrop_tag_generator->ref( 'limit-option-description' ) ) )
		);
		?>
		</label>
	</fieldset>

	<fieldset>
		<legend id="<?php echo esc_attr( $easy_dragdrop_tag_generator->ref( 'multifiles-option-legend' ) ); ?>">
		<?php
			echo esc_html( __( 'Multiple Files?', 'easy-file-uploader' ) );
		?>
		</legend>
		<label>
		<?php
		printf(
			'<span id="%1$s">%2$s</span><br />',
			esc_attr( trim( $easy_dragdrop_tag_generator->ref( 'multifiles-option-description' ) ) ),
			esc_html( __( 'Check if you want to allow multiple files to be uploaded.', 'easy-file-uploader' ) )
		);

		printf(
			'<input type="checkbox" value="1" aria-labelledby="%1$s" aria-describedby="%2$s" data-tag-part="option" data-tag-option="multifiles:" />',
			esc_attr( trim( $easy_dragdrop_tag_generator->ref( 'multifiles-option-legend' ) ) ),
			esc_attr( trim( $easy_dragdrop_tag_generator->ref( 'multifiles-option-description' ) ) )
		);
		?>
		</label>
	</fieldset>

	<fieldset>
		<legend id="<?php echo esc_attr( $easy_dragdrop_tag_generator->ref( 'maxfiles-option-legend' ) ); ?>">
		<?php
			echo esc_html( __( 'Maximum Files', 'easy-file-uploader' ) );
		?>
		</legend>
		<label>
		<?php
		printf(
			'<span id="%1$s">%2$s</span><br />',
			esc_attr( trim( $easy_dragdrop_tag_generator->ref( 'maxfiles-option-description' ) ) ),
			esc_html( __( 'Maximum number of files that can be uploaded.', 'easy-file-uploader' ) )
		);

		printf(
			'<input type="number" value="%1$s" aria-labelledby="%2$s" aria-describedby="%3$s" data-tag-part="option" data-tag-option="maxfiles:" />',
			esc_attr( trim( (string) get_option( 'easy_dragdrop_max_files', '' ) ) ),
			esc_attr( trim( $easy_dragdrop_tag_generator->ref( 'maxfiles-option-legend' ) ) ),
			esc_attr( trim( $easy_dragdrop_tag_generator->ref( 'maxfiles-option-description' ) ) )
		);
		?>
		</label>
	</fieldset>
</div>

<footer class="insert-box">
	<?php
		$easy_dragdrop_tag_generator->print( 'insert_box_content' );
		$easy_dragdrop_tag_generator->print( 'mail_tag_tip' );
	?>
</footer>