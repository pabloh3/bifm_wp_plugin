<?php

// define base url for the API
define('BIFM_API_BASE_URL', 'https://wp.builditforme.ai');
//define('BIFM_API_BASE_URL', 'http://127.0.0.1:5001');

// Enqueue required scripts and styles
function cbc_enqueue_scripts() {
    wp_enqueue_script('cbc_script', plugins_url('/static/blog-creator-script.js', __FILE__), array('jquery'), '1.0.66', true);

    // Localize the script with your data
    $translation_array = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'single_post_nonce' => wp_create_nonce('create-single-post-action'),
        'bulk_upload_nonce' => wp_create_nonce('bulk-upload-csv-action')
    );
    // error log the entire translation array
    wp_localize_script('cbc_script', 'cbc_object', $translation_array);
}
add_action('admin_enqueue_scripts', 'cbc_enqueue_scripts');

function decrypt($data, $random_key) {
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'AES-128-CBC', $random_key, $options = 0, $iv);
}

// create blog
add_action('wp_ajax_cbc_create_blog', 'handle_cbc_create_blog');
function handle_cbc_create_blog() {
    // Check nonce for security
    check_ajax_referer('create-single-post-action', 'nonce');

    // Get the keyphrase and category_id from the frontend
    $keyphrase = isset($_POST['keyphrase']) ? sanitize_text_field(wp_unslash($_POST['keyphrase'])) : '';
    $category = isset($_POST['category']) ? sanitize_text_field(wp_unslash($_POST['category'])) : '';
    $website = home_url();  // Current website URL
    $user_id = get_current_user_id();
    $current_user = wp_get_current_user();
    $user_email = $current_user->user_email;
    $related_links = fetch_related_links($category);
    # Extract website info
    $username = get_user_meta($user_id, 'username', true);
    $encrypted_password = get_user_meta($user_id, 'encrypted_password', true);
    // return an error if the user has not set their username and password
    if (!$username || !$encrypted_password) {
        wp_send_json_error(array('message' => "Please set your blog author username and password in the settings page."));
    }
    $random_key = get_user_meta($user_id, 'random_key', true);
    $password = decrypt($encrypted_password, $random_key);
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

    $url = BIFM_API_BASE_URL . "/create-blog";
    
    /*// for  debugging DELETE!!!!!!!!!!!!!!!!!!!
    wp_send_json(array(
        'data' => '{"message": "test error message", "jobId": 123}',
        'status' => 500
    ), 500);*/
    
    $response = wp_remote_post($url, array(
        'headers' => array('Content-Type' => 'application/json'),
        'body' => json_encode(array(
            'keyphrase' => $keyphrase,
            'category' => $category,
            'website' => $website,
            'requester' => $user_email,
            'related_links' => $related_links,
            'username' => $username,
            'password' => $password,
            'website_description' => $website_description,
            'image_style' => $image_style,
            'blog_language' => $blog_language
        )),
        'method' => 'POST',
        'data_format' => 'body'
    ));



    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log("error when calling create-blog api");
        wp_send_json_error(array('message' => "Something went wrong: $error_message"));
    } else {
        $status_code = wp_remote_retrieve_response_code($response);
        error_log("Response: ");
        wp_send_json(array(
            'data' => wp_remote_retrieve_body($response),
            'status' => $status_code
        ), $status_code);
    }

    wp_die("Reached end without success message");
}


