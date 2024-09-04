<?php 
if ( ! defined( 'ABSPATH' ) ) exit;
require ( __DIR__ . '/../bifm-config.php' );// define base url for the API

// Create blog
add_action('wp_ajax_cbc_bifm_create_blog', 'bifm_handle_cbc_bifm_create_blog');
function bifm_handle_cbc_bifm_create_blog() {
    // Check nonce for security
    check_ajax_referer('create-single-post-action', 'nonce');

    // Get the keyphrase and category_id from the frontend
    $keyphrase = isset($_POST['keyphrase']) ? sanitize_text_field(wp_unslash($_POST['keyphrase'])) : '';
    $category = isset($_POST['category']) ? sanitize_text_field(wp_unslash($_POST['category'])) : '';
    $category_name = isset($_POST['category_name']) ? sanitize_text_field(wp_unslash($_POST['category_name'])) : '';
    $response = bifm_create_blog($keyphrase, $category, $category_name);
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log("error when calling create-blog api");
        wp_send_json_error(array('message' => "Something went wrong: $error_message"));
    } else {
        // Register request
        $status_code = wp_remote_retrieve_response_code($response);
        wp_send_json(array(
            'data' => wp_remote_retrieve_body($response),
            'status' => $status_code
        ), $status_code);
    }

    wp_die("Reached end without success message");
}

function bifm_create_blog($keyphrase, $category, $category_name) {
    $website = home_url();  // Current website URL
    $user_id = get_current_user_id();
    $current_user = wp_get_current_user();
    $user_email = $current_user->user_email;
    $related_links = bifm_fetch_related_links($category);

    # Extract website info
    $username = get_user_meta($user_id, 'username', true);
    $encrypted_password = get_user_meta($user_id, 'encrypted_password', true);
    // return an error if the user has not set their username and password
    if (!$username || !$encrypted_password) {
        wp_send_json_error(array('message' => "Please set your blog author username and password in the settings page."));
    }

    $website_description = get_user_meta($user_id, 'website_description', true);
    if (!$website_description) {
        $website_description = "";
    }
    $image_style = get_user_meta($user_id, 'image_style', true);
    if (!$image_style) {
        $image_style = "";
    }
    $blog_language = get_user_meta($user_id, 'blog_language', true);
    if (!$blog_language) {
        $blog_language = "english";
    }
    $image_width = get_user_meta($user_id, 'image_width', true);
    if (!$image_width) {
        $image_width = "";
    }
    $image_height = get_user_meta($user_id, 'image_height', true);
    if (!$image_height) {
        $image_height = "";
    }
    global $BIFM_API_URL;
    $url = $BIFM_API_URL . "create-blog";
    
    // Generate UUID
    $uuid = wp_generate_uuid4();
    $response = wp_remote_post($url, array(
        'headers' => array('Content-Type' => 'application/json'),
        'body' => wp_json_encode(array(
            'keyphrase' => $keyphrase,
            'category' => $category,
            'category_name' => $category_name,
            'website' => $website,
            'requester' => $user_email,
            'related_links' => $related_links,
            'username' => $username,
            'password' => $encrypted_password,
            'website_description' => $website_description,
            'image_style' => $image_style,
            'blog_language' => $blog_language,
            'image_width' => $image_width,
            'image_height' => $image_height,
            'uuid' => $uuid
        )),
        'method' => 'POST',
        'data_format' => 'body'
    ));
    if (is_wp_error($response)) {
        return $response;
    } else {
        bifm_register_request($uuid, $keyphrase, $category_name, $user_email);
        return $response;
    }

    return $response;
}

add_action('wp_ajax_cbc_poll_for_results', 'bifm_handle_cbc_poll_for_results');
function bifm_handle_cbc_poll_for_results() {
    // Check nonce for security
    check_ajax_referer('create-single-post-action', 'nonce');
    
    // Get the jobId from the frontend
    $jobId = isset($_POST['jobId']) ? sanitize_text_field(wp_unslash($_POST['jobId'])) : '';
    global $BIFM_API_URL;
    // Construct the URL for the external service
    $url = $BIFM_API_URL . "poll-blog-results/{$jobId}";

    // Send a GET request to the external service
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        wp_send_json_error(array('message' => "Something went wrong: $error_message"));
    } else {
        $status_code = wp_remote_retrieve_response_code($response);
        wp_send_json(array(
            'data' => wp_remote_retrieve_body($response),
            'status' => $status_code
        ), $status_code);
    }

    wp_die("Reached end without success message");
}

