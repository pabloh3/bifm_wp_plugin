<?php 

// Handle change blog settings
// Add action for logged-in users
add_action('wp_ajax_bifm_save_settings', 'handle_bifm_save_settings');

// Function to handle form submission
function handle_bifm_save_settings() {
    try {
        // Check for nonce security
        if (!isset($_POST['bifm_nonce']) || !wp_verify_nonce($_POST['bifm_nonce'], 'bifm-writer-settings-nonce')) {
            $nonce_new = wp_create_nonce('bifm-writer-settings-nonce');
            error_log("Nonce verification failed! New nonce: " . $nonce_new);
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
?>