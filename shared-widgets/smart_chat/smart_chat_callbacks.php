<?php
//session info
if (!session_id()) {
    session_start();
}

//test assistant start
function handle_chat_message() {
    error_log("back end handle message called");
    $message = $_POST['message'];
    // Send the message to AI API
    $response = callAPI($message);
}

function callAPI($message) {
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
            'assistant_id' => 'asst_7sJDgGA1Xhm5ckklkwUiPEIF'
        )),
        'method' => 'POST',
        'data_format' => 'body',
        'timeout' => 30 // Set the timeout (in seconds)
    ));

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log("error when calling chat api");
        wp_send_json_error(array('message' => "Something went wrong: $error_message"), 500);
    } else {
        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        if ($status_code == 200) {
            error_log("200");
            error_log($response_body['message']);
            $thread_id = $response_body['thread_id'];
            $_SESSION['thread_id'] = $thread_id;
            wp_send_json_success(array('message' => $response_body['message']));
        } else {
            error_log("not 200");
            error_log($response_body['message']);
            wp_send_json_error(array('message' => $response_body['message']), $status_code);
        }
    }

    wp_die();
}
