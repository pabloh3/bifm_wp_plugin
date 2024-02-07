<?php
/*
Plugin Name: Build It For Me - AI creator
Description: Ask a bot to create for you.
Version: 1.0.4
Author: Build It For Me
*/
// include the WordPress HTTP API
include_once(ABSPATH . WPINC . '/http.php');
require 'bifm-config.php';
$current_version = '1.0.4';

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function builditforme_ewm_admin_menu() {
    add_menu_page(
        'Build It For Me AI',
        'Build It For Me',
        'edit_posts',
        'bifm-plugin', //this is the slug used for updates
        'ewm_admin_page_content',
        'dashicons-welcome-widgets-menus'
    );
    
    add_submenu_page(
        null, //'elementor-widget-manager',
        'Create Widget Page',
        'Create Widget',
        'edit_posts',
        'create-widget',
        'ewm_create_widget_content'
    );
    
    add_submenu_page(
        'elementor-blog-manager',
        'Create Blog Page',
        'Create Blog',
        'edit_posts',
        'create-blog',
        'ewm_create_blog_content'
    );
        
    add_submenu_page(
        'elementor-chat-manager',
        'Smart Chat Settings',
        'Smart Chat',
        'edit_posts',
        'create-chat',
        'ewm_create_chat_content'
    );
}
add_action('admin_menu', 'builditforme_ewm_admin_menu');

function bifm_enqueue_scripts() {
    if (isset($_GET['page'])) {
        if ($_GET['page'] == 'create-blog') {
            // Enqueue scripts for the blog page
            wp_enqueue_script('cbc_script', plugins_url('/static/blog-creator-script.js', __FILE__), array('jquery'), '1.0.69', true);

            // Localize the script with your data
            $translation_array = array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'single_post_nonce' => wp_create_nonce('create-single-post-action'),
                'bulk_upload_nonce' => wp_create_nonce('bulk-upload-csv-action')
            );
            // error log the entire translation array
            wp_localize_script('cbc_script', 'cbc_object', $translation_array);
            
        } elseif ($_GET['page'] == 'create-chat') {
            // Enqueue scripts for the chat page
            wp_enqueue_script('cbc_script_chat', plugins_url('/static/smart-chat-script.js', __FILE__), array('jquery'), '1.0.3', true); 
            // Localize the script with your data
            $translation_array = array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('update-chat-settings-nonce'),
            );
            // error log the entire translation array
            wp_localize_script('cbc_script_chat', 'cbc_script_object_chat', $translation_array);
            
        } 
    }
}

add_action('admin_enqueue_scripts', 'bifm_enqueue_scripts');


function ewm_admin_page_content() {
    //this code was taken to admin-page.php
    include plugin_dir_path(__FILE__) . 'admin-page.php';
    // Externalize JavaScript Code
    wp_enqueue_script('my-custom-script', plugin_dir_url(__FILE__) . 'static/admin-page.js', array('jquery'), '1.0.2', true);
}

function ewm_create_widget_content() {
    //this code was taken to admin-page.php
    include plugin_dir_path(__FILE__) . 'client.php';
}

function ewm_create_blog_content() {
    //this code was taken to admin-page.php
    include plugin_dir_path(__FILE__) . 'blog-creator-page.php';
}

function ewm_create_chat_content() {
    //this code was taken to admin-page.php
    include plugin_dir_path(__FILE__) . 'smart-chat-page.php';
}

function builditforme_ewm_enqueue_admin_scripts($hook) {
    global $pagenow;
    
    // Check if we're on the create-widget page to load that JS
    if ($pagenow == 'admin.php' && isset($_GET['page']) && ($_GET['page'] == 'create-widget')) {
        // Enqueue CSS & JavaScript
        wp_enqueue_style('my-plugin-styles', esc_url(plugins_url('static/styles.css', __FILE__)),'','1.0.8', false);
        wp_enqueue_script('my-plugin-script', plugin_dir_url(__FILE__) . 'static/main.js', array('jquery'), '1.0.76', true);
        // Pass ajax_url to script.js
        wp_localize_script('my-plugin-script', 'my_plugin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('my-plugin-nonce')
        ));
    }
    else {
        # in all admin pages load the styles
        wp_enqueue_style('my-plugin-styles', plugins_url('static/styles.css', __FILE__),'','1.0.7', false);
    }
}
add_action('admin_enqueue_scripts', 'builditforme_ewm_enqueue_admin_scripts');

