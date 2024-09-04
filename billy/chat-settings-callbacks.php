<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// define base url for the API
require ( __DIR__ . '/../bifm-config.php' );// define base url for the API
// Handle change smart chat settings
// Add action for logged-in users
add_action('wp_ajax_bifm_smart_chat_settings', 'bifm_smart_chat_settings');
add_action('wp_ajax_bifm_smart_chat_reset', 'bifm_smart_chat_reset');

function bifm_smart_chat_reset() {
    error_log("requested handle_bifm_smart_chat_reset");
    try {
        // Check for nonce security
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['nonce'])), 'update-chat-settings-nonce')) {
            throw new Exception(__('Nonce verification failed!','bifm'));
        }
        // if directory doesn't exist, create it
        $dirPath = wp_upload_dir()['basedir'] . '/bifm-files/chat_files/';
        if (!file_exists($dirPath)) {
            wp_mkdir_p($dirPath);
        }
        //delete any files in the directory
        // Check if directory exists and is readable
        if (is_dir($dirPath) && is_readable($dirPath)) {
            // Open the directory
            if ($dh = opendir($dirPath)) {
                // Read files from the directory
                while (($file = readdir($dh)) !== false) {
                    if ($file != "." && $file != "..") { // Exclude current and parent directory links
                        wp_delete_file($dirPath . $file);
                    }
                }
                closedir($dh);
            }
        }
        //reset the assistant
        $assistant_id = get_option('bifm_assistant_id');
        // call the api to delete the assistant
        global $BIFM_API_URL;
        $url = $BIFM_API_URL . '/delete_assistant';
        $response = wp_remote_post($url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => wp_json_encode(array(
                'bifm_assistant_id' => $assistant_id,
                'bifm_vector_store_id' => get_option('bifm_vector_store_id'),
            )),
            'method' => 'POST',
            'data_format' => 'body',
            'timeout' => 10 // Set the timeout (in seconds)
        ));
        //sometimes we need to reset because of an error with openai or the API, so if we can't delete the assistant, we still reset the options
        if (is_wp_error($response)) {
            //do nothing
        }
        update_option('bifm_assistant_instructions', '');
        update_option('bifm_uploaded_file_names', array());
        update_option('bifm_assistant_id', '');
        update_option('bifm_vector_store_id', NULL);
        // return confirmation
        wp_send_json_success(esc_html__('Chatbot reset successfully.','bifm'));
    } catch (Exception $e) {
        wp_send_json_error(esc_html($e->getMessage()), 400);
    }
}

function bifm_get_custom_upload_folder(){
    return wp_upload_dir()['basedir'] . '/bifm-files/chat_files/';
}


