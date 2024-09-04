<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require ( __DIR__ . '/../bifm-config.php' );// define base url for the API

$custom_log_file = WP_CONTENT_DIR . '/custom.log';
//session info
if (!session_id()) {
    session_start();
}


//assistant start
add_action('wp_ajax_send_chat_message', 'bifm_handle_chat_message');
function bifm_handle_chat_message() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['nonce'])), 'billy-nonce')) {
        wp_send_json_error(array('message' => __("Couldn't verify user",'bifm') ), 500);
    } 

    if(!is_array($_POST['message']))
        $message = sanitize_text_field($_POST['message']);
    else {
        $message = $_POST['message']; // each element of this array is sanitized in the next lines
        foreach ($message as $key => $value) {
            if(is_string($value)){
                $message[$key] = sanitize_text_field($value);
            } elseif (is_array($value)) {
                // If the element is an array, recursively sanitize its elements
                $message[$key] = array_map(function($item) {
                    return is_string($item) ? sanitize_text_field($item) : $item;
                }, $value);
            } else {
                $message[$key] = $value; // booleans, ints, doubles, etc. are not sanitized
            }
        }
    }
    $tool_call_id = sanitize_text_field($_POST['tool_call_id']);
    $widget_name = sanitize_text_field($_POST['widget_name']);
    $run_id = sanitize_text_field($_POST['run_id']);
    // Send the message to AI API
    //commenting out to build out widget!!!
    $response = bifm_call_api($message, $widget_name, $run_id, $tool_call_id);
}


// Call the API
function bifm_call_api($message, $widget_name, $run_id, $tool_call_id) {
    $assistant_id = get_option('bifm_assistant_id');
    if ($assistant_id === false) {
        update_option('bifm_assistant_instructions', '');
        update_option('bifm_uploaded_file_names', array());
        update_option('bifm_assistant_id', NULL);
        update_option('bifm_vector_store_id', NULL);
    }
    if (isset($_SESSION['thread_id'])) {
        $thread_id = sanitize_text_field($_SESSION['thread_id']);
    } else {
        $thread_id = null;
    }

    global $BIFM_API_URL;
    $url = $BIFM_API_URL . '/assistant_chat';

    if ($widget_name == null) {
        $response = wp_remote_post($url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => wp_json_encode(array(
                'message' => $message,
                'thread_id' => $thread_id,
                'assistant_id' => $assistant_id
            )),
            'method' => 'POST',
            'data_format' => 'body',
            'timeout' => 60 // Set the timeout (in seconds)
        ));

    } else {
        $response = bifm_widget_submission($message, $run_id, $widget_name, $assistant_id, $thread_id, $tool_call_id);
    }

    if (is_wp_error($response)) {
        $error_response = $response->get_error_message() ? $response->get_error_message() : "Unknown error when calling chat API";
        error_log($error_response);
        wp_send_json_error(array('message' => __("Something went wrong:",'bifm').$error_response), 500);
    } else {
        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        if ($status_code == 202) {
            // log response array as string
            $jobId = $response_body['jobId'];
            wp_send_json_success(array('jobId' => $jobId), 202);
        } else if ($status_code == 200) {
            bifm_handle_response($response_body, $message);
        } else {
            $error_response = isset($response_body['message']) ? $response_body['message'] : __("API for chat returned an error with code: ",'bifm') .  $status_code;
            error_log("Error trying to call API on smart chat: " . $error_response);
            wp_send_json_error(array('message' => $error_response), $status_code > 0 ? $status_code : 500);
        }
    }
    wp_die();
}

