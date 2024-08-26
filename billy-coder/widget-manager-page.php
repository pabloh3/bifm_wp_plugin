<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<br/>
<a href="admin.php?page=bifm" class="bifm-btn  waves-effect waves-light purple light-grey" style="width: 120px;">
    <i class="material-icons left">arrow_back</i>
    <?php esc_html_e('Back','bifm'); ?>
</a>
<div class="container">
    <div id="widget-generator" class="bifm-col s12">
        <h5><?php esc_html_e('Widget Generator','bifm'); ?></h5>
        <?php if(is_plugin_active('elementor/elementor.php')): ?>
        <!-- Button to create a new widget -->
        <button id="createNewWidget" class="bifm-btn  waves-effect waves-light"><?php esc_html_e('Create new widget','bifm'); ?></button>
        <br/>
        <?php
        $widgets_manager = \Elementor\Plugin::$instance->widgets_manager;
        $widget_types = $widgets_manager->get_widget_types();
        ?>

        <ul class="collection with-header" id="widget-list" style="max-width: 500px;">
            <li class="collection-header" style="margin-bottom: 0;"><h4><?php esc_html_e('Your widgets','bifm'); ?></h4></li>
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
    <?php else: ?>
        <p><?php esc_html_e('Elementor is not active. Please activate it to start creating your own widgets with this tool.','bifm'); ?></p>
    <?php endif; ?>
    </div>
</div>