// Get the categories
add_action('wp_ajax_cbc_get_categories', 'bifm_handle_cbc_get_categories');
function bifm_handle_cbc_get_categories() {
    // Get all the categories
    $categories = get_categories(array(
        'hide_empty' => false, // Include categories that are empty
        'orderby' => 'name',
        'order'   => 'ASC'
    ));
    //print length of categories
    $result = array();
    foreach ($categories as $category) {
        $result[] = array('id' => $category->term_id, 'name' => $category->name);
    }
    wp_send_json_success($result);
}

function bifm_get_category_id_by_name($category_name) {
    $term = get_term_by('name', $category_name, 'category');
    return ($term && !is_wp_error($term)) ? $term->term_id : false;
}

// If necessary to create a new category for the post
add_action('wp_ajax_cbc_create_category', 'bifm_handle_cbc_create_category');
function bifm_handle_cbc_create_category() {
    // Check nonce for security
    check_ajax_referer('create-single-post-action', 'nonce');
    $category_name = isset($_POST['category_name']) ? sanitize_text_field(wp_unslash($_POST['category_name'])) : '';
    if (!$category_name) {
        wp_send_json_error(array('message' => "Invalid category name."));
        return;
    }

    $term = wp_insert_term($category_name, 'category');

    if (is_wp_error($term)) {
        wp_send_json_error(array('message' => $term->get_error_message()));
    } else {
        wp_send_json_success(array('id' => $term['term_id']));
    }
}

// Fetch links for similar Pages / posts to feed the bot
function bifm_fetch_related_links($category) {
    $related_links = [];

    // Fetching three most recent posts of the same category with a 'published' status
    $posts_args = array(
        'posts_per_page' => 2,
        'cat'  => $category,
        'post_type'      => 'post',
        'post_status'    => 'publish',     // Only get published posts
        'orderby'        => 'date',        // Order by post date
        'order'          => 'DESC'         // Order in descending order to get the most recent
    );
    $posts_query = new WP_Query($posts_args);
    while($posts_query->have_posts()) {
        $posts_query->the_post();
        $related_links[] = array(
            'title' => get_the_title(),
            'url'   => get_permalink(),
        );
    }
    wp_reset_postdata(); // Resetting post data after custom query

    // Fetching two pages with a 'published' status (Modify this as needed)
    $pages_args = array(
        'posts_per_page' => 2,
        'post_type'      => 'page',
        'post_status'    => 'publish'      // Only get published pages
    );
    $pages_query = new WP_Query($pages_args);
    while($pages_query->have_posts()) {
        $pages_query->the_post();
        $related_links[] = array(
            'title' => get_the_title(),
            'url'   => get_permalink(),
        );
    }
    wp_reset_postdata();

    return $related_links;
}

// Bulk creation of blog posts
add_action('wp_ajax_cbc_create_bulk_blogs', 'bifm_handle_cbc_create_bulk_blogs');
function bifm_handle_cbc_create_bulk_blogs() {
    // Check nonce for security
    check_ajax_referer('bulk-upload-items-action', 'nonce');
    $items = [];
    // Get the items from the frontend
    if (isset($_POST['items']) && count($_POST['items'])) {
        $items = map_deep($_POST['items'], function($item) { //sanitized in the next lines
            return is_array($item) ? $item : sanitize_text_field(trim($item));
        });
    }

    if (empty($items)) {
        wp_send_json_error(array('message' => "No items provided."));
    }

    $response = bifm_cbc_process_items($items);
    $status_code = wp_remote_retrieve_response_code($response);
    wp_send_json(array(
        'data' => wp_remote_retrieve_body($response),
        'status' => $status_code,
    ), $status_code);
}

