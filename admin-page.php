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
            echo '<li class="collection-item" id="' . $widget_name . '">';
            echo '<div>' . $widget->get_title() . '<a href="#!" class="secondary-content delete-widget" data-widget-name="' . $widget_name . '" style="margin-left: 5px;"><i class="material-icons">delete</i></a>';
            echo '</div></li>';
        }
    }

    echo '</ul>';
    
    echo '<script>var my_script_object = { ajax_url: "' . admin_url('admin-ajax.php') . '", nonce: "' . $nonce . '" };</script>';

    echo "
        <script>
             document.getElementById('createNewBlog').addEventListener('click', newBlog);
            
            function newBlog() {
                window.location.href = 'admin.php?page=create-blog'; // Redirect to the provided URL
            }
            
            document.getElementById('createNewWidget').addEventListener('click', requestFolderName);
            
            function requestFolderName() {
                fetch(ajaxurl + '?action=get_folder_name')
                .then(response => response.json()) // Parse the response as JSON
                .then(data => {
                    if (data.success && data.data.redirectUrl) {
                        window.location.href = data.data.redirectUrl; // Redirect to the provided URL
                    } else {
                        displayWarning(data.data);
                    }
                })
                .catch(error => {
                    console.error('Error fetching new page:', error);
                });
            }

    
            document.querySelectorAll('.delete-widget').forEach(button => {
                button.addEventListener('click', function() {
                    var widgetName = this.getAttribute('data-widget-name');
                    if (confirm('Are you sure you want to delete the widget? This is irreversible.')) {
                    deleteWidget(widgetName);
                    }
                });
            });
            
            function deleteWidget(widgetName) {
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=delete_custom_widget&widget_name=' + widgetName + '&nonce=' + my_script_object.nonce,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(widgetName).remove();
                        alert('Widget deleted successfully.');
                    } else {
                        alert('Error deleting widget.');
                    }
                });
            }
            function displayWarning(message) {
                var warningDiv = document.getElementById('warningMessage');
                warningDiv.textContent = message;
            
                // Make the warning box visible
                warningDiv.style.display = 'block';
            }

    </script>
    ";
    // Materialize JavaScript
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>';