// endpoint for polling
function bifm_billy_check_job_status() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['nonce'])), 'billy-nonce')) {
        wp_send_json_error(array('message' => __("Couldn't verify user",'bifm') ), 500);
    }
    $jobId = sanitize_text_field($_POST['jobId']);
    $message = sanitize_text_field($_POST['message']);

    global $BIFM_API_URL;
    $url = $BIFM_API_URL . 'chat_job_status/' . $jobId;

    $response = wp_remote_get($url, array('timeout' => 60));

    if (is_wp_error($response)) {
        $error_response = $response->get_error_message() ? $response->get_error_message() : __("Unknown error when checking job status",'bifm');
        error_log("Error in checking chat job status: " . $error_response);
        wp_send_json_error(array('message' => __("Something went wrong:",'bifm').$error_response), 500);
    } else {
        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        if ($status_code == 200) {
            // If this is the first time calling the assistant, store the assistant ID
            if ((!isset($assistant_id) || $assistant_id == NULL) && isset($response_body['site_info'])) {
                $site_info = $response_body['site_info'];
                if (isset($site_info['assistant_id'])) {
                    update_option('bifm_assistant_id', $site_info['assistant_id']);
                }
            }
            bifm_handle_response($response_body, $message);
        } else if ($status_code == 202) {
            // log response array
            $response_data = json_decode(wp_remote_retrieve_body($response), true);
            //In progress
            $jobId = isset($response_data['jobId']) ? $response_data['jobId'] : null;
            wp_send_json_success(array('jobId' => $jobId), 202);
        } else {
            $error_response = isset($response_body['message']) ? $response_body['message'] : "API for job status returned an error with code: " .  $status_code;
            error_log("Error_message checking Billy job status: " . $error_response);
            wp_send_json_error(array('message' => $error_response), $status_code > 0 ? $status_code : 500);
        }
    }
    wp_die();
}
add_action('wp_ajax_bifm_billy_check_job_status', 'bifm_billy_check_job_status');


// Handle response from API
function bifm_handle_response($body, $message) {
    // Case where just a regular chat response
    if (isset($body['thread_id'])) {
        $_SESSION['thread_id'] = $body['thread_id'];
        $thread_id = $body['thread_id'];
        // Get the existing thread data from the WP option
        $thread_data = get_option('bifm_assistant_thread_data', array());

        // Check if the thread ID is not already in the list of threads
        if (!array_key_exists($thread_id, $thread_data)) {
            // Create a snippet of the first 20 characters of the message
            $message_snippet = substr($message, 0, 20) . '...';
            // Add the new thread ID with its message snippet
            $thread_data[$thread_id] = $message_snippet;
        }

        // Make sure only the last 5 threads are kept
        if (count($thread_data) > 5) {
            // Removes the oldest thread ID
            array_shift($thread_data); 
        }

        // Update the option with the new list of thread data
        update_option('bifm_assistant_thread_data', $thread_data);
    }

    if (!isset($body['status']) || $body['status'] == 'chatting' || $body['status'] == 'error') {
        if (isset($body['data'])) {
            wp_send_json_success(array('message' => $body['data']));
        } else {
            wp_send_json_success(array('message' => $body['message']));
        }
    //
    } else {
        if ($body['status'] == 'reply_with_widget' || $body['status'] == 'needs_authorization') {
            $tool_name = $body['tool_name'];
            // eventually get rid of this filter, might want a list of approved tools to check against
            try {
                $parameters = $body['data']['parameters'];
                $tool_call_id = $body['tool_call_id'];
                $run_id = $body['run_id'];
                // to do, estamos pasando empty run id y tool call id 
                $response = bifm_include_widget($tool_name, $parameters, $run_id, $tool_call_id);
                if (isset($response['thread_id'])) {
                    $_SESSION['thread_id'] = $response['thread_id'];
                }
                wp_send_json_success(array('tool' => true, 'message' => "", 'widget_object' => $response));
            } catch (Exception $e) {
                wp_send_json_success(array('message' => __('There was an error in getting ','bifm') . $tool_name . '.'));
            }
        }
    }
}

// add action for case where bifm_new_chat is clicked
add_action('wp_ajax_bifm_new_chat', 'bifm_new_chat');
function bifm_new_chat() {
    //check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['nonce'])), 'billy-nonce')) {
        wp_send_json_error(array('message' => __("Couldn't verify user",'bifm')), 500);
    }
    $assistant_id = get_option('bifm_assistant_id');
    if ($assistant_id === false) {
        wp_send_json_error(array('message' => __("Your admin hasn't configured the smart chat in the BIFM plugin.",'bifm')), 500);
        wp_die();
    }
    // clear the thread_id from session
    unset($_SESSION['thread_id']);
    $thread_id = null;
    //return success
    wp_send_json_success(array('message' => __('New chat started','bifm'), 'thread_id' => $thread_id));
}

