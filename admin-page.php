<?php
    $nonce = wp_create_nonce('my_custom_action');

    // Materialize CSS and Icons
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">';
    echo '<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">';

    //echo '<div style="color:red">Using local API.</div>';
    //echo '<div style="color:red">Validate https in new_blogpost and upload_image.</div>';

    // Tabs structure
    echo '<div class="row">';
    //disclaimers for staging

    echo '<div class="col s12">';
    echo '<ul class="tabs">';
    echo '<li class="tab col s2"><a href="#smart-chat">Smart chat</a></li>';
    echo '<li class="tab col s2"><a href="#blog-generator">Blog Generator</a></li>';
    echo '<li class="tab col s2"><a href="#settings">Blog Settings</a></li>';
    echo '<li class="tab col s2"><a href="#widget-generator">Widget Generator</a></li>';
    echo '</ul>';
    echo '</div>';

    echo '<div class="container">';  // using Materialize's container for alignment and spacing

    // Smart chat Tab
    echo '<div id="smart-chat" class="col s12">';
    echo '<h5>Ask Billy</h5>';
    echo '<p>Ask Billy, your WordPress expert, anything about your website and he will help you out!</p>';
    // insert billy-page.php
    include_once 'billy-page.php';
    // Button to modify smart chat settings
    echo '<button id="createNewChat" class="btn waves-effect waves-light red lighten-2">Modify Smart Chat Settings</button>';
    echo '<button id="backButton" class="btn waves-effect waves-light red lighten-2" style="display: none;"><i class="material-icons left">arrow_back</i>Back</button>';
    echo '</div>';
    //end smart chat tab

    // Widget Generator Tab
    echo '<div id="widget-generator" class="col s12">';
    echo '<h5>Widget Generator</h5>';
        // Button to create a new widget
        echo '<button id="createNewWidget" class="btn waves-effect waves-light red lighten-2">Create new widget</button>';
        echo '<button id="backButton" class="btn waves-effect waves-light red lighten-2" style="display: none;"><i class="material-icons left">arrow_back</i>Back</button>';
    
        $widgets_manager = \Elementor\Plugin::$instance->widgets_manager;
        $widget_types = $widgets_manager->get_widget_types();
    
        echo '<ul class="collection with-header" id="widget-list" style="max-width: 500px;">';
        echo '<li class="collection-header" style="margin-bottom: 0;"><h4>Your widgets</h4></li>';
        foreach ($widget_types as $widget) {
            $reflector = new ReflectionClass(get_class($widget));
            $widget_file_path = $reflector->getFileName();
            $widget_name = $widget->get_name(); // Get the widget name
        
            // Check if the widget's file is in your custom folder
            if (strpos($widget_file_path, 'bifm-widgets') !== false) {
                echo '<li class="collection-item" id="' . esc_attr($widget_name) . '">';
                echo '<div>' . esc_html($widget->get_title()) . '<a href="#!" class="secondary-content delete-widget" data-widget-name="' . esc_attr($widget_name) . '" style="margin-left: 5px;"><i class="material-icons">delete</i></a>';
                echo '</div></li>';
            }
        }
    echo '</div>';

    // Blog Generator Tab
    echo '<div id="blog-generator" class="col s12">';
    echo '<h5>Blog Generator</h5>';
    // Button to create a new blog
    echo '<button id="createNewBlog" class="btn waves-effect waves-light red lighten-2">Create new blog post</button>';
    echo '<button id="backButton" class="btn waves-effect waves-light red lighten-2" style="display: none;"><i class="material-icons left">arrow_back</i>Back</button>';
    echo '</div>';

    // Settings Tab
    echo '<div id="settings" class="col s12">';
    echo '<h5>Settings</h5>';
