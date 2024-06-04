<?php
include __DIR__ . '/bifm-config.php';

$custom_log_file = WP_CONTENT_DIR . '/custom.log';
//session info
if (!session_id()) {
    session_start();
}


//assistant start
add_action('wp_ajax_send_chat_message', 'handle_chat_message');
function handle_chat_message() {
    error_log("handle_chat_message called");
    error_log("nonce received: " . $_POST['nonce']);
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'billy-nonce')) {
        wp_send_json_error(array('message' => "Couldn't verify user"), 500);
    }
    error_log("back end handle message called");
    $message = $_POST['message'];
    $tool_call_id = $_POST['tool_call_id'];
    $widget_name = $_POST['widget_name'];
    $run_id = $_POST['run_id'];
    // Send the message to AI API
    //commenting out to build out widget!!!
    $response = callAPI($message, $widget_name, $run_id, $tool_call_id);
}

function callAPI($message, $widget_name, $run_id, $tool_call_id) {
    $assistant_id = get_option('assistant_id');
    error_log("Assistant ID: " . $assistant_id);
    if ($assistant_id === false) {
        wp_send_json_error(array('message' => "Your admin hasn't configured the smart chat in the BIFM plugin."), 500);
        wp_die();
    }
    if (isset($_SESSION['thread_id'])) {
        $thread_id = $_SESSION['thread_id'];
    } else {
        $thread_id = null;
    }

    global $API_URL;
    $url = $API_URL . '/assistant_chat';

    if ($widget_name == null) {
        $response = wp_remote_post($url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode(array(
                'message' => $message,
                'thread_id' => $thread_id,
                'assistant_id' => $assistant_id
            )),
            'method' => 'POST',
            'data_format' => 'body',
            'timeout' => 60 // Set the timeout (in seconds)
        ));

    } else {
        $response = widget_submission($message, $run_id, $widget_name, $assistant_id, $thread_id, $tool_call_id);
    }
    if (is_wp_error($response)) {
        $error_response = $response->get_error_message() ? $response->get_error_message() : "Unknown error when calling chat API";
        error_log($error_response);
        wp_send_json_error(array('message' => "Something went wrong: $error_response"), 500);
    } else {
        $status_code = wp_remote_retrieve_response_code($response);
        error_log("Status code: " . $status_code);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        if ($status_code == 200) {
            handle_response($response_body);
        } else {
            error_log("Got a not 200 response" . $status_code);
            if (isset($response_body['message'])){
                $error_response = $response_body['message'];
            } elseif (isset($response_body['error'])){
                $error_response =  $response_body['error'];
            } else{
                $error_response = "API for chat returned an error with code: " .  $status_code;
            }        
            error_log("Error_message: ");
            error_log($error_response);
            wp_send_json_error(array('message' => $error_response), $status_code > 0 ? $status_code : 500);
        }
    }
    wp_die();
}

//handle response from API
function handle_response($body) {
    // case where just a regular chat response
    if (isset($body['thread_id'])) {
        $_SESSION['thread_id'] = $body['thread_id'];
    }
    if (!isset($body['status']) || $body['status'] == 'chatting' || $body['status'] == 'error') {
        if (isset($body['data'])) {
            wp_send_json_success(array('message' => $body['data']));
        } else {
            wp_send_json_success(array('message' => $body['message']));
        }
    //
    } else {
        if ($body['status'] == 'needs_authorization') {
            $tool_name = $body['tool_name'];
            // eventually get rid of this filter, might want a list of approved tools to check against
            if ($tool_name == 'writer') {
                $parameters = $body['data']['parameters'];
                $tool_call_id = $body['tool_call_id'];
                $run_id = $body['run_id'];
                // to do, estamos pasando empty run id y tool call id 
                $response = include_widget($tool_name, $parameters, $run_id, $tool_call_id);
                if (isset($response['thread_id'])) {
                    $_SESSION['thread_id'] = $response['thread_id'];
                }
                wp_send_json_success(array('tool' => true, 'message' => "", 'widget_object' => $response));
            } else {
                wp_send_json_success(array('message' => 'Please authorize ' . $tool_name . ' to continue'));
            }
        }
    }
}

function include_widget($widget_name, $parameters, $run_id, $tool_call_id) {
    include_once __DIR__ . '/billy-widgets/validate-' . $widget_name . '/validate-' . $widget_name . '.php';
    $response = get_widget($parameters, $run_id, $tool_call_id);
    return $response;
}

//widget submission
function widget_submission($message, $run_id, $widget_name, $assistant_id, $thread_id, $tool_call_id) {
    include_once __DIR__ . '/billy-widgets/validate-' . $widget_name . '/response-' . $widget_name . '.php';
    $response = widget_response($message, $run_id, $assistant_id, $thread_id, $tool_call_id);
    return $response;
}