/* Load an old thread */
add_action('wp_ajax_bifm_load_billy_chat', 'bifm_load_billy_chat'); // wp_ajax_{action} for logged-in users
function bifm_load_billy_chat() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['nonce'])), 'billy-nonce')) {
        wp_send_json_error(array('message' => __("Couldn't verify user",'bifm')), 500);
    }
    if (!session_id()) {
        session_start();
    }
    // if no thread_id is passed, load current thread
    if (!isset($_POST['thread_id']) && isset($_SESSION['thread_id'])) {
            $thread_id = sanitize_text_field($_SESSION['thread_id']);
    } else {
        $thread_id = sanitize_text_field($_POST['thread_id']);
        $_SESSION['thread_id'] = $thread_id; // Set the new current thread ID
    }

    $thread_ids = get_option('bifm_assistant_thread_data');
    if (($key = array_search($thread_id, $thread_ids)) !== false) {
        unset($thread_ids[$key]);
        array_unshift($thread_ids, $thread_id); // Move this thread to the top
        update_option('bifm_assistant_thread_data', $thread_ids);
    }

    // Call the API to get the thread
    global $BIFM_API_URL;
    $url = $BIFM_API_URL . '/load_thread'; // Make sure this matches your Flask route
    // post to the API
    $response = wp_remote_post($url, array(
        'headers' => array('Content-Type' => 'application/json'),
        'body' => wp_json_encode(array(
            'thread_id' => $thread_id
        )),
        'data_format' => 'body',
        'timeout' => 60 // Set the timeout (in seconds)
    ));
    
    if ($response instanceof WP_Error) {
        error_log("Response to load thread contains an error.");
        wp_send_json_error(array('message' => __("Couldn't load thread: ",'bifm') . $response->get_error_message()), 500);
    } elseif (wp_remote_retrieve_response_code($response) != 200) {
        error_log("Response to load thread is not 200.");
        wp_send_json_error(array('message' => __("Couldn't load thread: ",'bifm') . wp_remote_retrieve_response_message($response)), 500);
    } else {
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        error_log("Response body status: ", $response_body['status']);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        wp_send_json_success($data);
    }
    /* translators: %s: thread ID */
    wp_send_json_success(array('message' => sprintf(__("Thread %s loaded and moved up.",'bifm'),$thread_id) ),200);
}




// handle widgets //
function bifm_include_widget($widget_name, $parameters, $run_id, $tool_call_id) {
    include_once __DIR__ . '/../billy-widgets/validate-' . $widget_name . '/validate-' . $widget_name . '.php';
    $response = bifm_get_widget($parameters, $run_id, $tool_call_id);
    return $response;
}

//widget submission
function bifm_widget_submission($message, $run_id, $widget_name, $assistant_id, $thread_id, $tool_call_id) {
    include_once __DIR__ . '/../billy-widgets/validate-' . $widget_name . '/response-' . $widget_name . '.php';
    $response = bifm_widget_response($message, $run_id, $assistant_id, $thread_id, $tool_call_id);
    return $response;
}


// Handle agreement saving
function bifm_save_agreement() {
    // Check nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['nonce'])), 'billy-nonce')) {
        wp_send_json_error(array('message' => __("Couldn't verify user",'bifm') ), 500);
    }

    // Get the current user
    $user_id = get_current_user_id();
    if ($user_id == 0) {
        wp_send_json_error('User not logged in');
        return;
    }

    // Update user meta
    update_user_meta($user_id, 'accepted_terms_conditions', true);
    error_log("Agreement saved for user $user_id");

    // Send a success response
    wp_send_json_success(__('Agreement saved successfully','bifm'));
}
add_action('wp_ajax_bifm_save_agreement', 'bifm_save_agreement');