// handle deleting widgets
add_action('wp_ajax_delete_custom_widget', 'delete_custom_widget_callback');
function delete_custom_widget_callback() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'my_custom_action')) {
        wp_send_json_error('Invalid nonce');
        exit;
    }

    $widget_name = isset($_POST['widget_name']) ? sanitize_text_field($_POST['widget_name']) : '';

    $widget_folder_path = __DIR__ . '/bifm-widgets/' . $widget_name;

    // Delete the widget folder
    remove_widget($widget_folder_path, $widget_name);

    wp_send_json_success();
}

function remove_widget($dir,  $widget_name) {
    // Remove registration from widget-registration.php
    $registration_file = __DIR__ . '/widget-registration.php';
    $file_contents = file_get_contents($registration_file);
    $pattern = "/\/\/ Register custom widget: " . preg_quote($widget_name, '/') . ".*?\}/s";
    $file_contents = preg_replace($pattern, '', $file_contents);
    file_put_contents($registration_file, $file_contents);
    error_log("widget removed from widget_registration.php");
    rrmdir($dir);
}

function rrmdir($dir){
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . "/" . $object))
                    rrmdir($dir . "/" . $object);
                else
                    unlink($dir . "/" . $object);
            }
        }
        rmdir($dir);
    }
}

// Handle create new widget
add_action('wp_ajax_get_folder_name', 'get_folder_name_callback');
function get_folder_name_callback() {
    if (is_user_logged_in()){
        $current_user = wp_get_current_user();
        $user_email = $current_user->user_email;
        $user_id = get_current_user_id();
    } else {
        wp_send_json_error('User not logged in to wordpress.');
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
    global $API_URL;
    $api_endpoint = $API_URL . '/assign_foldername';
    $response = wp_remote_post($api_endpoint, array(
        'method' => 'POST',
        'headers' => array(
          'Content-Type' => 'application/json'
        ),
        'body' => json_encode($body_data)
    ));

    if (is_wp_error($response)) {
        wp_send_json_error('Failed to fetch folder name from API.');
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
        wp_send_json_error('Invalid response from API.');
    }
}


// Handle change blog settings
// Add action for logged-in users
add_action('wp_ajax_bifm_save_settings', 'handle_bifm_save_settings');

// Function to handle form submission
function handle_bifm_save_settings() {
    try {
        // Check for nonce security
        if (!isset($_POST['bifm_nonce']) || !wp_verify_nonce($_POST['bifm_nonce'], 'my_custom_action')) {
            throw new Exception('Nonce verification failed!');
        }
        $user_id = get_current_user_id();
        // Update username and password only if username is provided
        if (isset($_POST['blog_author_username'])) {
            update_user_meta($user_id, 'username', $_POST['blog_author_username']);
            
            // Update password only if it's provided
            if (!empty($_POST['blog_author_password'])) {
                $random_key = bin2hex(random_bytes(32));
                $password = encrypt_data($_POST['blog_author_password'], $random_key);
                update_user_meta($user_id, 'encrypted_password', $password);
                update_user_meta($user_id, 'random_key', $random_key);
            }
        }
        
        // Always update these settings
        update_user_meta($user_id, 'website_description', $_POST['website_description']);
        update_user_meta($user_id, 'image_style', $_POST['image_style']);
        update_user_meta($user_id, 'blog_language', $_POST['blog_language']);
        update_user_meta($user_id, 'image_width', $_POST['image_width']);
        update_user_meta($user_id, 'image_height', $_POST['image_height']);
        
        wp_send_json_success('Settings saved successfully.');
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage(), 400);
    }
}



// Define a secret key. Store this securely and do not expose it.

