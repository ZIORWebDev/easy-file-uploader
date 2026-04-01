import createdragDropInstance from './dragdrop/main.js';

$(document).ready(function () {
  const dragDropInstances = new Map();
  const dragDropUploaderFields = $('.easy-dragdrop-upload');
  const dragDropUploader = window.EasyDragDropUploader || {};

  dragDropUploader.allowMultiple = '1' === EasyDragDropUploader.allowMultiple;

  /**
   * Raised event before the FilePond instance is created
   * To allow developers to set global FilePond options
   */
  $(document).trigger(
    'easy_dragdrop_before_instance_created',
    dragDropUploader,
  );

  dragDropUploaderFields.each(function () {
    const inputConfig = getInputConfiguration($(this)) || {};
    const configuration = { ...dragDropUploader, ...inputConfig };

    for (const key in configuration) {
      if (!inputConfig[key] && dragDropUploader[key]) {
        configuration[key] = dragDropUploader[key];
      }
    }

    const submitButtonElement = $(this).closest('form').find('[type="submit"]');
    const submitButtonText = getSubmitButtonText(submitButtonElement);

    const dragDropInstance = createdragDropInstance($(this)[0], configuration);
    dragDropInstance.submitButtonElement = submitButtonElement;
    dragDropInstance.submitButtonText = submitButtonText;

    /**
     * Update the submit button when a file is added or processed
     */
    dragDropInstance.on('addfile', updateSubmitButton.bind(dragDropInstance));
    dragDropInstance.on(
      'processfiles',
      updateSubmitButton.bind(dragDropInstance),
    );

    /**
     * Raised event when a FilePond instance is created
     * @event easy_dragdrop_instance_created
     * @property {object} filePondInstance - The created FilePond instance
     */
    $(document).trigger('easy_dragdrop_instance_created', dragDropInstance);

    dragDropInstances.set(this, dragDropInstance);
  });

  /**
   * Get the text of the submit button
   * @param {jQuery} submitButtonElement - The jQuery object representing the submit button
   * @returns {string} The text of the submit button
   */
  function getSubmitButtonText(submitButtonElement) {
    const submitButtonText = submitButtonElement.is('button')
      ? submitButtonElement.find('.elementor-button-text').text().trim() ||
        submitButtonElement.text().trim()
      : submitButtonElement.is('input')
      ? submitButtonElement.val().trim()
      : '';

    return submitButtonText;
  }

  /**
   * Set the text of the submit button
   * @param {jQuery} submitButtonElement - The jQuery object representing the submit button
   * @param {string} submitButtonText - The text to set on the submit button
   */
  function setSubmitButtonText(submitButtonElement, submitButtonText) {
    if ($(submitButtonElement).is('input')) {
      $(submitButtonElement).val(submitButtonText);
    } else {
      const $textSpan = $(submitButtonElement).find('.elementor-button-text');
      if ($textSpan.length) {
        $textSpan.text(submitButtonText);
      } else {
        $(submitButtonElement).text(submitButtonText);
      }
    }
  }

  /**
   * Checks if any FilePond instance is uploading
   * @returns {boolean} True if any instance is uploading, false otherwise
   */
  function hasUploadingInstances() {
    for (const value of dragDropInstances.values()) {
      const files = value.getFiles();
      if (
        files.some(
          (file) => file.status !== 5 && ![6, 8, 10].includes(file.status),
        )
      ) {
        return true;
      }
    }

    return false;
  }

  /**
   * Update the submit button when a file is added or processed
   */
  function updateSubmitButton() {
    const submitButtonText = $(this)[0].submitButtonText;
    const submitButtonElement = $(this)[0].submitButtonElement;
    const isUploading = hasUploadingInstances();

    // If isUploading or this current the current instance, disable the submit button
    if (isUploading) {
      $(submitButtonElement).addClass('easy-dragdrop-upload-button--disabled');
      $(submitButtonElement).prop('disabled', isUploading);
      setSubmitButtonText(submitButtonElement, 'Uploading...');
    } else {
      $(submitButtonElement).removeClass(
        'easy-dragdrop-upload-button--disabled',
      );
      $(submitButtonElement).prop('disabled', false);
      setSubmitButtonText(submitButtonElement, submitButtonText);
    }
  }

  /**
   * Clears all FilePond instances when an Elementor form submission is successful.
   * @event submit_success
   * @param {Event} event - The event object
   * @param {object} response - The response object from the form submission
   */
  $(document).on('submit_success', function () {
    dragDropInstances.forEach((instance) => instance.removeFiles());
  });

  /**
   * Clears all FilePond instances when a CF7 form is successfully submitted.
   * @event wpcf7mailsent
   * @param {Event} event - The event object
   */
  $(document).on('wpcf7mailsent', function (event) {
    dragDropInstances.forEach((instance) => instance.removeFiles());
  });
});

/**
 * Retrieves input configuration from the file input element.
 * @param {jQuery} fileInput - The jQuery object representing the file input element.
 * @returns {object} The extracted configuration options.
 */
function getInputConfiguration(fileInput) {
  const data = $(fileInput).data();

  return {
    acceptedFileTypes: data.filetypes?.split(',') ?? null,
    allowMultiple: fileInput.attr('multiple') !== undefined,
    labelIdle: data.label ?? '',
    maxFileSize: data.filesize ? `${data.filesize}MB` : null,
    maxFiles: data.maxfiles ? parseInt(data.maxfiles) : null
  };
}