// Function to handle form submission
function bifm_smart_chat_settings() {
    try {
        // Check for nonce security
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['nonce'])), 'update-chat-settings-nonce')) {
            throw new Exception(__('Nonce verification failed!','bifm'));
        }
        
        // Handle files that need to be deleted
        $file_list_stored_new = array();
        $files_to_delete = array();

        // if directory doesn't exist, create it
        $dirPath = wp_upload_dir()['basedir'] . '/bifm-files/chat_files/';
        if (!file_exists($dirPath)) {
            wp_mkdir_p($dirPath);
        }
        // in order to manage file updates, the front end passes a list of files not deleted by the user, we iterate through the files in the directory and delete the ones not in the list
        // The deleted files are stored in array $files_to_delete to be passed to the API for deletion by OpenAI
        if (isset($_POST['files_list']) && is_array($_POST['files_list'])) {

            $files_list_json = wp_unslash($_POST['files_list']); //phpcs:ignore Array is sanitized in the next few lines
            $files_list_json = stripslashes($files_list_json);
            $files_list_posted = json_decode($files_list_json, true);
            if (!is_array($files_list_posted)) {
                throw new Exception(__('Invalid file list format.','bifm'));
            }
            $files_list_posted = array_map('sanitize_text_field', $files_list_posted);

            // Check if directory exists and is readable
            if (is_dir($dirPath) && is_readable($dirPath)) {
                // Open the directory
                if ($dh = opendir($dirPath)) {
                    // Read files from the directory
                    while (($file = readdir($dh)) !== false) {
                        if ($file != "." && $file != "..") { // Exclude current and parent directory links
                            // Check if the file is in the files_list_array
                            $file_in_list = false;
                            foreach ($files_list_posted as $file_info) {
                                if ($file_info == $file) {
                                    $file_in_list = true;
                                    break;
                                }
                            }

                            // Delete the file if it is not in the list
                            if (!$file_in_list) {
                                error_log("file to delete: " . $file);
                                wp_delete_file($dirPath . $file);
                                //remove the file from options
                                $file_list_stored = get_option('bifm_uploaded_file_names');
                                foreach ($file_list_stored as $key => $value) {
                                    // iterate through the files in the list and add them to a temp array, except the one to delete
                                    if ($value['file_name'] != $file) {
                                        $files_list_stored_new[] = ['file_name' => $value['file_name'], 'file_id' => $value['file_id']];
                                    }
                                    else {
                                        $file_to_delete_id = $value['file_id'];
                                        array_push($files_to_delete, $file_to_delete_id);
                                    }
                                }
                                update_option('bifm_uploaded_file_names', $file_list_stored_new);
                            }
                        }
                    }
                    closedir($dh);
                }
            }
        }

        // Handle assistant update and new files
        if (isset($_POST['bifm_assistant_instructions'], !empty($_POST['bifm_assistant_instructions']))) {
            $assistant_instructions = sanitize_text_field(wp_unslash($_POST['bifm_assistant_instructions']));
            update_option( 'bifm_assistant_instructions', $assistant_instructions);
            $assistant_id = get_option('bifm_assistant_id');
            $uploadedFiles = $_FILES['files']; //No need to sanitize the assignment of $_FILES['files'] because $_FILES is a superglobal array that contains information about files uploaded via an HTML form. However, the data within this array is validated sanitized before processing.
            $uploadedFile = $uploadedFiles['tmp_name']; // No need to sanitize. Contains the path to the temporary file that PHP has stored on the server during the upload process. This value is automatically generated by PHP and typically looks something like /tmp/phpYzdqkD. Since this value is managed by the server and not user-controlled, it doesn’t require sanitization with sanitize_file_name.
            global $BIFM_API_URL;
            //handle files
            if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
                $uploadedFiles = $_FILES['files']; 
        
                // Define the target directory as the /bifm-files/chat_files/ directory in uploads
                $targetDir = bifm_get_custom_upload_folder();
        
                // Create the directory if it doesn't exist
                if (!file_exists($targetDir)) {
                    wp_mkdir_p($targetDir);
                }

                // Process each file
                // in order for python to identify the file, we needed to define a boundary to indicate where the file starts, that doesn't contain special chars
                $boundary = 'UYTvLhK1u09E5OyhWJBqEZPS';
                $headers = array('Content-Type' => 'multipart/form-data; boundary=' . $boundary);
                $file_list_query = get_option('bifm_uploaded_file_names');
                foreach ($uploadedFiles['name'] as $key => $name) {
                    $name = sanitize_file_name($name); // Sanitize the file name
                    // Set the target file path
                    $targetFile = $targetDir . basename($name);
                    // Check for any errors during upload
                    if ($uploadedFiles['error'][$key] !== UPLOAD_ERR_OK) {
                        throw new Exception(__('File upload error: ', 'bifm') . $uploadedFiles['error'][$key]);
                    }
                    // Validate the file type
                    $filetype = wp_check_filetype($name);
                    $allowed_types = array('txt', 'pdf', 'docx', 'doc', 'csv', 'xls', 'xlsx', 'ppt', 'pptx'); // Adjust allowed file types as needed
                    if (!in_array($filetype['ext'], $allowed_types)) {
                        throw new Exception(__('File type not allowed: ', 'bifm') . $filetype['ext']);
                    }
                    // Validate the file
                    if (!is_uploaded_file($uploadedFiles['tmp_name'][$key])) {
                        throw new Exception(__('Invalid file upload.','bifm'));
                    }

                    // Manually create multipart content
                    $body = '';
                    $fileElement = array(
                      'name'     => $uploadedFiles['name'][$key],
                      'type'     => $uploadedFiles['type'][$key],
                      'tmp_name' => $uploadedFiles['tmp_name'][$key],
                      'error'    => $uploadedFiles['error'][$key],
                      'size'     => $uploadedFiles['size'][$key]
                    );
                    add_filter( 'upload_dir', 'bifm_get_custom_upload_folder' );
                    wp_handle_upload($fileElement);
                    remove_filter( 'upload_dir', 'bifm_get_custom_upload_folder' );
                    //if () {
                        // Check if the file exists and is readable
                        //error_log("file was stored in back end");
                        if (file_exists($targetFile) && is_readable($targetFile)) {
                            $vector_store_id = get_option('bifm_vector_store_id');
                            // Start building the body
                            // Add vector_store_id to the multipart/form-data body
                            $body .= '--' . $boundary . "\r\n";
                            $body .= 'Content-Disposition: form-data; name="vector_store_id"' . "\r\n\r\n";
                            $body .= $vector_store_id . "\r\n";
                            // Add the file to the body
                            $file_content = wp_remote_get($targetFile);
                            $body .= '--' . $boundary . "\r\n";
                            $body .= 'Content-Disposition: form-data; name="file"; filename="' . basename($name) . '"' . "\r\n";
                            $body .= 'Content-Type: ' . mime_content_type($targetFile) . "\r\n\r\n";
                            $body .= $file_content . "\r\n";
                        }
                    //} 
                    /*else {
                        // Handle errors, e.g., file couldn't be moved
                        error_log("Error uploading file: " . $name);
                        wp_send_json_error('Error uploading file: ' . $name);
                        return;
                    }*/
                    $body .= '--' . $boundary . '--';

                    // Send the file to the Flask API
                    error_log("sending file to flask api");
                    $file_response = wp_remote_post($BIFM_API_URL . '/upload_file', array(
                        'headers' => $headers,
                        'body' => $body,
                        'method' => 'POST',
                        'timeout' => 60
                    ));
                    
                    // Check for errors or non 200 responses
                    if (is_wp_error($file_response)) {
                        error_log("Error when calling file upload api");
                        // delete file if there is an error
                        $targetFile = $targetDir . basename($name);
                        wp_delete_file($targetFile);
                        $error_message = $file_response->get_error_message();
                        wp_send_json_error(array('message' => "Something went wrong: $error_message"), 500);
                        return;
                    } else {
                        $status_code = wp_remote_retrieve_response_code($file_response);
                        $response_body = json_decode(wp_remote_retrieve_body($file_response), true);
                        if ($status_code == 200) {
                            $file = $response_body['files'];
                            $file_list_query[] = ['file_name' => $file['file_name'], 'file_id' => $file['file_id']];
                            // Store file names and IDs
                            update_option('bifm_uploaded_file_names', $file_list_query); // Store file data
                            $vector_store_id = $file['bifm_vector_store_id'];
                            update_option('bifm_vector_store_id', $vector_store_id);
                        } else {
                            // delete file if there is an error
                            error_log("deleting file: " . $name);
                            $targetFile = $targetDir . basename($name);
                            wp_delete_file($targetFile);
                            wp_send_json_error(array('message' => $response_body['message']), $status_code);
                            return;
                        }
                    }
                }
            } else {
                // No files or files are empty which is ok
            }
            //end handle files
            
            // Send the message to AI API
            $url = $BIFM_API_URL . '/assistant_update';
            $list_file_ids = array();
            $file_list_stored = get_option('bifm_uploaded_file_names');
            foreach ($file_list_stored as $key => $value) {
                array_push($list_file_ids, $value['file_id']);
            }

           // Collect basic setup info
            global $wp_version;
            $site_info = __('The site is running on WordPress version: ','bifm') . $wp_version . '. ';
            $theme = wp_get_theme();
            $site_info .= __('The theme is: ','bifm') . $theme->get('Name') . __(' Version: ','bifm') . $theme->get('Version') . '. ';

            // Check if Elementor is active
            if (in_array('elementor/elementor.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                $elementor_data = get_plugin_data(WP_PLUGIN_DIR . '/elementor/elementor.php');
                $site_info .= __('Elementor is active. Please guide the user to use Elementor for editing pages. ','bifm');
                $site_info .= __('Elementor Version: ','bifm') . $elementor_data['Version'] . '. ';
                
                // Check Elementor experiments
                $experiments_manager = \Elementor\Plugin::$instance->experiments;
                $is_flex_active = $experiments_manager->is_feature_active('flexbox-layout');
                $is_grid_active = $experiments_manager->is_feature_active('container');
                
                $site_info .= __('Flexbox Layout is ','bifm') . ($is_flex_active ? 'active' : 'not active') . '. ';
                $site_info .= __('Grid Layout is ','bifm') . ($is_grid_active ? 'active' : 'not active') . '. ';
            } else {
                $other_editors = [];
                
                // Check if Gutenberg is active
                if (function_exists('has_blocks')) {
                    $other_editors[] = 'Gutenberg';
                }
                
                // Array of popular page builders
                $page_builders = [
                    'classic-editor/classic-editor.php', // Classic Editor
                    'beaver-builder-lite-version/fl-builder.php', // Beaver Builder
                    'divi-builder/divi-builder.php', // Divi Builder
                    'js_composer/js_composer.php', // WPBakery Page Builder
                    'thrive-visual-editor/thrive-visual-editor.php', // Thrive Architect
                    'brizy/brizy.php' // Brizy
                ];
                
                // Check each page builder
                foreach ($page_builders as $builder) {
                    if (in_array($builder, apply_filters('active_plugins', get_option('active_plugins')))) {
                        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $builder);
                        $other_editors[] = $plugin_data['Name'] . ' Version: ' . $plugin_data['Version'];
                    }
                }
                
                // Append other editors to site info
                if (!empty($other_editors)) {
                    $site_info .= 'Page builders active: ' . implode(', ', $other_editors) . '. ';
                }
            }



            $response = wp_remote_post($url, array(
                'headers' => array('Content-Type' => 'application/json'),
                'body' => wp_json_encode(array(
                    'bifm_assistant_id' => $assistant_id,
                    'bifm_assistant_instructions' => stripslashes($assistant_instructions),
                    'assistant_files' => $list_file_ids,
                    'files_to_delete' => $files_to_delete,
                    'site_url' => get_site_url(),
                    'site_theme' => $site_info,
                    'bifm_vector_store_id' => get_option('bifm_vector_store_id'),
                )),
                'method' => 'POST',
                'data_format' => 'body',
                'timeout' => 10 // Set the timeout (in seconds)
            ));
        
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                error_log("Error when calling chat update api");
                wp_send_json_error(array('message' => __("Something went wrong: ",'bifm').$error_message), 500);
            } else {
                $status_code = wp_remote_retrieve_response_code($response);
                $response_body = json_decode(wp_remote_retrieve_body($response), true);
                if ($status_code == 200) {
                    $assistant_id = $response_body['bifm_assistant_id'];
                    update_option('bifm_assistant_id', $assistant_id);
                    $vector_store_id = $response_body['bifm_vector_store_id'];
                    update_option('bifm_vector_store_id', $vector_store_id);
                    // check if there's any message in the response
                    if (isset($response_body['message'])) {
                        wp_send_json_success(array('message' => $response_body['message']));
                    } else {
                        wp_send_json_success(array('message' => "Settings saved successfully."));
                    }
                } else {
                    error_log("got a non 200 response on handle chat settings: " . $response_body['message']);
                    wp_send_json_error(array('message' => $message), $status_code);
                }
            }            
        }

        wp_send_json_success(__('Settings saved successfully.','bifm'));
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage(), 400);
    }
}
