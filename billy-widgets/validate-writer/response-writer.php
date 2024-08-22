<?php
if ( ! defined( 'ABSPATH' ) ) exit;
function bifm_widget_response($data, $run_id, $assistant_id, $thread_id, $tool_call_id){
    $authorized = $data['authorize'];
    if ($authorized === true or $authorized === "true"){
        if (!isset($data['keyphrase'])){
            $keyphrase = NULL;
        } else {
            $keyphrase = $data['keyphrase'];
        }
        error_log("requesting blog creation");
        $response = bifm_create_blog($keyphrase,1,"No category");
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $tool_message = __("An error occurred when writing blog post",'bifm');
        } else {
            $tool_message = __("Blog post request successfull, will be ready for the user to view in 2 minutes",'bifm');
        }
    } else {
        error_log("not authorized blog creation");
        $tool_message = __("User did not authorize blog creation",'bifm');
    }

    global $BIFM_API_URL;
    $url = $BIFM_API_URL . '/assistant_chat';

    $response_tool = wp_remote_post($url, array(
        'headers' => array('Content-Type' => 'application/json'),
        'body' => wp_json_encode(array(
            'message' => null,
            'tool_outputs' => array(
                'tool_call_id' => $tool_call_id,
                'output' => $tool_message
            ),
            'thread_id' => $thread_id,
            'bifm_assistant_id' => $assistant_id,
            'run_id' => $run_id
        )),
        'method' => 'POST',
        'data_format' => 'body',
        'timeout' => 60 // Set the timeout (in seconds)
    ));
    return $response_tool;
}


