import { create, registerPlugin } from "filepond";
import FilePondPluginFileValidateSize from "filepond-plugin-file-validate-size";
import FilePondPluginFileValidateType from "filepond-plugin-file-validate-type";
import dragDropUploader from "./helpers.js";

import "filepond/dist/filepond.css";
import "./style.css";

// Array of FilePond plugins to register
let filePondPlugins = [
    FilePondPluginFileValidateSize,
    FilePondPluginFileValidateType
];

// Allow developers to modify the plugin list via "easy_dragdrop_plugins" filter
filePondPlugins = dragDropUploader.applyFilters("easy_dragdrop_plugins", filePondPlugins);

// Register FilePond plugins
registerPlugin(...filePondPlugins);

/**
 * Generates the DragDrop configuration with security settings.
 * @param {object} configuration - The configuration object containing settings like maxFileSize and acceptedFileTypes.
 * @returns {object} The modified DragDrop configuration.
 */
function getDragDropConfiguration(configuration) {
    // Prepare the default configuration with security headers and callbacks.
    const defaultConfiguration = {
        credits: false,
        fileValidateTypeLabelExpectedTypes: "",
        labelMaxFileSize: "",
        server: {
            process: {
                method: "POST",
                headers: {
                    'X-WP-Nonce': wpApiSettings.nonce
                },
                ondata: (formData) => {
                    formData.append("size", configuration.maxFileSize);
                    formData.append("types", configuration.acceptedFileTypes.join(","));

                    return formData;
                },
                onerror: (response) => {
                    const responseItem = JSON.parse(response);

                    $(document).trigger("easy_dragdrop_upload_error", responseItem);

                    return responseItem?.error ?? "";
                },
                onload: (response) => {
                    const responseItem = JSON.parse(response);

                    // Trigger easy_dragdrop_upload_success event
                    $(document).trigger("easy_dragdrop_upload_success", responseItem);

                    return responseItem?.file_id ?? "";
                },
                url: `${configuration.rest}/upload`
            },
            revert: {
                headers: {
                    'X-WP-Nonce': wpApiSettings.nonce
                },
                url: `${configuration.rest}/delete`
            }
        }
    };

    return dragDropUploader.applyFilters("easy_dragdrop_configuration", Object.assign({}, configuration, defaultConfiguration));
}

/**
 * Creates a DragDrop instance for a given file input element.
 * @param {HTMLElement} fileInput - The file input element.
 * @param {object} [configuration] - Optional configuration settings.
 * @returns {object} The created DragDrop instance.
 */
function createDragDropInstance(fileInput, configuration = {}) {
    const DragDropConfiguration = getDragDropConfiguration(configuration);

    return create(fileInput, DragDropConfiguration);
}

export default createDragDropInstance;
