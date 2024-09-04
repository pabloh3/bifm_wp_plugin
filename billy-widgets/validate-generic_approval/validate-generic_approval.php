<?php
if ( ! defined( 'ABSPATH' ) ) exit;
function bifm_get_widget($parameters, $run_id, $tool_call_id) {
    // Create a random id for the widget
    $error_bubble = "<div class='warning-bubble bubble'><p>" . __("Billy wanted to edit your page, but this function requires having Elementor installed.", "bifm") . "</p></div>";
    //check if user has elementor installed
    if ( ! did_action( 'elementor/loaded' ) ) {
        return array('widget' => $error_bubble, 'script' => '');
    }

    $widget_id = uniqid();
    $parameters_json = wp_json_encode($parameters);
    $message = sanitize_text_field($parameters['message']);
    $card_title = sanitize_text_field($parameters['card_title']);
    // check if the parameters have a selctor
    $selector_section = '';
    if (!empty($parameters['selector'])) {
        $selector = sanitize_text_field($parameters['selector']);
        if ($selector == 'all_pages') {
            // load the code from the selector-all_pages.php file
            include plugin_dir_path(__FILE__) . 'selector-all_pages.php';
            $selector_section = bifm_get_selector_html($parameters);
        }
    }

    $acceptance_text = esc_js(__('You authorized Billy to make changes. This could take a few minutes.', 'bifm'));
    $rejection_text = esc_js(__('You rejected Billy from making changes.', 'bifm'));
    $processing = esc_js(__('Processing', 'bifm'));


    // Use Materialize card layout for the widget with two buttons (authorize and reject)
    $widget = "<div id='validate-generic_approval-widget-" . esc_attr($widget_id) . "' class='card'>
        <div class='card-content' id='bifm-generic_approval'>
            <span class='card-title'>" . esc_attr($card_title) . "</span>
            <p>". esc_attr($message) ."</p>"
            . $selector_section . "
        </div>
        <div style='display: flex;'>
            <button id='authorize-generic_approval-" . esc_attr($widget_id) . "' class='bifm-btn waves-effect waves-light card-button'>".__("Go ahead",'bifm')."</button>
            <button id='reject-generic_approval-" . esc_attr($widget_id) . "' class='bifm-btn waves-effect waves-light grey card-button'>".__("Nope","bifm")."</button>
        </div>
    </div>";


    // JavaScript for handling button events and display messages as bubbles
    $script = "
    document.getElementById('authorize-generic_approval-" . esc_attr($widget_id) . "').addEventListener('click', function(event) {
        event.preventDefault();
        var data = " . $parameters_json . "; 
        data.authorize = true;
        if (document.getElementById('bifm_generic_approval_selector')) {
            var name = document.getElementById('bifm_generic_approval_selector').name;
            data[name] = document.getElementById('bifm_generic_approval_selector').value;
        }
        var bubble = document.createElement('div');
        bubble.classList.add('billy-bubble', 'bubble');
        bubble.innerHTML = '<p>" . $acceptance_text . "</p>';
        document.getElementById('validate-generic_approval-widget-" . esc_attr($widget_id) . "').outerHTML = bubble.outerHTML;
        var processingMessage = document.createElement('div');
        processingMessage.innerHTML = '<div id=\"billy-responding\" class=\"processing-message\">" . $processing .  "<span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span></div>';
        try {
            document.getElementById('billy-chatbox').appendChild(processingMessage);
        } catch (error) {
            console.error('No chatbox to append processing');
        }
        sendMessage(data, 'generic_approval', '" . esc_attr($run_id) . "', '" . esc_attr($tool_call_id) . "');
    });

    document.getElementById('reject-generic_approval-" . esc_attr($widget_id) . "').addEventListener('click', function(event) {
        event.preventDefault();
        var data = " . wp_json_encode($parameters) . "; 
        console.log('reject generic_approval');
        data.authorize = false;
        var bubble = document.createElement('div');
        bubble.classList.add('billy-bubble', 'bubble');
        bubble.innerHTML = '<p>" . $rejection_text . "</p>';
        document.getElementById('validate-generic_approval-widget-" . esc_attr($widget_id) . "').outerHTML = bubble.outerHTML;
        
        var processingMessage = document.createElement('div');
        processingMessage.innerHTML = '<div id=\"billy-responding\" class=\"processing-message\">" . $processing . "<span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span></div>';
        try {
            document.getElementById('billy-chatbox').appendChild(processingMessage);
        } catch (error) {
            console.error('No chatbox to append processing');
        }
        sendMessage(data, 'generic_approval', '" . esc_attr($run_id) . "', '" . esc_attr($tool_call_id) . "');
    });

    function reinitializeSelect() {
        setTimeout(function() {
            console.log('reinitialize select');
            var elems = document.querySelectorAll('select');
            M.FormSelect.init(elems);
        }, 100);
    }
    reinitializeSelect();";

    
    // Return a dictionary with both widget and script
    return array('widget' => $widget, 'script' => $script);
}