function bifm_cbc_process_items($items) {
    global $BIFM_API_URL;
    $url = $BIFM_API_URL . "create-blog-batch";
    
    // Fetch additional data
    $website = home_url();  // Current website URL
    $current_user = wp_get_current_user();
    $user_email = $current_user->user_email;
    $user_id = get_current_user_id();
    $related_links = array(); // Assuming related links are not required for batch creation

    // Extract user info
    $username = get_user_meta($user_id, 'username', true);
    $encrypted_password = get_user_meta($user_id, 'encrypted_password', true);
    // Return an error if the user has not set their username and password
    if (!$username || !$encrypted_password) {
        wp_send_json_error(array('message' => "Please set your blog author username and password in the [settings page](/wp-admin/admin.php?page=bifm#settings)."));
    }
    $website_description = get_user_meta($user_id, 'website_description', true);
    if (!$website_description) {
        $website_description = "";
    }
    $image_style = get_user_meta($user_id, 'image_style', true);
    if (!$image_style) {
        $image_style = "";
    }
    $blog_language = get_user_meta($user_id, 'blog_language', true);
    if (!$blog_language) {
        $blog_language = "english";
    }
    $image_width = get_user_meta($user_id, 'image_width', true);
    if (!$image_width) {
        $image_width = "";
    }
    $image_height = get_user_meta($user_id, 'image_height', true);
    if (!$image_height) {
        $image_height = "";
    }

    //modify items to include a uuid in each item
    $new_items = array();
    foreach ($items as $item) {
        $uuid = wp_generate_uuid4();
        $item['uuid'] = $uuid;
        $new_items[] = $item; // Use [] to append item to the array
    }
    $items = $new_items;
    error_log("items modified: " . wp_json_encode($items));
    
    // Prepare the headers and body of the request
    $body = array(
        'items' => $items,
        'website' => $website,
        'requester' => $user_email,
        'related_links' => $related_links,
        'username' => $username,
        'password' => $encrypted_password,
        'website_description' => $website_description,
        'image_style' => $image_style,
        'blog_language' => $blog_language,
        'image_width' => $image_width,
        'image_height' => $image_height,
    );
    
    $json_body = wp_json_encode($body);

    // Use wp_remote_post to perform the request
    $headers = array(
        'Content-Type' => 'application/json'
    );
    $response = wp_remote_post($url, array(
        'headers' => $headers,
        'body' => $json_body,
        'method' => 'POST',
        'timeout' => 45,
    ));

    // Check for errors in the response
    if (is_wp_error($response)) {
        error_log("error found in response");
        $error_message = $response->get_error_message();
        wp_die(esc_html(__("Something went wrong:",'bifm').$error_message));
    } else {
        // Handle the successful response
        error_log("response: " . wp_json_encode($response));
        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
    
        // register requests based on items not keyphrases
        for ($i = 0; $i < count($items); $i++) {
            bifm_register_request($items[$i]['uuid'], $items[$i]['keyphrase'], $items[$i]['category_name'], $user_email);
        }

        // Do something with the response
        return $response;
    }
}



function bifm_register_request($uuid, $keyphrase, $category, $requester) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cbc_blog_requests';

    // Check if table exists, if not call bifm_create_requests_table
    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$table_name)) != $table_name) { //phpcs:ignore
        bifm_create_requests_table();
    }

    $wpdb->insert($table_name, array( //phpcs:ignore
        'uuid' => $uuid,
        'requested_at' => current_time('mysql'),
        'keyphrase' => $keyphrase,
        'category' => $category,
        'requester' => $requester,
    ));
}


function bifm_cbc_delete_blog_post() {
    // Check the nonce for security
    check_ajax_referer('create-single-post-action', 'nonce');

    // Get the post ID and UUID from the request
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $uuid = isset($_POST['uuid']) ? sanitize_text_field($_POST['uuid']) : '';
    // Validate inputs
    if (!$uuid) {
        wp_send_json_error('Invalid data provided.');
    }

    // Attempt to delete the post
    $deleted_post = wp_delete_post($post_id, true);

    // Delete the corresponding request from the custom table
    global $wpdb;
    $table_name = $wpdb->prefix . 'cbc_blog_requests';

    $deleted_request = $wpdb->delete($table_name, array('uuid' => $uuid)); //phpcs:ignore

    if ($deleted_request === false) {
        wp_send_json_error('Failed to delete the request from the custom table.');
    }

    // If everything is successful
    wp_send_json_success('Post and request deleted successfully.');
}
add_action('wp_ajax_cbc_delete_blog', 'bifm_cbc_delete_blog_post');
add_action('wp_ajax_nopriv_cbc_delete_blog', 'bifm_cbc_delete_blog_post');

?>
