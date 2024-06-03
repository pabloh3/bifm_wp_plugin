<?php
function widget_response($data, $run_id, $assistant_id, $thread_id, $tool_call_id){
    $keyphrase = $data['keyphrase'];
    $authorized = $data['authorize'];
    if ($authorized === true or $authorized === "true"){
        error_log("requesting blog creation");
        $response = create_blog($keyphrase,1,"No category");
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $tool_message = "An error occurred when writing blog post";
        } else {
            $tool_message = "Blog post request successfull, will be ready for the user to view in 2 minutes";
        }
    } else {
        error_log("not authorized blog creation");
        $tool_message = "User did not authorize blog creation";

    }

    global $API_URL;
    $url = $API_URL . '/assistant_chat';

    $response_tool = wp_remote_post($url, array(
        'headers' => array('Content-Type' => 'application/json'),
        'body' => json_encode(array(
            'message' => null,
            'tool_outputs' => array(
                'tool_call_id' => $tool_call_id,
                'output' => $tool_message
            ),
            'thread_id' => $thread_id,
            'assistant_id' => $assistant_id,
            'run_id' => $run_id
        )),
        'method' => 'POST',
        'data_format' => 'body',
        'timeout' => 60 // Set the timeout (in seconds)
    ));
    return $response_tool;
}


