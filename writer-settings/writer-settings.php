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

        // Extract POST data
        $username = isset($_POST['blog_author_username']) ? $_POST['blog_author_username'] : null;
        $password = isset($_POST['blog_author_password']) ? $_POST['blog_author_password'] : null;
        $website_description = isset($_POST['website_description']) ? $_POST['website_description'] : null;
        $image_style = isset($_POST['image_style']) ? $_POST['image_style'] : null;
        $blog_language = isset($_POST['blog_language']) ? $_POST['blog_language'] : null;
        $image_width = isset($_POST['image_width']) ? $_POST['image_width'] : null;
        $image_height = isset($_POST['image_height']) ? $_POST['image_height'] : null;

        // Call the function to set the settings
        $response = bifm_set_settings($user_id, $username, $password, $website_description, $image_style, $blog_language, $image_width, $image_height);

        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message(), 400);
        } else {
            wp_send_json_success('Settings saved successfully.');
        }

    } catch (Exception $e) {
        wp_send_json_error($e->getMessage(), 400);
    }
}

// Function to set the settings
function bifm_set_settings($user_id, $username, $password, $website_description, $image_style, $blog_language, $image_width, $image_height) {
    try {
        // Update username and password only if username is provided
        if ($username) {
            update_user_meta($user_id, 'username', $username);

            // Update password only if provided
            if ($password) {
                error_log("Pass to encrypt: " . $password);
                $encrypted_password = encrypt_data($password);
                error_log("Encrypted password: " . $encrypted_password);
                update_user_meta($user_id, 'encrypted_password', $encrypted_password);
            } else {
                try {
                    $author_id = get_user_by('login', $username)->ID;
                    delete_old_password($author_id, $user_id);
                    $new_password = generate_new_password($author_id, $user_id);
                    error_log("Pass to encrypt: " . $new_password);
                    $encrypted_password = encrypt_data($new_password);
                    error_log("Encrypted password: " . $encrypted_password);
                    update_user_meta($user_id, 'encrypted_password', $encrypted_password);
                } catch (Exception $e) {
                    return new WP_Error('password_error', $e->getMessage());
                }
            }
        }

        // Always update these settings
        update_user_meta($user_id, 'website_description', $website_description);
        update_user_meta($user_id, 'image_style', $image_style);
        update_user_meta($user_id, 'blog_language', $blog_language);
        update_user_meta($user_id, 'image_width', $image_width);
        update_user_meta($user_id, 'image_height', $image_height);

        return true;

    } catch (Exception $e) {
        return new WP_Error('settings_error', $e->getMessage());
    }
}


// Define a secret key. Store this securely and do not expose it.
function encrypt_data($data) {
    // Load the public key from this folder's public_key.pem file
    error_log("data to encrypt: " . $data);
    $public_key = file_get_contents(plugin_dir_path(__FILE__) . 'public_key.pem');
    error_log("Public key: " . $public_key);
    // Encrypt the password using the public key
    openssl_public_encrypt($data, $encrypted_password, $public_key);
    error_log("decoded password: " . $encrypted_password);
    $encrypted_password_base64 = base64_encode($encrypted_password);
    return $encrypted_password_base64;
}

function generate_new_password($author_id, $user_id) {
    if ( class_exists( 'WP_Application_Passwords' ) ) {
        // Ensure the current user has the necessary capability
        if ( current_user_can( 'edit_user', $author_id ) ) {
            // Generate a new application password
            $name = "BIFM_author_" . $author_id . "_user_" . $user_id;
            $new_password = WP_Application_Passwords::create_new_application_password( $author_id, array(
                'name' => $name,
                'id'=> 'bifm'
            ) );
    
            // Check if the password was successfully generated
            if ( is_wp_error( $new_password ) ) {
                //raise exception
                throw new Exception('Failed to generate a new application password ' . $new_password->get_error_message());
            } else {
                // Output the new application password
                return $new_password[0];
            }
        } else {
            throw new Exception('User does not have the necessary capability to generate a new application password.');
        }
    } else {
        throw new Exception('WP_Application_Passwords class does not exist.');
    }
}

function delete_old_password($author_id, $user_id) {
    if ( class_exists( 'WP_Application_Passwords' ) ) {
        // Ensure the current user has the necessary capability
        if ( current_user_can( 'edit_user', $author_id ) ) {
            // Get application passwords
            $passwords = WP_Application_Passwords::get_user_application_passwords( $author_id );
            // Loop through the passwords
            $app_name = "BIFM_author_" . $author_id . "_user_" . $user_id;
            foreach ( $passwords as $password ) {
                // Check if the password name matches the one we want to delete
                if ( $password['name'] === $app_name ) {
                    // Delete the application password
                    $result = WP_Application_Passwords::delete_application_password( $author_id, $password['uuid'] );
                    // Check if the password was successfully deleted
                    if ( is_wp_error( $result ) ) {
                        throw new Exception('Failed to delete the application password ' . $result->get_error_message());
                    }
                }
            }
        } else {
            throw new Exception('User does not have the necessary capability to delete an application password.');
        }
    } else {
        throw new Exception('WP_Application_Passwords class does not exist.');
    }
}
?>