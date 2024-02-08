<?php
// define base url for the API
require 'bifm-config.php';
// Handle change smart chat settings
// Add action for logged-in users
add_action('wp_ajax_bifm_smart_chat_settings', 'handle_bifm_smart_chat_settings');
add_action("wp_ajax_bifm_smart_chat_reset", "handle_bifm_smart_chat_reset");

function handle_bifm_smart_chat_reset() {
    error_log("requested handle_bifm_smart_chat_reset");
    try {
        // Check for nonce security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'update-chat-settings-nonce')) {
            throw new Exception('Nonce verification failed!');
        }
        //delete any files in the directory
        $dirPath = wp_upload_dir()['basedir'] . '/bifm-files/chat_files/';
        // Check if directory exists and is readable
        if (is_dir($dirPath) && is_readable($dirPath)) {
            // Open the directory
            if ($dh = opendir($dirPath)) {
                // Read files from the directory
                while (($file = readdir($dh)) !== false) {
                    if ($file != "." && $file != "..") { // Exclude current and parent directory links
                        unlink($dirPath . $file);
                    }
                }
                closedir($dh);
            }
        }
        //reset the assistant
        $assistant_id = get_option('assistant_id');
        // call the api to delete the assistant
        global $API_URL;
        $url = $API_URL . '/delete_assistant';
        $response = wp_remote_post($url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode(array(
                'assistant_id' => $assistant_id
            )),
            'method' => 'POST',
            'data_format' => 'body',
            'timeout' => 10 // Set the timeout (in seconds)
        ));
        //sometimes we need to reset because of an error with openai or the API, so if we can't delete the assistant, we still reset the options
        if (is_wp_error($response)) {
            //do nothing
        }
        update_option('assistant_instructions', '');
        update_option('uploaded_file_names', array());
        update_option('assistant_id', '');
        // return confirmation
        wp_send_json_success('Chatbot reset successfully.');
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage(), 400);
    }
}


// Function to handle form submission
function handle_bifm_smart_chat_settings() {
    error_log("in handle_bifm_smart_chat_settings");
    try {
        // Check for nonce security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'update-chat-settings-nonce')) {
            throw new Exception('Nonce verification failed!');
        }
        
        // Handle files that need to be deleted
        $file_list_stored_new = array();
        $files_to_delete = array();
        // in order to manage file updates, the front end passes a list of files not deleted by the user, we iterate through the files in the directory and delete the ones not in the list
        // The deleted files are stored in array $files_to_delete to be passed to the API for deletion by OpenAI
        if (isset($_POST['files_list'])) {
            $files_list_json = $_POST['files_list'];
            $files_list_json = stripslashes($files_list_json);
            $files_list_posted = json_decode($files_list_json, true);

            $dirPath = wp_upload_dir()['basedir'] . '/bifm-files/chat_files/';
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
                                update_option('uploaded_file_names', $file_list_stored_new);
                            }
                        }
                    }
                    closedir($dh);
                }
            }
        }

        // Handle assistant update and new files
        if (isset($_POST['assistant_instructions'], $_POST['assistant_instructions'])) {
            $assistant_instructions = $_POST['assistant_instructions'];
            update_option( 'assistant_instructions', $assistant_instructions);
            $assistant_id = get_option('assistant_id');
            $uploadedFiles = $_FILES['files'];
            $uploadedFile = $uploadedFiles['tmp_name'];
            global $API_URL;
            //handle files
            if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
                $uploadedFiles = $_FILES['files'];
        
                // Define the target directory as the /bifm-files/chat_files/ directory in uploads
                $targetDir = wp_upload_dir()['basedir'] . '/bifm-files/chat_files/';
        
                // Create the directory if it doesn't exist
                if (!file_exists($targetDir)) {
                    wp_mkdir_p($targetDir);
                }
        
                // Process each file
                //$boundary = 'pC&5CFTcj#L33e&O%pI*xua8';
                $boundary = 'UYTvLhK1u09E5OyhWJBqEZPS';
                //$headers = array('Content-Type' => 'multipart/form-data; boundary=' . $boundary);
                $headers = array('Content-Type' => 'multipart/form-data; boundary=' . $boundary);
                $file_list_query = get_option('uploaded_file_names');
                foreach ($uploadedFiles['name'] as $key => $name) {
                    // Manually create multipart content
                    $body = '';
                    // Set the target file path
                    $targetFile = $targetDir . basename($name);

                    if (move_uploaded_file($uploadedFiles['tmp_name'][$key], $targetFile)) {
                        // Check if the file exists and is readable
                        error_log("file was stored in back end");
                        if (file_exists($targetFile) && is_readable($targetFile)) {
                        // Start building the body
                            $file_content = file_get_contents($targetFile);
                            $body .= '--' . $boundary . "\r\n";
                            $body .= 'Content-Disposition: form-data; name="file"; filename="' . basename($name) . '"' . "\r\n";
                            $body .= 'Content-Type: ' . mime_content_type($targetFile) . "\r\n\r\n";
                            $body .= $file_content . "\r\n";
                        }
                    } else {
                        // Handle errors, e.g., file couldn't be moved
                        error_log("Error uploading file: " . $name);
                        wp_send_json_error('Error uploading file: ' . $name);
                        return;
                    }
                    $body .= '--' . $boundary . '--';

                    // Send the file to the Flask API
                    error_log("sending file to flask api");
                    $file_response = wp_remote_post($API_URL . '/upload_file', array(
                        'headers' => $headers,
                        'body' => $body,
                        'method' => 'POST',
                        'timeout' => 60
                    ));
                    
                    // Check for errors or non 200 responses
                    if (is_wp_error($file_response)) {
                        // delete file if there is an error
                        $targetFile = $targetDir . basename($name);
                        unlink($targetFile);
                        $error_message = $file_response->get_error_message();
                        error_log("Error when calling file upload api");
                        wp_send_json_error(array('message' => "Something went wrong: $error_message"), 500);
                        return;
                    } else {
                        $status_code = wp_remote_retrieve_response_code($file_response);
                        $response_body = json_decode(wp_remote_retrieve_body($file_response), true);
                        if ($status_code == 200) {
                            $file = $response_body['files'];
                            $file_list_query[] = ['file_name' => $file['file_name'], 'file_id' => $file['file_id']];
                            // Store file names and IDs
                            update_option('uploaded_file_names', $file_list_query); // Store file data
                        } else {
                            // delete file if there is an error
                            error_log("deleting file: " . $name);
                            $targetFile = $targetDir . basename($name);
                            unlink($targetFile);
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
            $url = $API_URL . '/assistant_update';
            $list_file_ids = array();
            $file_list_stored = get_option('uploaded_file_names');
            foreach ($file_list_stored as $key => $value) {
                array_push($list_file_ids, $value['file_id']);
            }
            $response = wp_remote_post($url, array(
                'headers' => array('Content-Type' => 'application/json'),
                'body' => json_encode(array(
                    'assistant_id' => $assistant_id,
                    'assistant_instructions' => stripslashes($assistant_instructions),
                    'assistant_files' => $list_file_ids,
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