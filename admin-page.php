<?php
    $nonce = wp_create_nonce('my_custom_action');

    // Materialize CSS and Icons
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">';
    echo '<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">';

    echo '<h3>Build It For Me - Widget Manager</h3>';

    // Button to create a new blog
    echo '<button id="createNewBlog" class="btn waves-effect waves-light red lighten-2">Create new blog post</button>';
    // Button to create a new widget
    echo '<button id="createNewWidget" class="btn waves-effect waves-light red lighten-2">Create new widget</button>';
    echo '<button id="backButton" class="btn waves-effect waves-light red lighten-2" style="display: none;"><i class="material-icons left">arrow_back</i>Back</button>';
    echo '<div id="warningMessage" class="card-panel yellow darken-2" style="display: none;"></div>';

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

    echo '</ul>';

    echo '<script>var my_script_object = { ajax_url: "' . esc_js(admin_url('admin-ajax.php')) . '", nonce: "' . esc_js($nonce) . '" };</script>';

    // Inline JavaScript should ideally be externalized to a separate file
    // ...

    // Materialize JavaScript
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>';

?>