// Add settings content here...
    echo '<p>Here you can update the settings for blog creation.</p>';
    echo '<p>Please note that the blog creator requires the "JSON Basic Authentication" plugin by the Wordpress API team to be installed.</p>';

    // Start of the form
    echo '<form id="bifm-settings-form" action="#" method="post">';

    // Blog author's username
    $user_id = get_current_user_id();
    $username = get_user_meta($user_id, 'username', true);
    echo '<div class="row"><div class="input-field col s12 l4">';
    if (!is_null($username)) {
        echo '<input id="blog_author_username" type="text" name="blog_author_username" class="validate materialize-textarea" value="' . htmlspecialchars($username) . '" required >';
    } else {
        echo '<input id="blog_author_username" type="text" name="blog_author_username" class="validate materialize-textarea" required >';
    }
    echo '<label for="blog_author_username">Blog author\'s username</label>';
    echo '<p style="color:red">Create an author account that only has author access, DO NOT use an admin account. </p>';
    echo '</div>';

    // Blog author's password
    echo '<div class="input-field col s12 l4">';
    echo '<input id="blog_author_password" type="password" name="blog_author_password" class="validate materialize-textarea">';
    echo '<label for="blog_author_password">Blog author\'s password</label>';
    echo '</div></div>';

    // Language for new blog posts
    $blog_language = get_user_meta($user_id, 'blog_language', true);
    echo '<div class="row"><div class="input-field col s12 l4">';
    echo '<select id="blog_language" name="blog_language">';
    echo '<option value="" disabled' . (empty($blog_language) ? ' selected' : '') . '>Choose your option</option>';
    echo '<option value="english"' . ($blog_language == 'english' ? ' selected' : '') . '>English</option>';
    echo '<option value="spanish"' . ($blog_language == 'spanish' ? ' selected' : '') . '>Spanish</option>';
    echo '</select>';
    echo '<label for="blog_language">Language for new blog posts</label>';
    echo '</div>';

    // Image dimensions
    $image_width = get_user_meta($user_id, 'image_width', true);
    echo '<div class="input-field col s12 l2">';
    echo '<select id="image_width" name="image_width">';
    echo '<option value="" disabled' . (empty($image_width) ? ' selected' : '') . '>Width</option>';
    echo '<option value="1024"' . ($image_width == '1024' ? ' selected' : '') . '>1024</option>';
    echo '<option value="1792"' . ($image_width == '1792' ? ' selected' : '') . '>1792</option>';
    echo '</select>';
    echo '<label for="image_width">Image width (px)</label>';
    echo '</div>';        
    $image_height = get_user_meta($user_id, 'image_height', true);
    echo '<div class="input-field col s12 l2">';
    echo '<select id="image_height" name="image_height">';
    echo '<option value="" disabled' . (empty($image_height) ? ' selected' : '') . '>Height</option>';
    echo '<option value="1024"' . ($image_height == '1024' ? ' selected' : '') . '>1024</option>';
    echo '<option value="1792">1792</option>';
    echo '</select>';
    echo '<label for="image_height">Image height (px)</label>';
    echo '</div></div>';

    // Website description
    $website_description = get_user_meta($user_id, 'website_description', true);
    echo '<div class="row"><div class="input-field col s12 l8">';
    if (!is_null($website_description)) {
        echo '<textarea id="website_description" name="website_description" class="materialize-textarea">' . htmlspecialchars($website_description) . '</textarea>';
    } else {
        echo '<textarea id="website_description" name="website_description" class="materialize-textarea"></textarea>';
    }
    echo '<label for="website_description"><b>Instructions for the writer bot:</b> Describe your website and how you want your posts written.</label>';
    echo '</div></div>';

    // Image style 
    $image_style = get_user_meta($user_id, 'image_style', true);
    echo '<div class="row"><div class="input-field col s12 l8">';
    if (!is_null($image_style)) {
        echo '<textarea id="image_style" name="image_style" class="materialize-textarea">' . htmlspecialchars($image_style) . '</textarea>';
    } else {
        echo '<textarea id="image_style" name="image_style" class="materialize-textarea"></textarea>';
    }
    echo '<label for="image_style"><b>Instructions for the image generation bot:</b> Describe the style you want for your images.</label>';
    echo '</div></div>';

    // Submit button
    echo '<button class="btn waves-effect waves-light" type="submit" name="action" >Update</button>';
    echo '</form>';
    echo '</div>'; // Close the settings tab


    // Warning message
    echo '<div id="warningMessage" class="card-panel yellow darken-2" style="position: fixed; left: 0; bottom: 0; width: 100%; text-align: center; display: none; z-index: 1000;">This is a warning message!</div>';
    echo '</div>'; // Close the container

    // Inline JavaScript for Tab and multiple choice Functionality
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var elems = document.querySelectorAll(".tabs");
            var instances = M.Tabs.init(elems, {});
            var elems2 = document.querySelectorAll("select");
            var instances2 = M.FormSelect.init(elems2, {});
        });
    </script>';


    echo '<script>var my_script_object = { ajax_url: "' . esc_js(admin_url('admin-ajax.php')) . '", nonce: "' . esc_js($nonce) . '" };</script>';


    // Materialize JavaScript
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>';
    ?>
    




