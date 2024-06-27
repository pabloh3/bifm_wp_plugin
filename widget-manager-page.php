<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<a href="admin.php?page=bifm-plugin" class="btn bifm-btn  waves-effect waves-light purple light-grey" style="width: 120px;">
    <i class="material-icons left">arrow_back</i>
    Back
</a>
<div id="widget-generator" class="col s12">
    <h5>Widget Generator</h5>
    <!-- Button to create a new widget -->
    <button id="createNewWidget" class="btn bifm-btn  waves-effect waves-light">Create new widget</button>
    <br/>
    <?php
    $widgets_manager = \Elementor\Plugin::$instance->widgets_manager;
    $widget_types = $widgets_manager->get_widget_types();
    ?>

    <ul class="collection with-header" id="widget-list" style="max-width: 500px;">
        <li class="collection-header" style="margin-bottom: 0;"><h4>Your widgets</h4></li>
        <?php foreach ($widget_types as $widget): ?>
            <?php
            $reflector = new ReflectionClass(get_class($widget));
            $widget_file_path = $reflector->getFileName();
            $widget_name = $widget->get_name(); // Get the widget name
            ?>
            <!-- Check if the widget's file is in your custom folder -->
            <?php if (strpos($widget_file_path, 'bifm-widgets') !== false): ?>
                <li class="collection-item" id="<?php echo esc_attr($widget_name); ?>">
                    <div>
                        <?php echo esc_html($widget->get_title()); ?>
                        <a href="#!" class="secondary-content delete-widget" data-widget-name="<?php echo esc_attr($widget_name); ?>" style="margin-left: 5px;">
                            <i class="material-icons">delete</i>
                        </a>
                    </div>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</div>