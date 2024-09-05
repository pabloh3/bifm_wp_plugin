<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function bifm_widget_response($data, $run_id, $assistant_id, $thread_id, $tool_call_id){
    $authorized = filter_var($data['authorize'], FILTER_VALIDATE_BOOLEAN);
    if ($authorized) {
        $tool_message = __("User granted generic approval.", 'bifm');
        $user_id = get_current_user_id();
        $username = sanitize_user(get_user_meta($user_id, 'username', true));
        $encrypted_password = sanitize_text_field(get_user_meta($user_id, 'encrypted_password', true));
        if (!$username || !$encrypted_password) {
            wp_send_json_error(array('message' => __("Please set a username and password in the settings page.", 'bifm')));
        }
    } else {
        error_log("Rejected page edit.");
        $tool_message = __("User rejected your request for changes.", 'bifm');
        $user_id = NULL;
        $username = NULL;
        $encrypted_password = NULL;
    }

    // Ensure API_URL is set and sanitized
    global $BIFM_API_URL;
    $BIFM_API_URL = esc_url_raw($BIFM_API_URL);
    $url = $BIFM_API_URL . '/assistant_chat';
    $website = home_url();  // Current website URL
    $site_info = array(
        'website' => esc_url($website),
        'username' => $username,
        'password' => $encrypted_password,
    );

    // Sanitize the entire $data array
    $data = array_map('sanitize_text_field', $data);
    // Extract page / post elementor_data
    $post_id = intval($data['page']);
    $elementor_data = get_post_meta($post_id, '_elementor_data', true);
    //if elementor_data is empty make the post elementor editable
    if (empty($elementor_data)) {
        error_log("post_id: " . $post_id . " has empty elementor_data");
        //extract post body
        $post = get_post($post_id);
        $post_content = $post->post_content;
        // Escape the post content for JSON
        $escaped_content = wp_slash($post_content);
        // create elementor metadata
        $random_id = uniqid();
        $elementor_data = json_encode(
            [
                [
                    "id" => $random_id,
                    "elType" => "section",
                    "settings" => [],
                    "elements" => [
                        [
                            "id" => substr(str_replace('-', '', wp_generate_uuid4()), 0, 13),
                            "elType" => "column",
                            "settings" => ["_column_size" => 100],
                            "elements" => [
                                [
                                    "id" => substr(str_replace('-', '', wp_generate_uuid4()), 0, 13),
                                    "elType" => "widget",
                                    "settings" => ["editor" => $escaped_content],
                                    "elements" => [],
                                    "widgetType" => "text-editor"
                                ]
                            ],
                            "isInner" => false
                        ]
                    ],
                    "isInner" => false
                ]
            ]
        );
        
        update_post_meta($post_id, '_elementor_edit_mode', 'builder');
        update_post_meta($post_id, '_elementor_template_type', 'wp-post');
        update_post_meta($post_id, '_elementor_version', ELEMENTOR_VERSION); // Use current Elementor version
        update_post_meta($post_id, '_elementor_data', $elementor_data);
    
    }
        



    // Prepare and send the API request
    $response_tool = wp_remote_post($url, array(
        'headers' => array('Content-Type' => 'application/json'),
        'body' => wp_json_encode(array(
            'message' => null,
            'tool_outputs' => array(
                'tool_call_id' => $tool_call_id,
                'output' => $tool_message,
                'status' => 'execute_tool',
                'function' => array(
                    'name' => $data['callback_tool'],
                    'arguments' => $data,
                )
            ),
            'thread_id' => $thread_id,
            'assistant_id' => $assistant_id,
            'run_id' => $run_id,
            'site_info' => $site_info
        )),
        'method' => 'POST',
        'data_format' => 'body',
        'timeout' => 60 // Set the timeout (in seconds)
    ));

    // Check for errors in the API response
    if (is_wp_error($response_tool)) {
        error_log("API request failed: " . $response_tool->get_error_message());
        return wp_send_json_error(array('message' => __("API request failed.", 'bifm')));
    }

    return $response_tool;
}
?>