function encrypt_data($data, $random_key) {
    $ivLength = openssl_cipher_iv_length($cipher = 'AES-128-CBC');
    $iv = openssl_random_pseudo_bytes($ivLength);
    $encrypted = openssl_encrypt($data, $cipher, $random_key, $options = 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

function decrypt_data($data, $random_key) {
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'AES-128-CBC', $random_key, $options = 0, $iv);
}


# expose Yoast values in the API that usually wouldn't be exposed
function register_yoast_fields() {
    register_rest_field('post', '_yoast_wpseo_focuskw',  array(
        'get_callback'    => 'custom_get_post_meta_for_api',
        'update_callback' => 'custom_update_post_meta_for_api',
        'schema'          => null,
    ));

    register_rest_field('post', '_yoast_wpseo_metadesc',  array(
        'get_callback'    => 'custom_get_post_meta_for_api',
        'update_callback' => 'custom_update_post_meta_for_api',
        'schema'          => null,
    ));
}
add_action('rest_api_init', 'register_yoast_fields');

function custom_get_post_meta_for_api($object, $field_name, $request) {
    return get_post_meta($object['id'], $field_name, true);
}

function custom_update_post_meta_for_api($value, $object, $field_name) {
    // Ensure the value is sanitized before saving
    $sanitized_value = sanitize_text_field($value);
    return update_post_meta($object->ID, $field_name, $sanitized_value);
}

// Elementor ads a meta tag by default to pages from the excerpt. This results in duplicate descriptions with Yoast. This function removes the elementor meta tag.
function remove_hello_elementor_description_meta_tag() {
	remove_action( 'wp_head', 'hello_elementor_add_description_meta_tag' );
}
add_action( 'after_setup_theme', 'remove_hello_elementor_description_meta_tag' );

// Update the plugin based on the most recent tag on github


// Filter the update_plugins transient just before it's updated
add_filter('pre_set_site_transient_update_plugins', 'my_plugin_pre_set_site_transient_update_plugins');

function my_plugin_pre_set_site_transient_update_plugins($transient) {
    //delete_transient('my_plugin_last_update_check');
    // Don't do anything if we are not checking for plugin updates
    if (empty($transient->checked)) return $transient;
    $last_checked = get_transient('my_plugin_last_update_check');
    if ($last_checked && (time() - $last_checked) < 12 * HOUR_IN_SECONDS) {
        // It's been less than 12 hours since the last check.
        return;
    }
    // Define your plugin data
    global $current_version;  // Your current plugin version
    $plugin_slug = plugin_basename(__DIR__) . '/widget-manager.php';  // Path to your main plugin file
    $github_repo = 'https://api.github.com/repos/pabloh3/bifm_wp_plugin/releases/latest';  // Your GitHub repo URL

    // Use WordPress HTTP API to send a request to GitHub API
    $response = wp_remote_get($github_repo, array(
        'headers' => array('Accept' => 'application/vnd.github.v3+json')
    ));

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
        // Handle errors
        return $transient;
    }

    $release_data = json_decode(wp_remote_retrieve_body($response), true);
    $latest_version = ltrim($release_data['tag_name'], 'v');  // Remove 'v' prefix if present

    // Check if the latest version from GitHub is newer than the current version
    if (version_compare($current_version, $latest_version, '<')) {
        error_log("new version found");
        // Update the transient to include new version information
        $transient->response[$plugin_slug] = (object) array(
            'slug'        => $plugin_slug,
            'new_version' => $latest_version,
            'url'         => $release_data['html_url'],
            'package'     => 'https://github.com/pabloh3/bifm_wp_plugin/archive/refs/tags/' . $latest_version . '.zip',
        );
    }
    set_transient('my_plugin_last_update_check', time(), 12 * HOUR_IN_SECONDS);
    // get updated transient
    return $transient;
}


require_once( __DIR__ . '/blog-manager.php' );
require_once( __DIR__ . '/smart-chat-manager.php' );
require_once( __DIR__ . '/widget-registration.php' );
require_once( __DIR__ . '/shared-widget-registration.php' );
require_once( __DIR__ . '/chat.php' );
require_once( __DIR__ . '/bifm_action_hooks.php' );
require_once( __DIR__ . '/shared-bifm_action_hooks.php' );

