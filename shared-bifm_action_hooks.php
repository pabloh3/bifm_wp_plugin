<?php

include_once plugin_dir_path( __FILE__ ) . './shared-widgets/smart_chat/smart_chat_callbacks.php';
// Register the AJAX actions.
add_action('wp_ajax_send_chat_message', 'handle_chat_message');
add_action('wp_ajax_nopriv_send_chat_message', 'handle_chat_message'); // For handling requests from non-logged-in users

    
?>