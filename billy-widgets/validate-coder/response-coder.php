<?php
if ( ! defined( 'ABSPATH' ) ) exit;
function bifm_widget_response($data, $run_id, $assistant_id, $thread_id, $tool_call_id){
    $authorized = $data['authorize'];
    if ($authorized === true or $authorized === "true"){
        error_log("requesting API access");
        // to do grant API access
        $tool_message = __("redirecting to coder",'bifm');
    } else {
        error_log("Rejected going to coder");
        $tool_message = __("User rejected going to coder",'bifm');
    }

    global $BIFM_API_URL;
    $url = $BIFM_API_URL . '/assistant_chat';
    $website = home_url();  // Current website URL
    $site_info = array(
        'website' => $website,
    );

    $response_tool = wp_remote_post($url, array(
        'headers' => array('Content-Type' => 'application/json'),
        'body' => wp_json_encode(array(
            'message' => null,
            'tool_outputs' => array(
                'tool_call_id' => $tool_call_id,
                'output' => $tool_message,
                'status' => 'execute_tool',
                'function' => array(
                    'name' => 'coder',
                    'arguments' => array(),
                    'id' => $tool_call_id,
                )
            ),
            'thread_id' => $thread_id,
            'bifm_assistant_id' => $assistant_id,
            'run_id' => $run_id,
            'site_info' => $site_info
        )),
        'method' => 'POST',
        'data_format' => 'body',
        'timeout' => 60 // Set the timeout (in seconds)
    ));
    return $response_tool;
}


