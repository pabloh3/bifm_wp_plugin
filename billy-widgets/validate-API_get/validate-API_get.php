<?php
if ( ! defined( 'ABSPATH' ) ) exit;
function bifm_get_widget($parameters, $run_id, $tool_call_id) {
    // Create a random id for the widget
    $widget_id = uniqid();
    $parameters_json = wp_json_encode($parameters);

    $rejection_text = __('You rejected granting Billy private access to your site.', 'bifm');
    $acceptance_text = __('You authorized Billy to read drafts and site configuration.', 'bifm');

    // Use Materialize card layout for the widget with two buttons (authorize and reject)
    $widget = "<div id='validate-API_get-widget-" . $widget_id . "' class='card'>
    <div class='card-content'>
        <span class='card-title'>".__("Billy wants to review your site","bifm")."</span>
        <p>".__("Billy requested access to read your site's configuration and draft content.","bifm")."</p>
    </div>
    <div style='display: flex;'>
        <button id='authorize-API_get-" . esc_attr($widget_id) . "' class='bifm-btn waves-effect waves-light card-button'>".__("Go ahead",'bifm')."</button>
        <button id='reject-API_get-" . esc_attr($widget_id) . "' class='bifm-btn waves-effect waves-light grey card-button'>".__("Nope","bifm")."</button>
    </div>
</div>";


    // JavaScript for handling button events and display messages as bubbles
    $script = "
    document.getElementById('authorize-API_get-" . esc_attr($widget_id) . "').addEventListener('click', function(event) {
        var data = " . $parameters_json . "; 
        console.log('authorize API_get');
        event.preventDefault();
        data['authorize'] = true;
        var bubble = document.createElement('div');
        bubble.classList.add('billy-bubble', 'bubble');
        bubble.innerHTML = '<p> " . $acceptance_text . "</p>';
        document.getElementById('validate-API_get-widget-" . esc_attr($widget_id) . "').outerHTML = bubble.outerHTML;
        var processingMessage = document.createElement('div');
        processingMessage.innerHTML = '<div id=\"billy-responding\" class=\"processing-message\">Processing<span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span></div>';
        try {
            document.getElementById('billy-chatbox').appendChild(processingMessage);
        } catch (error) {
            console.error('No chatbox to append processing');
        }
        sendMessage(data, 'API_get', '" . esc_attr($run_id) . "', '" . esc_attr($tool_call_id) . "');
    });
    document.getElementById('reject-API_get-" . esc_attr($widget_id) . "').addEventListener('click', function() {
        var data = " . $parameters_json . "; 
        console.log('reject API_get');
        data['authorize'] = false;
        var bubble = document.createElement('div');
        bubble.classList.add('billy-bubble', 'bubble');
        bubble.innerHTML = '<p>". $rejection_text ."</p>';
        document.getElementById('validate-API_get-widget-" . esc_attr($widget_id) . "').outerHTML = bubble.outerHTML;
        var processingMessage = document.createElement('div');
        processingMessage.innerHTML = '<div id=\"billy-responding\" class=\"processing-message\">Processing<span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span></div>';
        try {
            document.getElementById('billy-chatbox').appendChild(processingMessage);
        } catch (error) {
            console.error('No chatbox to append processing');
        }
        sendMessage(data, 'API_get', '" . esc_attr($run_id) . "', '" . esc_attr($tool_call_id) . "');
    });
    ";

    // Return a dictionary with both widget and script
    return array('widget' => $widget, 'script' => $script);
}
