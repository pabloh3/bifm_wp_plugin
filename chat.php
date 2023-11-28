<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$api_url = "https://wp.builditforme.ai";
#change url when working on local
#$api_url = "http://127.0.0.1:5000";

add_action('wp_ajax_plugin_send_message', 'bifm_handle_plugin_send_message');
function bifm_handle_plugin_send_message() {
    // Check nonce for security
    check_ajax_referer('my-plugin-nonce', 'nonce');

    // Get the message from the frontend
    $messageBody = isset($_POST['messageBody']) ? sanitize_text_field($_POST['messageBody']) : '';
    $stage = isset($_POST['stage']) ? sanitize_text_field($_POST['stage']) : '';
    $user_id = get_current_user_id();
    $site_url = home_url();
    $user_id_complete = "site=" . $site_url . "&user=" . $user_id;
    $folderName = isset($_POST['folderName']) ? sanitize_text_field($_POST['folderName']) : '';
    #put together the url by concatenating with the api_url
    global $api_url; 
    $url = $api_url . "/{$folderName}/processgpt";
    $response = wp_remote_post($url, array(
        'headers' => array('Content-Type' => 'application/json'),
        'body' => json_encode(array(
            'message' => $messageBody,
            'stage' => $stage,
            'user_info' => $user_id_complete  // Here's where the user ID is added
        )),
        'method' => 'POST',
        'data_format' => 'body'
    ));

    // Check for errors
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        wp_send_json_error(array('message' => "Something went wrong:" .  esc_html__($error_message)));
    } else {
        // Get the status code from the external server's response
        $status_code = wp_remote_retrieve_response_code($response);

        // Return the output and status code to the frontend
        wp_send_json(array(
            'data' => wp_remote_retrieve_body($response),
            'status' => $status_code,
        ), $status_code);  // Here's where we set the HTTP status code to 202
    }

    // Always die at the end of AJAX functions in WordPress
    wp_die("Reached end without success message");
}



add_action('wp_ajax_my_plugin_poll_action', 'handle_my_plugin_poll_action');
function handle_my_plugin_poll_action() {
    // Check nonce for security
    check_ajax_referer('my-plugin-nonce', 'nonce');

    // Get the folderName and jobId from the frontend
    $folderName = isset($_POST['folderName']) ? sanitize_text_field($_POST['folderName']) : '';
    $jobId = isset($_POST['jobId']) ? sanitize_text_field($_POST['jobId']) : '';

    // Construct the URL for the external service
    global $api_url; 
    $url = $api_url . "/{$folderName}/results/{$jobId}";

    // Send a GET request to the external service
    $response = wp_remote_get($url);

    // Check for errors
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        wp_send_json_error(array('message' => "Something went wrong:" .  esc_html__($error_message)));
    } else {
        // Get the status code from the external server's response
        $status_code = wp_remote_retrieve_response_code($response);

        // Return the output and status code to the frontend
        wp_send_json(array(
            'data' => wp_remote_retrieve_body($response),
            'status' => $status_code
        ),$status_code);
    }

    // Always die at the end of AJAX functions in WordPress
    wp_die("Reached end without success message");
}

// handle fatal error when saving
function handle_fatal_error($dir_path, $widget_name) {
    $error = error_get_last();
    error_log("Error type: " . $error['type']);
    error_log("Error message: " . $error['message']);
    // list of what each error is: https://www.php.net/manual/en/errorfunc.constants.php
    if ($error && in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR, 4096))) {
        
        error_log("############### handle fatal error running dir: " . $dir_path);
        error_log("widget: " . $widget_name);
        // this function lives in widget-manager.php
        remove_widget($dir_path,  $widget_name);
    
        wp_send_json_error(array('message' => "A fatal error occurred while saving the widget '{$widget_name}'. The widget has been deleted. Please check the widget data and try again."));
        // Optionally, log the error for debugging
        // error_log(print_r($error, true));
    }
}


