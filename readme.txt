=== Easy DragDrop File Uploader ===
Contributors: ziorwebdev, reygcalantaol  
Tags: file upload, elementor, dragdrop, drag and drop, ajax upload
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 1.1.8
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

Enhances Elementor Pro Forms and Contact Form 7 with a drag and drop uploader for seamless file uploads.

== Description ==

**Easy DragDrop File Uploader** is a WordPress plugin that integrates a drag and drop uploader with Elementor Pro Forms and Contact Form 7 (CF7), allowing seamless file uploads with advanced features like image previews, drag-and-drop, and asynchronous uploads.

### **Key Features**
- **Drag & Drop Uploads** – Simplifies file uploads with an intuitive UI.
- **Asynchronous Uploads** – Ensures faster performance without reloading the page.
- **Secure Upload Handling** – Adheres to WordPress security best practices.
- **Customizable Settings** – Configure file size limits, allowed file types, and more.
- **Styled for Elementor** – Seamlessly integrates with Elementor Pro Forms.
- **Forms Supported** – Elementor Pro Form and Contact Form 7 (CF7).

**Get the <a href="https://ziorweb.dev/plugin/easy-dragdrop-file-uploader-pro" target="_blank">premium</a> version for more features.**

== Installation ==

1. Install and activate the plugin via **Plugins → Add New Plugin** in WordPress.
2. Go to **Settings → DragDrop Uploader** to configure the plugin.
3. Edit an Elementor Pro Form and add the **DragDrop Upload** field.

== Frequently Asked Questions ==

= What forms are supported by the Easy DragDrop File Uploader? =  
Easy DragDrop File Uploader currently supports Elementor Pro Forms and Contact Form 7 (CF7).

= Where are uploaded files stored? =  
By default, uploaded files are stored in the WordPress uploads directory.

= Can I restrict the allowed file types? =  
Yes, you can set allowed file types in the plugin settings.

= Where can I find the full source code of this plugin? =  
The full source code is publicly available at [GitHub Repository](https://github.com/ZIORWebDev/easy-dragdrop-file-uploader).

= What library is used for the file upload functionality? =  
This plugin uses the [FilePond](https://pqina.nl/filepond/) library to handle file uploads efficiently and securely.

== Screenshots ==

1. Screenshot of the plugin settings page.
2. Screenshot of DragDrop upload field in the frontend.
3. Screenshot of the Elementor form builder with the DragDrop upload field.

== Changelog ==

= 1.1.8 =
- Fix: Elementor editor preview for drag and drop uploader field

= 1.1.6 =
- Add default settings values for button label, max file size, and file types

= 1.1.3 =
- Move plugin settings to Easy DragDrop File Uploader -> Settings

= 1.1.2 =
- Patch form-data dependency to version 4.0.4
- Added filter hook "easy_dragdrop_temp_file_path",
- Added action hook "easy_dragdrop_upload_failure"

= 1.1.0 =
- Integrate DragDrop with CF7 Form.
- Improve FAQ.

= 1.0.2 =
- Initial release.
- Integrated DragDrop with Elementor Pro Forms.
- Added admin settings for file restrictions.

== License ==

This plugin is licensed under **GPL-2.0-or-later**.

