<?php
// define base url for the API
//$API_URL = 'https://wp.builditforme.ai';
//change when working on local
$API_URL = 'http://127.0.0.1:5001';

// Handle change smart chat settings
// Add action for logged-in users
add_action('wp_ajax_bifm_smart_chat_settings', 'handle_bifm_smart_chat_settings');

// Function to handle form submission
function handle_bifm_smart_chat_settings() {
    error_log("in handle_bifm_smart_chat_settings");
    try {
        // Check for nonce security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'update-chat-settings-nonce')) {
            throw new Exception('Nonce verification failed!');
        }
        // in order to manage file updates, the front end passes a list of files not deleted by the user, we iterate through the files in the directory and delete the ones not in the list
        // The deleted files are stored in array $files_to_delete to be passed to the API for deletion by OpenAI
        // Update files_list on options
        if (isset($_POST['files_list'])) {
            $files_to_delete = array();
            $files_list_json = $_POST['files_list'];
            $files_list_json = stripslashes($files_list_json);
            $files_list_posted = json_decode($files_list_json, true);
            error_log("files list when saving as option: " . print_r($files_list_posted, true));

            $dirPath = plugin_dir_path(__FILE__) . 'shared-widgets/smart_chat/chat_files/';
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
                                unlink($dirPath . $file);
                                //remove the file from options
                                $file_list_stored = get_option('uploaded_file_names');
                                $file_list_stored_new = array();
                                foreach ($file_list_stored as $file_info) {
                                    // iterate through the files in the list and add them to a temp array, except the one to delete
                                    if ($file_info['filename'] != $file) {
                                        $file_list_stored_new[] = $file_info;
                                    }
                                    else {
                                        $files_to_delete[] = $file_info['file_id'];
                                    }
                                }
                                update_option('uploaded_file_names', $file_list_stored_new);
                            }
                        }
                    }
                    closedir($dh);
                }
            }
        }

        if (isset($_POST['assistant_instructions'], $_POST['assistant_instructions'])) {
            $assistant_instructions = $_POST['assistant_instructions'];
            update_option( 'assistant_instructions', $assistant_instructions);
            $assistant_id = get_option('assistant_id');
            $uploadedFiles = $_FILES['files'];
            $uploadedFile = $uploadedFiles['tmp_name'];
            //handle files
            if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
                $uploadedFiles = $_FILES['files'];
                //error log all the file's names
                for ($i = 0; $i < count($uploadedFiles['name']); $i++) {
                    error_log("uploaded file: " . $uploadedFiles['name'][$i]);
                }
        
                // Define the target directory
                $targetDir = plugin_dir_path(__FILE__) . 'shared-widgets/smart_chat/chat_files/';
        
                // Create the directory if it doesn't exist
                if (!file_exists($targetDir)) {
                    wp_mkdir_p($targetDir);
                }
        
                // Process each file
                $fileNames = []; // Array to store file data
                foreach ($uploadedFiles['name'] as $key => $name) {
                    // Set the target file path
                    $targetFile = $targetDir . basename($name);

                    // Validate and move the file
                    if (move_uploaded_file($uploadedFiles['tmp_name'][$key], $targetFile)) {
                        // File successfully uploaded
                        // Placeholder for file ID retrieval logic from OpenAI
                        $fileId = 'none'; // Set to 'none' if no file ID is provided
                        $fileNames[] = array('filename' => $name, 'file_id' => $fileId); // Store filename and file ID
                    } else {
                        // Handle errors, e.g., file couldn't be moved
                        wp_send_json_error('Error uploading file: ' . $name);
                        return;
                    }
                }

                // Store file names and IDs
                if (!empty($fileNames)) {
                    update_option('uploaded_file_names', $fileNames); // Store file data
                }

                // All files processed successfully
                wp_send_json_success('Files uploaded successfully.');
            } else {
                // No files or files are empty
            }
            //end handle files
            
            // Send the message to AI API
            global $API_URL;
            $url = $API_URL . '/assistant_update';

            $response = wp_remote_post($url, array(
                'headers' => array('Content-Type' => 'application/json'),
                'body' => json_encode(array(
                    'assistant_id' => $assistant_id,
                    'assistant_instructions' => stripslashes($assistant_instructions),
                    'files_to_delete' => $files_to_delete
                )),
                'method' => 'POST',
                'data_format' => 'body',
                'timeout' => 10 // Set the timeout (in seconds)
            ));
        
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                error_log("Error when calling chat update api");
                wp_send_json_error(array('message' => "Something went wrong: $error_message"), 500);
            } else {
                $status_code = wp_remote_retrieve_response_code($response);
                $response_body = json_decode(wp_remote_retrieve_body($response), true);
                if ($status_code == 200) {
                    $assistant_id = $response_body['assistant_id'];
                    update_option('assistant_id', $assistant_id);
                    $assistant_id_new = get_option('assistant_id');
                    error_log("assistant id: " . $assistant_id_new);
                    wp_send_json_success(array('message' => $response_body['message']));
                } else {
                    error_log($response_body['message']);
                    wp_send_json_error(array('message' => $response_body['message']), $status_code);
                }
            }
            //end message
            
        }

        wp_send_json_success('Settings saved successfully.');
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage(), 400);
    }
}
