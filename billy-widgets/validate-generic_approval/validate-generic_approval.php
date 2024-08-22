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
    $message = $parameters['message'];
    $card_title = $parameters['card_title'];
    // check if the parameters have a selctor
    $selector_section = '';
    if (!empty($parameters['selector'])) {
        $selector = $parameters['selector'];
        if ($selector == 'all_pages') {
            // load the code from the selector-all_pages.php file
            include plugin_dir_path(__FILE__) . 'selector-all_pages.php';
            $selector_section = bifm_get_selector_html($parameters);
        }
    }

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
        bubble.innerHTML = '<p>' + " .__('You authorized Billy to make changes. This could take a few minutes.', 'bifm') . " + '</p>';
        document.getElementById('validate-generic_approval-widget-" . esc_attr($widget_id) . "').outerHTML = bubble.outerHTML;
        var processingMessage = document.createElement('div');
        processingMessage.innerHTML = '<div id=\"billy-responding\" class=\"processing-message\">' + " . __('Processing', 'bifm') . " + '<span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span></div>';
        document.getElementById('billy-chatbox').appendChild(processingMessage);
        sendMessage(data, 'generic_approval', " . esc_attr($run_id) . ", " . esc_attr($tool_call_id) . ");
    });

    document.getElementById('reject-generic_approval-" . esc_attr($widget_id) . "').addEventListener('click', function(event) {
        event.preventDefault();
        var data = " . wp_json_encode($parameters) . "; 
        console.log('reject generic_approval');
        data.authorize = false;
        var bubble = document.createElement('div');
        bubble.classList.add('billy-bubble', 'bubble');
        bubble.innerHTML = '<p>' + " . __('You rejected the changes Billy wanted to make on your site.', 'bifm') . " + '</p>';
        document.getElementById('validate-generic_approval-widget-" . esc_attr($widget_id) . "').outerHTML = bubble.outerHTML;
        
        var processingMessage = document.createElement('div');
        processingMessage.innerHTML = '<div id=\"billy-responding\" class=\"processing-message\">' + " . __('Processing', 'bifm') . " + '<span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span></div>';
        document.getElementById('billy-chatbox').appendChild(processingMessage);
        
        sendMessage(data, 'generic_approval', " . esc_attr($run_id) . ", " . esc_attr($tool_call_id) . ");
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