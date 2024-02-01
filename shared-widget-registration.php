<?php

// Register custom widget: smart_chat
add_action('elementor/widgets/widgets_registered', 'register_custom_widget_smart_chat');
function register_custom_widget_smart_chat() {
    // Include the widget class file
    require_once(__DIR__ . '/shared-widgets/smart_chat/smart_chat.php');
    \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \smart_chat());
}