add_action('wp_ajax_plugin_save', 'bifm_handle_plugin_save');
function bifm_handle_plugin_save() {
    $error_encountered = false;
    // Check nonce for security
    check_ajax_referer('my-plugin-nonce', 'nonce');

    // Get the folderName and widget name from the frontend
    $folderName = isset($_POST['folderName']) ? sanitize_text_field($_POST['folderName']) : '';
    $full_widget_name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $widget_name = strtolower(str_replace(' ', '_', $full_widget_name));
    $widget_name = preg_replace('/[^a-z0-9_]/', '', $widget_name);
    
    // Check if the directory already exists
    $dir_path = plugin_dir_path(__FILE__) . 'bifm-widgets/' . $widget_name;
    if (file_exists($dir_path)) {
        $error_encountered = true;
        wp_send_json_error(array('message' => 'A widget with this name already exists.'));
    }

    // register the function to be called in case of a fatal error
    register_shutdown_function(function() use ($dir_path, $widget_name) {
        handle_fatal_error($dir_path, $widget_name);
    });
    
    // call the API to save
    global $api_url; 
    $url = $api_url . "/{$folderName}/save/{$full_widget_name}";
    $response = wp_remote_post($url, array(
        'headers' => array('Content-Type' => 'application/json'),
        // Removed the 'body' parameter since $messageBody is not defined
        'method' => 'POST',
        'data_format' => 'body'
    ));
    // Check for errors
    if (is_wp_error($response)) {
        $error_encountered = true;
        $error_message = $response->get_error_message();
        wp_send_json_error(array('message' => "Something went wrong:" .  esc_html__($error_message)));
    } 
    
    //Saving the widget
    
    $rollback_required = false;
    $backup_directory = null;
    
     try {

        // Create the directory if it doesn't exist
        $dir_path = plugin_dir_path(__FILE__) . 'bifm-widgets/' . $widget_name;
        if (!file_exists($dir_path)) {
            if (!mkdir($dir_path, 0755, true)) {
                $error_encountered = true;
                wp_send_json_error(array('message' => 'Failed to create directory.'));
            }
        }
    
        // Create files based on the server's response
        $response_body = wp_remote_retrieve_body($response);
        $decoded_response = json_decode($response_body, true);
        
        if (isset($decoded_response['files']) && is_array($decoded_response['files'])) {
            $saved_files = [];
            error_log("saving files: ");
            foreach ($decoded_response['files'] as $file) {
                if (isset($file['file_name']) && isset($file['file_data'])) {
                    $file_name = $file['file_name'];
                    if ($file_name == 'widget_final.php') {
                        $file_name = $widget_name. '.php';
                    }
                    $decoded_file = base64_decode($file['file_data']);
                    $file_path = $dir_path . '/' . $file_name;
                    
                    // check that the new file doesn't return errors
                    if (!lint_php_code($decoded_file)) {
                        error_log("New widget failed lint");
                        // Delete all files created for this widget to ensure consistency
                        remove_widget($dir_path, $widget_name);
                        wp_send_json_error(array('message' => "The widget's code contains errors and cannot be saved."));
                    } else {
                        // Save the file
                        if (file_put_contents($file_path, $decoded_file) === false) {
                            $error_encountered = true;
                            wp_send_json_error(array('message' => "Failed to save file: {$file_name}"));
                        }
                        $saved_files[] = $file_path;
                    }
                }
            }
        
            if (!empty($saved_files)) {
                // Check if the widget is already registered
                $widget_registration_file = plugin_dir_path(__FILE__) . 'widget-registration.php';
                $widget_registration_content = file_get_contents($widget_registration_file);
                $widget_function_name = "register_custom_widget_{$widget_name}";
            
                if (strpos($widget_registration_content, $widget_function_name) === false) {
                    // Widget not registered, so append the registration script
                    $widget_registration_script = "\n// Register custom widget: {$widget_name}\n";
                    $widget_registration_script .= "add_action('elementor/widgets/widgets_registered', '{$widget_function_name}');\n";
                    $widget_registration_script .= "function {$widget_function_name}() {\n";
                    $widget_registration_script .= "    // Include the widget class file\n";
                    $widget_registration_script .= "    require_once(__DIR__ . '/bifm-widgets/{$widget_name}/{$widget_name}.php');\n";
                    $widget_registration_script .= "    \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new " . '\\' . "{$widget_name}());\n";
                    $widget_registration_script .= "}\n";
                    
                    // check that the new file doesn't return errors
                    if (!lint_php_code($widget_registration_script)) {
                        error_log("New widget failed lint");
                        // Delete all files created for this widget to ensure consistency
                        remove_widget($dir_path, $widget_name);
                        $error_encountered = true;
                        wp_send_json_error(array('message' => "The widget's code contains errors and cannot be saved."));
                    } else {
                        file_put_contents($widget_registration_file, $widget_registration_script, FILE_APPEND);
                    }
                }
    
            } else {
                $error_encountered = true;
                wp_send_json_error(array('message' => 'No valid file data received from the server.'));
            }
        } else {
            $error_encountered = true;
            wp_send_json_error(array('message' => 'No file data received from the server.'));
        }
     } catch (Exception $e) {
        $rollback_required = true;
        wp_send_json_error(array('message' => 'An error occurred: ' . esc_html__($e->getMessage())));
    
    } finally {
        // 3. Rollback on error
        if ($rollback_required) {
            remove_widget($dir_path,  $widget_name);
        }
        else {
           wp_send_json_success(array(
                'message' => 'Files saved successfully!',
                'file_paths' => $saved_files
            ));
        }
    }
    
    // Always die at the end of AJAX functions in WordPress
    wp_die('Save ended without a response');
}