add_action('wp_ajax_cbc_poll_for_results', 'handle_cbc_poll_for_results');
function handle_cbc_poll_for_results() {
    error_log("called to poll for results");
    // Check nonce for security
    check_ajax_referer('create-single-post-action', 'nonce');
    
    // Get the jobId from the frontend
    $jobId = isset($_POST['jobId']) ? sanitize_text_field(wp_unslash($_POST['jobId'])) : '';
    
    // Construct the URL for the external service
    $url = BIFM_API_BASE_URL . "/poll-blog-results/{$jobId}";

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

// get the categories
add_action('wp_ajax_cbc_get_categories', 'handle_cbc_get_categories');
function handle_cbc_get_categories() {
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


function get_category_id_by_name($category_name) {
    $term = get_term_by('name', $category_name, 'category');
    return ($term && !is_wp_error($term)) ? $term->term_id : false;
}


// if necessary to create a new category for the post
add_action('wp_ajax_cbc_create_category', 'handle_cbc_create_category');
function handle_cbc_create_category() {
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


// fetch links for similar Pages / posts to feed the bot
function fetch_related_links($category) {
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


// bulk creation of blog posts
add_action('wp_ajax_cbc_file_upload', 'handle_cbc_file_upload');
function handle_cbc_file_upload() {
    // Check user capabilities and nonce
    check_ajax_referer('bulk-upload-csv-action', 'nonce');

    
    // get the category
    $category_id = isset($_POST['category']) ? sanitize_text_field(wp_unslash($_POST['category'])) : '';
    
    #This is checking if the file upload field (named 'cbc_csv_file') exists within the $_FILES superglobal array
    if (isset($_FILES['cbc_csv_file'])) {
        // Handle file upload securely
        $overrides = array('test_form' => false);
        $file = wp_handle_upload($_FILES['cbc_csv_file'], $overrides);

        if (isset($file['error'])) {
            error_log("Error uploading file.");
            wp_die('Error uploading file: ' . $file['error'], 'Error', array('response' => 400));
        }

        
        // Process the CSV file
        $file_path = $file['file'];
        $response = cbc_process_csv($file_path, $category_id);
        $status_code = $response['status'];
        wp_send_json(array(
            'data' => $response['data'],
            'status' => $status_code,
        ), $status_code);
    }
}


function cbc_process_csv($file_path, $category_id) {

    // Assume the API expects a multipart/form-data request with a file field
    $url = BIFM_API_BASE_URL . "/create-blog-batch";
    
    // Fetch additional data
    $website = home_url();  // Current website URL
    $current_user = wp_get_current_user();
    $user_email = $current_user->user_email;
    $user_id = get_current_user_id();
    $related_links = fetch_related_links($category_id); // Assuming this function exists and $category_id is used here
    # Extract website info
    $username = get_user_meta($user_id, 'username', true);
    $encrypted_password = get_user_meta($user_id, 'encrypted_password', true);
    // return an error if the user has not set their username and password
    if (!$username || !$encrypted_password) {
        wp_send_json_error(array('message' => "Please set your blog author username and password in the settings page."));
    }
    $random_key = get_user_meta($user_id, 'random_key', true);
    $password = decrypt($encrypted_password, $random_key);
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
    # Extract website info    
    $keyphrases = array();
    if (($handle = fopen($file_path, 'r')) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $keyphrases[] = $data[0]; // Add the first column value to keyphrases array
        }
        fclose($handle);
    } else {
        error_log("Unable to open CSV file.");
        wp_die("Unable to open CSV file.", 'Error', array('response' => 400));
    }
    
    // Prepare the headers and body of the request
    $body = array(
        'keyphrases' => json_encode($keyphrases),
        'website' => $website,
        'requester' => $user_email,
        'related_links' => json_encode($related_links),
        'category_id' => $category_id, 
        'username' => $username,
        'password' => $password,
        'website_description' => $website_description,
        'image_style' => $image_style,
        'blog_language' => $blog_language
    );
    
    // Use wp_remote_post to perform the request
    $response = wp_remote_post($url, array(
        'headers' => $headers,
        'body' => $body,
        'method' => 'POST',
        'timeout' => 45,
    ));



    // Check for errors in the response
    if (is_wp_error($response)) {
        error_log("error found in response");
        $error_message = $response->get_error_message();
        wp_die("Something went wrong: $error_message");
    } else {
        // Handle the successful response
        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        // Do something with the response
        return array(
            'data' => $response_body,
            'status' => $status_code
        );
    }
}






?>