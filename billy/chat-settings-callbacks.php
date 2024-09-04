<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Define the base URL for the API
require_once( __DIR__ . '/../bifm-config.php' );

// DOCUMENTATION //
/* This file contains the AJAX callbacks for the smart chat settings page (Billy's settings)
If this is never called, no worries, on the first request the assistant id is created and id and vector store id are stored in the database.
HOOW TEH INFO IS SOTRED IN THE DATABASE THIS IS ALL AT WEBSITE LEVEL
update_option('bifm_assistant_instructions', '');
update_option('bifm_uploaded_file_names', array());
update_option('bifm_assistant_id', '');
update_option('bifm_vector_store_id', null);
*/


// Register AJAX actions for logged-in users
add_action('wp_ajax_bifm_smart_chat_settings', 'bifm_smart_chat_settings');
add_action('wp_ajax_bifm_smart_chat_reset', 'bifm_smart_chat_reset');

function bifm_smart_chat_reset() {
    try {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'update-chat-settings-nonce')) {
            throw new Exception(__('Nonce verification failed!', 'bifm'));
        }

        global $BIFM_API_URL;
        $url = $BIFM_API_URL . '/delete_assistant';

        $response = wp_remote_post($url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body'    => wp_json_encode(array(
                'assistant_id'    => get_option('bifm_assistant_id'),
                'vector_store_id' => get_option('bifm_vector_store_id'),
            )),
            'method'  => 'POST',
            'timeout' => 10,
        ));

        // Reset options even if the API call fails
        if (!is_wp_error($response)) {
            update_option('bifm_assistant_instructions', '');
            update_option('bifm_uploaded_file_names', array());
            update_option('bifm_assistant_id', '');
            update_option('bifm_vector_store_id', null);
            wp_send_json_success(__('Chatbot reset successfully.', 'bifm'));
        } else {
            throw new Exception(__('Error when resetting chatbot: ', 'bifm') . $response->get_error_message());
        }
        
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage(), 400);
    }
}

