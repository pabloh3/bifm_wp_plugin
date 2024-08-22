<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// handle deleting widgets
add_action('wp_ajax_delete_custom_widget', 'bifm_delete_custom_widget_callback');
function bifm_delete_custom_widget_callback() {    
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['nonce'])), 'my_custom_action')) {
        wp_send_json_error(__('Invalid nonce','bifm'));
        exit;
    }

    $widget_name = isset($_POST['widget_name']) ? sanitize_text_field($_POST['widget_name']) : '';
    //$widget_folder_path = __DIR__ . '/bifm-widgets/' . $widget_name;
    $widget_folder_path = wp_upload_dir()['basedir'] . '/bifm-files/bifm-widgets/' . $widget_name;

    // Delete the widget action hook in the uploads /backend_functions.php file
    $action_hooks_file = wp_upload_dir()['basedir'] . '/bifm-files/bifm_action_hooks.php';
    $action_hooks_content = file_get_contents($action_hooks_file); //phpcs:ignore
    /*Find teh string that starts with "include_once plugin_dir_path( __FILE__ ) . './bifm-widgets/{$widget_name}/backend_functions.php';...<a bunch of content>" and ends with two consecutive line breaks, remove it
    */ 
    $action_hooks_content = preg_replace("/include_once plugin_dir_path\( __FILE__ \) \. '\/bifm-widgets\/{$widget_name}\/backend_functions.php';.*?\n\n/s", '', $action_hooks_content);
    bifm_create_file($action_hooks_file, $action_hooks_content);

    // delete any action hooks in options that start with this widget name + _
    $hooks = get_option('bifm_action_hooks', []);
    foreach ($hooks as $hook_name => $widget_name_in_hook) {
        error_log("borrando hook: " . $hook_name . " widget: " . $widget_name);
        if (strpos($widget_name_in_hook, $widget_name) === 0) {
            unset($hooks[$hook_name]);
        }
    }
    update_option('bifm_action_hooks', $hooks);

    // Delete the widget folder
    try {
        bifm_remove_widget($widget_folder_path, $widget_name);
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
    wp_send_json_success();
}

function bifm_remove_widget($dir,  $widget_name) {
    // delete corresponding widget folder
    bifm_delete_folder($dir);
    error_log("widget removed from widget_registration.php");

    // Retrieve the existing widget names
    $widget_names = get_option('bifm_widget_names', []);
    if (in_array($widget_name, $widget_names)) {
        // Remove the widget name from the array
        $widget_names = array_filter($widget_names, function($name) use ($widget_name) {
            return $name !== $widget_name;
        });

        // Update the option with the modified list of widget names
        update_option('bifm_widget_names', array_values($widget_names));

        // Optionally log this action
        error_log("Deleted widget name: {$widget_name}");

    } else {
        // Log a message if the widget name does not exist
        error_log("The widget named '{$widget_name}' does not exist and cannot be deleted.");
    }
}
 

// Handle create new widget
add_action('wp_ajax_get_folder_name', 'bifm_get_folder_name_callback');
function bifm_get_folder_name_callback() {
    if (is_user_logged_in()){
        $current_user = wp_get_current_user();
        $user_email = $current_user->user_email;
        $user_id = get_current_user_id();
    } else {
        wp_send_json_error(__('User not logged in to wordpress.','bifm'));
        return;
    }
    
    $site_url = home_url();
    $user_id_complete = "site=" . $site_url . "&user=" . $user_id;
    $plugin_data = get_plugin_data(__FILE__);
    $version = $plugin_data['Version'];
    // Prepare the body data
    $body_data = array(
        'current_user' => $current_user->display_name,
        'user_email' => $user_email,
        'user_id' => $user_id_complete,
        'site_url' => $site_url,
        'plugin_version' => $version
    );
    global $BIFM_API_URL;
    $api_endpoint = $BIFM_API_URL . '/assign_foldername';
    $response = wp_remote_post($api_endpoint, array(
        'method' => 'POST',
        'headers' => array(
          'Content-Type' => 'application/json'
        ),
        'body' => wp_json_encode($body_data)
    ));

    if (is_wp_error($response)) {
        wp_send_json_error(__('Failed to fetch folder name from API.' ,'bifm') );
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['folderName'])) {
        $client_folder = $data['folderName'];
    
        // Create the URL for the redirection
        $redirect_url = admin_url('admin.php?page=create-widget&foldername='. $client_folder);
        // Send a JSON response with the URL
        wp_send_json_success(['redirectUrl' => $redirect_url]);
    } else if (isset($data['warning'])) {
        wp_send_json_error($data['warning']);
    } else {
        wp_send_json_error(__('Invalid response from API.','bifm') );
    }
}

?>