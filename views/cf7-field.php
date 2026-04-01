<?php
use ZIORWebDev\DragDrop\Helpers;

$field_types   = $args['field_types'] ?? array();
$tgg           = $args['tgg'] ?? null;
$max_file_size = Helpers::get_default_max_file_size();
?>
<header class="description-box">
	<h3>
	<?php
		echo esc_html( $field_types['easy_dragdrop_upload']['heading'] );
	?>
	</h3>

	<p>
	<?php
		$description = wp_kses(
			$field_types['easy_dragdrop_upload']['description'],
			array(
				'a'      => array( 'href' => true ),
				'strong' => array(),
			),
			array( 'http', 'https' )
		);

		echo $description;
		?>
	</p>
</header>

<div class="control-box">
	<?php
		$tgg->print(
			'field_type',
			array(
				'with_required'  => true,
				'select_options' => array(
					'easy_dragdrop_upload' => $field_types['easy_dragdrop_upload']['display_name'],
				),
			)
		);

		$tgg->print( 'field_name' );
		$tgg->print( 'class_attr' );
		?>

	<fieldset>
		<legend id="<?php echo esc_attr( $tgg->ref( 'buttonlabel-option-legend' ) ); ?>">
		<?php
			echo esc_html( __( 'Button Label', 'easy-file-uploader' ) );
		?>
		</legend>
		<label>
		<?php
		printf(
			'<span %1$s>%2$s</span><br />',
			wpcf7_format_atts(
				array(
					'id' => $tgg->ref( 'buttonlabel-option-description' ),
				)
			),
			esc_html( __( "Use underscores to separate the words since CF7 doesn't support spaces on field attribute value.", 'easy-file-uploader' ) )
		);

		$button_label = get_option( 'easy_dragdrop_button_label', 'Browse Files' );

		// Replace spaces with underscores. In the field option, we use underscores to separate the words since CF7 doesn't support spaces on field attribute value.
		$button_label = str_replace( ' ', '_', $button_label );

		printf(
			'<input %s />',
			wpcf7_format_atts(
				array(
					'type'             => 'text',
					'value'            => $button_label,
					'aria-labelledby'  => $tgg->ref( 'buttonlabel-option-legend' ),
					'aria-describedby' => $tgg->ref( 'buttonlabel-option-description' ),
					'data-tag-part'    => 'option',
					'data-tag-option'  => 'buttonlabel:',
				)
			)
		);
		?>
		</label>
	</fieldset>

	<fieldset>
		<legend id="<?php echo esc_attr( $tgg->ref( 'filetypes-option-legend' ) ); ?>">
		<?php
			echo esc_html( __( 'Acceptable file types', 'easy-file-uploader' ) );
		?>
		</legend>
		<label>
		<?php
		printf(
			'<span %1$s>%2$s</span><br />',
			wpcf7_format_atts(
				array(
					'id' => $tgg->ref( 'filetypes-option-description' ),
				)
			),
			esc_html( __( 'Pipe-separated file types list. Please can use file extensions.', 'easy-file-uploader' ) )
		);

		$raw_file_types = get_option( 'easy_dragdrop_file_types_allowed', '' );
		$raw_file_types = array_map( 'trim', explode( ',', $raw_file_types ) );

		printf(
			'<input %s />',
			wpcf7_format_atts(
				array(
					'type'             => 'text',
					'value'            => implode( '|', $raw_file_types ),
					'aria-labelledby'  => $tgg->ref( 'filetypes-option-legend' ),
					'aria-describedby' => $tgg->ref( 'filetypes-option-description' ),
					'data-tag-part'    => 'option',
					'data-tag-option'  => 'filetypes:',
				)
			)
		);
		?>
		</label>
	</fieldset>

	<fieldset>
		<legend id="<?php echo esc_attr( $tgg->ref( 'limit-option-legend' ) ); ?>">
		<?php
			echo esc_html( __( 'File size limit (MB)', 'easy-file-uploader' ) );
		?>
		</legend>
		<label>
		<?php
		printf(
			'<span %1$s>%2$s</span><br />',
			wpcf7_format_atts(
				array(
					'id' => $tgg->ref( 'limit-option-description' ),
				)
			),
			esc_html( __( 'File maximum size in MB.', 'easy-file-uploader' ) )
		);

		printf(
			'<input %s />',
			wpcf7_format_atts(
				array(
					'type'             => 'number',
					'value'            => $max_file_size,
					'aria-labelledby'  => $tgg->ref( 'limit-option-legend' ),
					'aria-describedby' => $tgg->ref( 'limit-option-description' ),
					'data-tag-part'    => 'option',
					'data-tag-option'  => 'limit:',
				)
			)
		);
		?>
		</label>
	</fieldset>

	<fieldset>
		<legend id="<?php echo esc_attr( $tgg->ref( 'multifiles-option-legend' ) ); ?>">
		<?php
			echo esc_html( __( 'Multiple Files?', 'easy-file-uploader' ) );
		?>
		</legend>
		<label>
		<?php
		printf(
			'<span %1$s>%2$s</span><br />',
			wpcf7_format_atts(
				array(
					'id' => $tgg->ref( 'multifiles-option-description' ),
				)
			),
			esc_html( __( 'Check if you want to allow multiple files to be uploaded.', 'easy-file-uploader' ) )
		);

		printf(
			'<input %s />',
			wpcf7_format_atts(
				array(
					'type'             => 'checkbox',
					'value'            => 1,
					'aria-labelledby'  => $tgg->ref( 'multifiles-option-legend' ),
					'aria-describedby' => $tgg->ref( 'multifiles-option-description' ),
					'data-tag-part'    => 'option',
					'data-tag-option'  => 'multifiles:',
				)
			)
		);
		?>
		</label>
	</fieldset>

	<fieldset>
		<legend id="<?php echo esc_attr( $tgg->ref( 'maxfiles-option-legend' ) ); ?>">
		<?php
			echo esc_html( __( 'Maximum Files', 'easy-file-uploader' ) );
		?>
		</legend>
		<label>
		<?php
		printf(
			'<span %1$s>%2$s</span><br />',
			wpcf7_format_atts(
				array(
					'id' => $tgg->ref( 'maxfiles-option-description' ),
				)
			),
			esc_html( __( 'Maximum number of files that can be uploaded.', 'easy-file-uploader' ) )
		);

		printf(
			'<input %s />',
			wpcf7_format_atts(
				array(
					'type'             => 'number',
					'value'            => get_option( 'easy_dragdrop_max_files', '' ),
					'aria-labelledby'  => $tgg->ref( 'maxfiles-option-legend' ),
					'aria-describedby' => $tgg->ref( 'maxfiles-option-description' ),
					'data-tag-part'    => 'option',
					'data-tag-option'  => 'maxfiles:',
				)
			)
		);
		?>
		</label>
	</fieldset>
</div>

<footer class="insert-box">
	<?php
		$tgg->print( 'insert_box_content' );
		$tgg->print( 'mail_tag_tip' );
	?>
</footer>