function bifm_smart_chat_settings() {
    try {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'update-chat-settings-nonce')) {
            throw new Exception(__('Nonce verification failed!', 'bifm'));
        }

        // Update assistant instructions
        $assistant_instructions = sanitize_text_field(wp_unslash($_POST['bifm_assistant_instructions'] ?? ''));
        update_option('bifm_assistant_instructions', $assistant_instructions);

        $assistant_id = get_option('bifm_assistant_id');
        $files_to_delete = array();
        $uploaded_file_ids = array();

        if (!empty($_POST['files_list']) && is_array($_POST['files_list'])) {
            $files_list_posted = array_map('sanitize_text_field', $_POST['files_list']);
        } else {
            $files_list_posted = array();
        }

        // Remove files from database that are not in the posted list
        $stored_files = get_option('bifm_uploaded_file_names', array());
        //debug 
        print_r($stored_files, true);
        foreach ($stored_files as $stored_file) {
            if (!in_array($stored_file['file_name'], $files_list_posted)) {
                error_log("file to delete: " . $stored_file['file_id']);
                $files_to_delete[] = $stored_file['file_id'];
            } else {
                $uploaded_file_ids[] = $stored_file['file_id'];
            }
        }
        update_option('bifm_uploaded_file_names', array_filter($stored_files, function($file) use ($files_to_delete) {
            return !in_array($file['file_id'], $files_to_delete);
        }));
        // debug delete
        print_r($files_to_delete, true);


        // Handle file uploads
        if (!empty($_FILES['files']['name'][0])) {
            $boundary = 'UYTvLhK1u09E5OyhWJBqEZPS';
            $headers = array('Content-Type' => 'multipart/form-data; boundary=' . $boundary);

            foreach ($_FILES['files']['name'] as $key => $name) {
                $file_info = array(
                    'name'     => sanitize_file_name($name),
                    'type'     => $_FILES['files']['type'][$key],
                    'tmp_name' => $_FILES['files']['tmp_name'][$key],
                    'error'    => $_FILES['files']['error'][$key],
                    'size'     => $_FILES['files']['size'][$key],
                );

                // Validate the file
                $filetype = wp_check_filetype($file_info['name']);
                if (!in_array($filetype['ext'], array('txt', 'pdf', 'docx', 'doc', 'csv', 'xls', 'xlsx', 'ppt', 'pptx'))) {
                    throw new Exception(__('File type not allowed: ', 'bifm') . $filetype['ext']);
                }

                $body = '--' . $boundary . "\r\n";
                $body .= 'Content-Disposition: form-data; name="file"; filename="' . $file_info['name'] . '"' . "\r\n";
                $body .= 'Content-Type: ' . $file_info['type'] . "\r\n\r\n";
                $body .= file_get_contents($file_info['tmp_name']) . "\r\n";
                $body .= '--' . $boundary . '--';

                // Send the file to the API
                global $BIFM_API_URL;
                $file_response = wp_remote_post($BIFM_API_URL . '/upload_file', array(
                    'headers' => $headers,
                    'body'    => $body,
                    'method'  => 'POST',
                    'timeout' => 60,
                ));

                if (is_wp_error($file_response)) {
                    throw new Exception(__('File upload failed: ', 'bifm') . $file_response->get_error_message());
                }

                $status_code = wp_remote_retrieve_response_code($file_response);
                $response_body = json_decode(wp_remote_retrieve_body($file_response), true);

                if ($status_code == 200) {
                    $uploaded_file_ids[] = $response_body['files']['file_id'];
                    $stored_files[] = array(
                        'file_name' => $response_body['files']['file_name'],
                        'file_id'   => $response_body['files']['file_id'],
                    );
                } else {
                    throw new Exception(__('File upload error: ', 'bifm') . $response_body['message']);
                }
            }
            update_option('bifm_uploaded_file_names', $stored_files);
        }

        // Send the updated settings to the API
        global $BIFM_API_URL;
        $url = $BIFM_API_URL . '/assistant_update';
        $site_info = bifm_collect_site_info();
        $response = wp_remote_post($url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body'    => wp_json_encode(array(
                'assistant_id'           => $assistant_id,
                'assistant_instructions' => $assistant_instructions,
                'assistant_files'        => $uploaded_file_ids,
                'files_to_delete'        => $files_to_delete,
                'site_url'               => get_site_url(),
                'site_theme'             => $site_info,
                'vector_store_id'        => get_option('bifm_vector_store_id'),
            )),
            'method'  => 'POST',
            'timeout' => 10,
        ));

        if (is_wp_error($response)) {
            throw new Exception(__('Error when updating chat settings: ', 'bifm') . $response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        if ($status_code == 200) {
            $assistant_id = $response_body['assistant_id'];
            update_option('bifm_assistant_id', $assistant_id);
            $vector_store_id = $response_body['vector_store_id'];
            update_option('bifm_vector_store_id', $vector_store_id);
            // check if there's any message in the response
            if (isset($response_body['message'])) {
                wp_send_json_success(array('message' => $response_body['message']));
            } else {
                wp_send_json_success(__('Settings saved successfully.', 'bifm'));
            }
        } else {
            // remove the newly uploaded file from db
            if (!empty($uploaded_file_ids)) {
                $stored_files = get_option('bifm_uploaded_file_names', array());
                update_option('bifm_uploaded_file_names', array_filter($stored_files, function($file) use ($uploaded_file_ids) {
                    return !in_array($file['file_id'], $uploaded_file_ids);
                }));
            }
            if (isset($response_body['message'])) {
                throw new Exception(__('Error when updating chat settings: ', 'bifm') . $response_body['message']);
            } else {
                throw new Exception(__('Error when updating chat settings.', 'bifm'));
            }
        }
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage(), 400);
    }
}

function bifm_collect_site_info() {
    global $wp_version;
    $theme = wp_get_theme();
    $site_info = sprintf(__('The site is running on WordPress version: %s. The theme is: %s Version: %s. ', 'bifm'),
        $wp_version,
        $theme->get('Name'),
        $theme->get('Version')
    );

    // Check if Elementor is active
    if (is_plugin_active('elementor/elementor.php')) {
        $elementor_data = get_plugin_data(WP_PLUGIN_DIR . '/elementor/elementor.php');
        $site_info .= __('Elementor is active. ', 'bifm');
        $site_info .= sprintf(__('Elementor Version: %s. ', 'bifm'), $elementor_data['Version']);
    } else {
        $other_editors = bifm_check_other_page_builders();
        if (!empty($other_editors)) {
            $site_info .= __('Page builders active: ', 'bifm') . implode(', ', $other_editors) . '. ';
        }
    }

    return $site_info;
}

function bifm_check_other_page_builders() {
    $other_editors = array();

    // Check for popular page builders
    $page_builders = array(
        'classic-editor/classic-editor.php',
        'beaver-builder-lite-version/fl-builder.php',
        'divi-builder/divi-builder.php',
        'js_composer/js_composer.php',
        'thrive-visual-editor/thrive-visual-editor.php',
        'brizy/brizy.php',
    );

    foreach ($page_builders as $builder) {
        if (is_plugin_active($builder)) {
            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $builder);
            $other_editors[] = $plugin_data['Name'] . ' Version: ' . $plugin_data['Version'];
        }
    }

    return $other_editors;
}