function lint_php_code($code) {
    // Create a temporary file
    $temp_file = tempnam(sys_get_temp_dir(), 'lint');
    file_put_contents($temp_file, $code);
    
    //debug delete
    $output0 = shell_exec("echo 'Hello World'");
    error_log("Printing text: " . $output0);
    error_log("temp file: " . $temp_file);
    //check php version
    #$output1 = shell_exec("/Users/pabloh/Library/Application Support/Local/lightning-services/php-8.2.10+1/bin/darwin/bin/php -v");
    #error_log("php version: " . $output1);

    // Lint the file
    $output = shell_exec("php -l " . escapeshellarg($temp_file));
    error_log("Lint test output: " . $output);
    // Delete the temporary file
    unlink($temp_file);
    // If the output contains "No syntax errors", the lint was successful
    return strpos($output, "No syntax errors") !== false;
}


add_action('wp_ajax_plugin_reset', 'bifm_handle_plugin_reset');
function bifm_handle_plugin_reset() {
    // Check nonce for security
    check_ajax_referer('my-plugin-nonce', 'nonce');

    // Get the folderName and widget name from the frontend
    $folderName = isset($_POST['folderName']) ? sanitize_text_field($_POST['folderName']) : '';
    global $api_url; 
    $url = $api_url . "/{$folderName}/reset";
    $response = wp_remote_post($url, array(
        'headers' => array('Content-Type' => 'application/json'),
        // Removed the 'body' parameter since $messageBody is not defined
        'method' => 'POST',
        'data_format' => 'body'
    ));

    // Check for errors
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        wp_send_json_error(array('message' => "Something went wrong:" .  esc_html__($error_message)));
    }
    
    wp_send_json_success(array(
        'message' => 'Files saved successfully!',
    ));

    // Always die at the end of AJAX functions in WordPress
    wp_die('Save ended without a response');
}


// Handle the undo request
add_action('wp_ajax_plugin_undo', 'bifm_handle_plugin_undo');
function bifm_handle_plugin_undo() {
    // Check nonce for security
    check_ajax_referer('my-plugin-nonce', 'nonce');

    // Get the folderName and widget name from the frontend
    $folderName = isset($_POST['folderName']) ? sanitize_text_field($_POST['folderName']) : '';
    global $api_url; 
    $url = $api_url . "/{$folderName}/undo";
    $response = wp_remote_post($url, array(
        'headers' => array('Content-Type' => 'application/json'),
        // Removed the 'body' parameter since $messageBody is not defined
        'method' => 'POST',
        'data_format' => 'body'
    ));

    // Check for errors
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        wp_send_json_error(array('message' => "Something went wrong:" .  esc_html__($error_message)));
    }
    
    wp_send_json_success(array(
        'message' => 'Files saved successfully!',
    ));

    // Always die at the end of AJAX functions in WordPress
    wp_die('Save ended without a response');
}

