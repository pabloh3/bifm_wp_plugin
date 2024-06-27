<?php
    $nonce = wp_create_nonce('my_custom_action');

    // Materialize CSS and Icons
    //echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">';
    echo '<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">';

    //echo '<div style="color:red">Using local API.</div>';
    //echo '<div style="color:red">Validate https in new_blogpost and upload_image.</div>';

    // Tabs structure
    echo '<div class="bifm-row">';
    //disclaimers for staging

    echo '<div class="bifm-col s12">';
    echo '<ul class="tabs">';
    echo '<li class="tab bifm-col s2"><a href="#smart-chat">Smart chat</a></li>';
    echo '<li class="tab bifm-col s2"><a href="#blog-generator">Blog Generator</a></li>';
    echo '<li class="tab bifm-col s2"><a href="#settings">Blog Settings</a></li>';
    echo '<li class="tab bifm-col s2"><a href="#widget-generator">Widget Generator</a></li>';
    echo '</ul>';
    echo '</div>';

    echo '<div class="container">';  // using Materialize's container for alignment and spacing

    // Smart chat Tab
    echo '<div id="smart-chat" class="bifm-col s12">';
    echo '<h5>Ask Billy</h5>';
    echo '<p>Ask Billy, your WordPress expert, anything about your website and he will help you out!</p>';
    // insert billy-page.php
    include_once 'billy-page.php';
    // Button to modify smart chat settings
    echo '<button id="createNewChat" class="bifm-btn waves-effect waves-light red lighten-2">Modify Smart Chat Settings</button>';
    echo '<button id="backButton" class="bifm-btn waves-effect waves-light red lighten-2" style="display: none;"><i class="material-icons left">arrow_back</i>Back</button>';
    echo '</div>';
    //end smart chat tab

    // Widget Generator Tab
    echo '<div id="widget-generator" class="bifm-col s12">';
    echo '<h5>Widget Generator</h5>';
        // Button to create a new widget
        echo '<button id="createNewWidget" class="bifm-btn waves-effect waves-light red lighten-2">Create new widget</button>';
        echo '<button id="backButton" class="bifm-btn waves-effect waves-light red lighten-2" style="display: none;"><i class="material-icons left">arrow_back</i>Back</button>';
    
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



    echo '<script>var my_script_object = { ajax_url: "' . esc_js(admin_url('admin-ajax.php')) . '", nonce: "' . esc_js($nonce) . '" };</script>';


    // Materialize JavaScript
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>';
    ?>
    




