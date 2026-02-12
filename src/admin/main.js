import "./main.css";

/**
 * If elementor does not exists, exit.
 */
if (!elementor) {
    return;
}

/**
 * Render the file uploader field in the editor.
 */
elementor.hooks.addFilter('elementor_pro/forms/content_template/field/easy-dragdrop-upload', function (inputField, item, i, settings) { 
    const itemClasses = _.escape(item.css_classes),
    required = '',
    multiple = '',
    fieldName = 'form_field_';

    if (item.required) {
        required = 'required';
    }

    if (item.allow_multiple_upload) {
        multiple = ' multiple="multiple"';
        fieldName += '[]';
    }

    return '<input size="1"  type="file" class="elementor-file-field elementor-field elementor-size-' + settings.input_size + ' ' + itemClasses + '" name="' + fieldName + '" id="form_field_' + i + '" ' + required + multiple + ' >';
}, 10, 4);

