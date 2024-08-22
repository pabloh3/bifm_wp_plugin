<?php if ( ! defined( 'ABSPATH' ) ) exit;
/*
 * Plugin Name: Build It For Me - AI creator
 * Description: Ask a bot to create for you.
 * Version: 1.2.8
 * Author: Build It For Me
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

// include the WordPress HTTP API
include_once(ABSPATH . WPINC . '/http.php');
require 'bifm-config.php';
define('BIFM_VERSION', '1.2.8');
define('BIFM_URL',plugin_dir_url(__FILE__));
define('BIFM_PATH',plugin_dir_path(__FILE__));

function bifm_create_requests_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cbc_blog_requests';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        uuid varchar(36) NOT NULL,
        requested_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        keyphrase text NOT NULL,
        category varchar(255) NOT NULL,
        requester varchar(100) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__,'bifm_create_requests_table');

function bifm_create_file($path,$content){
    global $wp_filesystem; 
    if (empty($wp_filesystem)) {
        require_once (ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }

    if(!$wp_filesystem->put_contents( $path, $content) ) {
        throw new Exception(esc_html(__('Failed to create file','bifm')));
    }
}

function bifm_delete_folder($path){
    global $wp_filesystem; 
    if (empty($wp_filesystem)) {
        require_once (ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }

    if(!$wp_filesystem->rmdir( $path,true) ) {
        throw new Exception(esc_html(__('Failed to remove directory','bifm')));
    }
}
 

// DEFINE ALL THE PAGES //
function bifm_admin_menu() {
    add_menu_page(
        __('Build It For Me AI','bifm'),
        __('Build It For Me','bifm'),
        'edit_posts',
        'bifm', //this is the slug used for updates
        'bifm_admin_page_content',
        'dashicons-welcome-widgets-menus'
    );
    
    add_submenu_page(
        null, //'elementor-widget-manager',
        __('Create Widget Page','bifm'),
        __('Create Widget','bifm'),
        'edit_posts',
        'create-widget',
        'bifm_create_widget_content'
    );

    
    add_submenu_page(
        null,
        __('Widget Manager','bifm'),
        __('Widget Manager','bifm'),
        'edit_posts',
        'widget-manager',
        'bifm_widget_manager'
    );
        
    add_submenu_page(
        'elementor-chat-manager',
        __('Smart Chat Settings','bifm'),
        __('Smart Chat','bifm'),
        'edit_posts',
        'create-chat',
        'bifm_create_chat_content'
    );

    add_submenu_page(
        'design-system',            // parent_slug
        __('Design system page','bifm'),       // page_title
        __('Design system','bifm'),            // menu_title
        'edit_posts',               // capability (give access to those with this access)
        'design_system',            // menu_slug
        'bifm_create_design_system'  // callback function
    );
    

    add_submenu_page(
        'writer-settings',
        __('Writer settings page','bifm'),
        __('Writer settings','bifm'),
        'edit_posts',
        'writer-settings',
        'bifm_writer_settings'
    );

    add_submenu_page(
        'elementor-blog-manager',
        __('Create Blog Page','bifm'),
        __('Create Blog','bifm'),
        'edit_posts',
        'create-blog',
        'bifm_create_blog_content'
    );
        
}
add_action('admin_menu', 'bifm_admin_menu');

// DEFINE ALL THE PAGES CONTENT  AND ENQUEUE SCRIPTS //

function bifm_admin_page_content() {
    //this code was taken to admin-page.php
    include plugin_dir_path(__FILE__) . 'billy/billy-page.php';
    // Externalize JavaScript Code
    //wp_enqueue_script('my-custom-script', plugins_url('static/admin-page.js', __FILE__), array('jquery'), filemtime(plugin_dir_path(__FILE__) . 'static/admin-page.js'), true);
    wp_enqueue_script('highlightjs',BIFM_URL.'static/highlightjs/highlight.min.js',['jquery'],BIFM_VERSION,true);
    wp_enqueue_script('markdownit',BIFM_URL.'static/markdownit/markdown-it.min.js',['jquery'],BIFM_VERSION,true);
    wp_enqueue_script('materialize',BIFM_URL.'static/materialize/materialize.min.js',['jquery'],BIFM_VERSION,true);
    wp_enqueue_script('billy-script', BIFM_URL . 'billy/billy.js', array('jquery','highlightjs'), filemtime(plugin_dir_path(__FILE__) . 'billy/billy.js'), true);
    wp_localize_script('billy-script', 'billy_localize', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('billy-nonce')
    ));
    wp_enqueue_style('billy-page', BIFM_URL . 'billy/billy-page.css', [], filemtime(plugin_dir_path(__FILE__) . 'billy/billy-page.css'), 'all');
}


function bifm_create_widget_content() {
    //this code was taken to admin-page.php
    include plugin_dir_path(__FILE__) . 'billy-coder/coder-page.php';
    wp_enqueue_style('my-plugin-styles', esc_url(plugins_url('static/styles.css', __FILE__)), [], filemtime(plugin_dir_path(__FILE__) . 'static/styles.css'), 'all');
    wp_enqueue_style('billy-page', BIFM_URL . 'billy-coder/coder-page.css',[],BIFM_VERSION);
    wp_enqueue_script('my-plugin-script', BIFM_URL . 'billy-coder/billy-coder.js', array('jquery'), filemtime(plugin_dir_path(__FILE__) . 'billy-coder/billy-coder.js'), true);        
    // Pass ajax_url to script.js
    wp_localize_script('my-plugin-script', 'my_plugin', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('my-plugin-nonce')
    ));
}

function bifm_widget_manager() {
    //this code was taken to admin-page.php
    include plugin_dir_path(__FILE__) . 'billy-coder/widget-manager-page.php';
    wp_enqueue_script('cbc_script_widget_mgr', plugins_url('/billy-coder/widget-manager.js', __FILE__), array('jquery'), filemtime(plugin_dir_path(__FILE__) . '/billy-coder/widget-manager.js'), true);
    wp_localize_script('cbc_script_widget_mgr', 'my_script_object', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('my_custom_action')
    ));
}

function bifm_create_blog_content() {
    //this code was taken to admin-page.php
    include plugin_dir_path(__FILE__) . 'blog-creator/blog-creator-page.php';
    // Enqueue scripts for the blog page
    wp_enqueue_style('cbc_script_blog_creator-page', BIFM_URL . 'blog-creator/blog-creator.css', [], filemtime(plugin_dir_path(__FILE__) . 'blog-creator/blog-creator.css'), 'all');
    wp_enqueue_script('cbc_script', plugins_url('/blog-creator/blog-creator-script.js', __FILE__), array('jquery'), BIFM_VERSION, true);
    

    // Localize the script with your data
    $translation_array = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'single_post_nonce' => wp_create_nonce('create-single-post-action'),
        'bulk_upload_nonce' => wp_create_nonce('bulk-upload-items-action')
    );
    // error log the entire translation array
    wp_localize_script('cbc_script', 'cbc_object', $translation_array);
}

function bifm_writer_settings() {
    //this code was taken to admin-page.php
    include plugin_dir_path(__FILE__) . '/writer-settings/writer-settings-page.php';
    wp_enqueue_script('cbc_script_widget_mgr', plugins_url('/writer-settings/writer-settings.js', __FILE__), array('jquery'), filemtime(plugin_dir_path(__FILE__) . '/writer-settings/writer-settings.js'), true);
    // Localize the script with your data
    $translation_array = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'bifm_nonce' => wp_create_nonce('bifm-writer-settings-nonce'),
    );
    // error log the entire translation array
    wp_localize_script('cbc_script_widget_mgr', 'my_script_object', $translation_array);
}

function bifm_create_chat_content() {
    //this code was taken to admin-page.php
    include plugin_dir_path(__FILE__) . 'billy/smart-chat-settings-page.php';
    // Enqueue scripts for the chat page
    wp_enqueue_script('cbc_script_chat', plugins_url('/billy/smart-chat-settings.js', __FILE__), array('jquery'), filemtime(plugin_dir_path(__FILE__) . '/billy/smart-chat-settings.js'), true);
    // Localize the script with your data
    $translation_array = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('update-chat-settings-nonce'),
    );
    // error log the entire translation array
    wp_localize_script('cbc_script_chat', 'cbc_script_object_chat', $translation_array);
}

function bifm_create_design_system() {
    //this code was taken to admin-page.php
    include plugin_dir_path(__FILE__) . 'design-system-page.php';
}


// ENQUEUE SCRIPTS AND STYLES TO BE USED IN ALL PAGES //
function bifm_ewm_enqueue_admin_scripts($hook) {
    global $pagenow;
    // Check if we're on the create-widget page to load that JS
    if ($pagenow == 'admin.php' && isset($_GET['page']) && ($_GET['page'] == 'bifm' || $_GET['page'] == 'create-widget' || $_GET['page'] == 'create-blog' || $_GET['page'] == 'bifm' || $_GET['page'] == 'create-chat' || $_GET['page'] == 'widget-manager' || $_GET['page'] == 'design_system' || $_GET['page'] == 'writer-settings')) { // phpcs:ignore
        // in all admin pages load the styles
        
    wp_enqueue_style('materialicons',BIFM_URL.'static/materialicons/icon.css',[],BIFM_VERSION);
        wp_enqueue_style('my-plugin-styles', esc_url(plugins_url('static/styles.css', __FILE__)), [], filemtime(plugin_dir_path(__FILE__) . 'static/styles.css'), 'all'); 
    }
}
add_action('admin_enqueue_scripts', 'bifm_ewm_enqueue_admin_scripts',90);

require_once( __DIR__ . '/blog-creator/blog-manager.php' );
require_once( __DIR__ . '/billy/chat-settings-callbacks.php' );
require_once( __DIR__ . '/shared-widget-registration.php' );
require_once( __DIR__ . '/billy-coder/billy-coder.php' );
require_once( __DIR__ . '/billy-coder/manage-widgets.php' );
require_once( __DIR__ . '/billy/smart_chat_callbacks.php' );
require_once( __DIR__ . '/writer-settings/writer-settings.php' );
require_once( __DIR__ . '/chat-bar/chat-bar.php' );



// EXPOSE API VALUES //
// expose Yoast values in the API that usually wouldn't be exposed
function bifm_register_yoast_fields() {
    register_rest_field('post', '_yoast_wpseo_focuskw',  array(
        'get_callback'    => 'bifm_custom_get_post_meta_for_api',
        'update_callback' => 'bifm_custom_update_post_meta_for_api',
        'schema'          => null,
    ));

    register_rest_field('post', '_yoast_wpseo_metadesc',  array(
        'get_callback'    => 'bifm_custom_get_post_meta_for_api',
        'update_callback' => 'bifm_custom_update_post_meta_for_api',
        'schema'          => null,
    ));
}
add_action('rest_api_init', 'bifm_register_yoast_fields');

// elementor data
add_action('rest_api_init', function () {
    register_rest_field('page', 'elementor_data', [
        'get_callback'    => 'bifm_get_elementor_data',
        'update_callback' => 'bifm_update_elementor_data',
        'schema'          => null,
    ]);
});

add_action('rest_api_init', function () {
    register_rest_field(['page', 'post'], 'elementor_data', [
        'get_callback'    => 'bifm_get_elementor_data',
        'update_callback' => 'bifm_update_elementor_data',
        'schema'          => null,
    ]);
});


function bifm_get_elementor_data($object, $field_name, $request) {
    return get_post_meta($object['id'], '_elementor_data', true);
}

function bifm_update_elementor_data($value, $object, $field_name) {
    if (!is_array($value) && !is_object($value)) {
        return new WP_Error('invalid_data', 'Elementor data must be an array or object.', ['status' => 400]);
    }
    return update_post_meta($object->ID, '_elementor_data', $value);
}

// Register field used to track post requests
function bifm_register_uuid_meta() {
    register_post_meta('post', 'bifm_uuid', array(
        'type' => 'string',
        'description' => 'BIFM UUID',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        }
    ));
}
add_action('init', 'bifm_register_uuid_meta');


function bifm_custom_get_post_meta_for_api($object, $field_name, $request) {
    return get_post_meta($object['id'], $field_name, true);
}

function bifm_custom_update_post_meta_for_api($value, $object, $field_name) {
    // Ensure the value is sanitized before saving
    $sanitized_value = sanitize_text_field($value);
    return update_post_meta($object->ID, $field_name, $sanitized_value);
}

// Elementor ads a meta tag by default to pages from the excerpt. This results in duplicate descriptions with Yoast. This function removes the elementor meta tag.
function bifm_remove_hello_elementor_description_meta_tag() {
	remove_action( 'wp_head', 'hello_elementor_add_description_meta_tag' );
}
add_action( 'after_setup_theme', 'bifm_remove_hello_elementor_description_meta_tag' );



// Hook for plugin deactivation to delete the table where post info is stored
register_deactivation_hook(__FILE__, 'bifm_cbc_drop_requests_table');

function bifm_cbc_drop_requests_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cbc_blog_requests'; 

    $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS %s;",$table_name)); // phpcs:ignore
}


// LOAD ACTION HOOKS FROM WIDGETS //
// register the widgets in DB
function bifm_register_custom_widgets_from_db() {
    $widget_names = get_option('bifm_widget_names', []);
    foreach ($widget_names as $widget_name) {
        $widget_path = wp_upload_dir()['basedir'] . "/bifm-files/bifm-widgets/{$widget_name}/{$widget_name}.php";
        if (file_exists($widget_path)) {
            require_once($widget_path);
            $class_name = "\\{$widget_name}";
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new $class_name());
        } else {
            // Handle the case where the widget file does not exist
            error_log(__("Tried to register widget file does not exist: ",'bifm') . $widget_path);
            // This could be logging an error or notifying an administrator
        }
    }
}
add_action('elementor/widgets/widgets_registered', 'bifm_register_custom_widgets_from_db');


// check if bifm_action_hooks exists, if not, create with content <?php
$dirPath = wp_upload_dir()['basedir'] . '/bifm-files';
// Check if the directory exists, and if not, create it
if (!is_dir($dirPath)) {
    // Attempt to create the directory recursively
    if (!wp_mkdir_p($dirPath)) {
        // Handle the error if the directory cannot be created
        error_log(__("Failed to create directory:",'bifm'). $dirPath);
        // Optionally, you can communicate the error back to the user or admin
    }
}
if (!file_exists(wp_upload_dir()['basedir'] . '/bifm-files/bifm_action_hooks.php')) {
    bifm_create_file(wp_upload_dir()['basedir'] . '/bifm-files/bifm_action_hooks.php', "<?php\n");
}
require_once(wp_upload_dir()['basedir'] . '/bifm-files/bifm_action_hooks.php' );
//create shared-bifm_action_hooks.php
if (!file_exists( __DIR__ . '/shared-bifm_action_hooks.php')) {
    bifm_create_file(  __DIR__ . '/shared-bifm_action_hooks.php', "<?php\n");
}

require_once(  __DIR__ . '/shared-bifm_action_hooks.php' );
// Retrieve stored hooks from the option
$hooks = get_option('bifm_action_hooks', []);
// Loop through each registered hook
foreach ($hooks as $hook_name => $widget_name) {
    // Construct the path to the backend functions file for the widget
    $backend_functions_path = wp_upload_dir()['basedir'] . '/bifm-files/bifm-widgets/' . $widget_name . '/backend_functions.php';

    // Check if the file exists before including it
    if (file_exists($backend_functions_path)) {
        include_once $backend_functions_path;

        // Register both no-priv and priv actions for the hook
        add_action('wp_ajax_nopriv_' . $hook_name, $hook_name);
        add_action('wp_ajax_' . $hook_name, $hook_name);
    }
}



// CODE TO HANDLE PLUGIN UPDATES //
// Filter the update_plugins transient just before it's updated
add_filter('pre_set_site_transient_update_plugins', 'bifm_pre_set_site_transient_update_plugins');
function bifm_pre_set_site_transient_update_plugins($transient) {
    //error_log("pre_set_site_transient_update_plugins called");
    //following line commented out, is used for debugging since deleting transient checks for new updates immediately
    //delete_transient('bifm_last_update_check');
    // Don't do anything if we are not checking for plugin updates
    if (empty($transient->checked)) {
        //error_log("empty transient checked, this is not a plugin update check");
        return $transient;
    }
    $last_checked = get_transient('bifm_last_update_check');
    $check_each_hours = 1;
    // if the transient has expired $last_checked will be false so this won't run and skip to update
    if ($last_checked && (time() - $last_checked) < $check_each_hours * HOUR_IN_SECONDS) {
        $time_elapsed_minutes = (time() - $last_checked) / 60;
        error_log("less than 1 hour since last check, time elapsed: " . $time_elapsed_minutes . " minutes");
        // Restore the update data if it's less than an hour since the last check
        $cached_response = get_transient('bifm_cached_response');
        if ($cached_response) {
            $transient->response = array_merge((array) $transient->response, (array) $cached_response);
        }
        return $transient;
    }
    // Define your plugin data
    $plugin_slug = plugin_basename(__DIR__) . '/widget-manager.php';  // Path to your main plugin file
    $github_repo = 'https://api.github.com/repos/pabloh3/bifm_wp_plugin/releases/latest';  // Your GitHub repo URL

    // Use WordPress HTTP API to send a request to GitHub API
    $response = wp_remote_get($github_repo, array(
        'headers' => array('Accept' => 'application/vnd.github.v3+json')
    ));

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
        // Handle errors
        error_log("error in response from github when checking for plugin updates");
        return $transient;
    }

    $release_data = json_decode(wp_remote_retrieve_body($response), true);
    $latest_version = ltrim($release_data['tag_name'], 'v');  // Remove 'v' prefix if present
    $assets = $release_data['assets'];
    if (!empty($assets)) {
        // Find the browser_download_url for your specific asset, e.g., bifm-plugin.zip
        foreach ($assets as $asset) {
            if ($asset['name'] == "bifm.zip") {
                $download_url = $asset['browser_download_url'];
                break;
            }
        }
        
        // Check if we found a matching asset and have a download URL
        if (!empty($download_url) && version_compare(BIFM_VERSION, $latest_version, '<')) {
            error_log("new version found");
            // Update the transient to include new version information with the correct package URL
            //error_log("new_transient: " .  print_r($transient, true));
            $update_data = (object) array(
                'slug'        => $plugin_slug,
                'plugin'      => $plugin_slug,
                'new_version' => $latest_version,
                'url'         => $release_data['html_url'],
                'package'     => $download_url,
            );
            $transient->response[$plugin_slug] = $update_data;
            set_transient('bifm_cached_response', [$plugin_slug => $update_data], $check_each_hours * HOUR_IN_SECONDS);
        }
    } else {
        error_log("no assets found in github release");
    }
    set_transient('bifm_last_update_check', time(), $check_each_hours * HOUR_IN_SECONDS);
    //error_log("new transient returned");
    // get updated transient
    return $transient;
}
// Clear the update cache when the plugin is updated
function bifm_clear_update_cache($upgrader_object, $options) {
    if ($options['action'] == 'update' && $options['type'] == 'plugin') {
        // Check if the plugin updated is your plugin
        if (isset($options['plugins']) && in_array(plugin_basename(__DIR__) . '/widget-manager.php', $options['plugins'])) {
            delete_transient('bifm_cached_response');
        }
    }
}
add_action('upgrader_process_complete', 'bifm_clear_update_cache', 10, 2);

// Suppress WordPress core update notifications
/*function suppress_update_notifications() {
    remove_action('admin_notices', 'update_nag', 3);
}
add_action('admin_menu', 'suppress_update_notifications');*/
