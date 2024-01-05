<?php
class smart_chat extends \Elementor\Widget_Base {
    public function get_name() {
        return 'smart_chat';
    }

    public function get_title() {
        return 'smart_chat';
    }

    public function get_icon() {
        return 'fa fa-code';
    }

    public function get_categories() {
        return ['general'];
    }


    
    protected function _register_controls() {
        // Add your controls here
    }

    //test assistant end

    protected function render() {
        $settings = $this->get_settings_for_display();
    
        // Include the PHP files
        include_once 'widget.php';
        

        // Include the HTML files
        
        // Enqueue the CSS files
        wp_enqueue_style('smart_chat_css_0', plugins_url('styles.css', __FILE__));

        // Enqueue the JS files
        wp_enqueue_script('smart_chat_js_0', plugins_url('main.js', __FILE__, array('jquery'), '1.0.9', true));
        wp_localize_script('smart_chat_js_0', 'ajax_object', array( 'ajaxurl' => admin_url('admin-ajax.php') ));
        // Localize the script with new data
        

    }
}