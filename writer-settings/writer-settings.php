<?php 
if ( ! defined( 'ABSPATH' ) ) exit;

// Handle change blog settings
// Add action for logged-in users
add_action('wp_ajax_bifm_save_settings', 'bifm_save_settings');

// Function to handle form submission
function bifm_save_settings() {
    try {
        // Check for nonce security
        if (!isset($_POST['bifm_nonce']) || !wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['bifm_nonce'])), 'bifm-writer-settings-nonce')) {
            $nonce_new = wp_create_nonce('bifm-writer-settings-nonce');
            error_log("Nonce verification failed! New nonce: " . $nonce_new);
            throw new Exception('Nonce verification failed!');
        }
        $user_id = get_current_user_id();
        // Update username and password only if username is provided
        if (isset($_POST['blog_author_username'])) {
            update_user_meta($user_id, 'username', sanitize_text_field($_POST['blog_author_username']));
        }
        
        // Update password only if the user is different than the one set
        if (!empty($_POST['blog_author_username'])) {
            // generate an application p
            if(!empty($_POST['blog_author_password'])) {
                $password = sanitize_text_field($_POST['blog_author_password']);
            } else {
                // try catch block to handle exceptions
                try {
                    $author_id = get_user_by('login', sanitize_text_field($_POST['blog_author_username']))->ID;
                    bifm_delete_old_password($author_id, $user_id);
                    $password = bifm_generate_new_password($author_id, $user_id);
                } catch (Exception $e) {
                    wp_send_json_error($e->getMessage(), 400);
                }
            }
            $password = bifm_encrypt_data($password);
            update_user_meta($user_id, 'encrypted_password', $password);
        }
        
        // Always update these settings
        update_user_meta($user_id, 'website_description', sanitize_text_field($_POST['website_description']));
        update_user_meta($user_id, 'image_style', sanitize_text_field($_POST['image_style']));
        update_user_meta($user_id, 'blog_language', sanitize_text_field($_POST['blog_language']));
        update_user_meta($user_id, 'image_width', sanitize_text_field($_POST['image_width']));
        update_user_meta($user_id, 'image_height', sanitize_text_field($_POST['image_height']));

        wp_send_json_success('Settings saved successfully.');

    } catch (Exception $e) {
        wp_send_json_error($e->getMessage(), 400);
    }
}

// Define a secret key. Store this securely and do not expose it.
function bifm_encrypt_data($data) {
    // Load the public key from this folder's public_key.pem file
    $public_key = file_get_contents(plugin_dir_path(__FILE__) . 'public_key.pem'); //phpcs:ignore
    // Encrypt the password using the public key
    openssl_public_encrypt($data, $encrypted_password, $public_key);
    $encrypted_password_base64 = base64_encode($encrypted_password);
    return $encrypted_password_base64;
}

function bifm_generate_new_password($author_id, $user_id) {
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
                throw new Exception('Failed to generate a new application password ' . esc_html($new_password->get_error_message()));
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

function bifm_delete_old_password($author_id, $user_id) {
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
                        throw new Exception('Failed to delete the application password ' . esc_html($result->get_error_message()));
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