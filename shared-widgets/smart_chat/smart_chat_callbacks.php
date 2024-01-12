<?php
//session info
if (!session_id()) {
    session_start();
}

//assistant start
function handle_chat_message() {
    error_log("back end handle message called");
    $message = $_POST['message'];
    // Send the message to AI API
    $response = callAPI($message);
}
function callAPI($message) {
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
    $url = 'http://localhost:5001/assistant_chat';

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
    if (is_wp_error($response)) {
        $error_response = $response->get_error_message() ? $response->get_error_message() : "Unknown error when calling chat API";
        error_log($error_response);
        wp_send_json_error(array('message' => "Something went wrong: $error_response"), 500);
    } else {
        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        if ($status_code == 200 && isset($response_body['message'])) {
            $thread_id = $response_body['thread_id'];
            $_SESSION['thread_id'] = $thread_id;
            wp_send_json_success(array('message' => $response_body['message']));
        } else {
            error_log("got a not 200 response");
            $error_response = isset($response_body['message']) ? $response_body['message'] : "Unknown error occurred";
            error_log("Error_message: ");
            error_log($error_response);
            wp_send_json_error(array('message' => $error_response), $status_code > 0 ? $status_code : 500);
        }
    }
    wp_die();
}
