<?php
if ( ! defined( 'ABSPATH' ) ) exit;
function bifm_get_widget($parameters, $run_id, $tool_call_id) {
    // Create a random id for the widget
    $widget_id = uniqid();
    $parameters_json = wp_json_encode($parameters);
    $selector = $parameters['selector'];
    $message = $parameters['message'];
    $card_title = $parameters['card_title'];
    // check if the parameters have a selctor
    $selector_section = '';
    if (!empty($parameters['selector'])) {
        if ($parameters['selector'] == 'all_pages') {
            // load the code from the selector-all_pages.php file
            ob_start();
            include 'selector-all_pages.php';
            $selector_section = ob_get_clean();
        }
    }

    // Use Materialize card layout for the widget with two buttons (authorize and reject)
    $widget = "<div id='validate-generic_approval-widget-" . $widget_id . "' class='card'>
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
        var data = " . $parameters_json . "; 
        console.log('authorize generic_approval');
        event.preventDefault();
        data['authorize'] = true;
        var bubble = document.createElement('div');
        bubble.classList.add('billy-bubble', 'bubble');
        bubble.innerHTML = '<p>You authorized Billy to do changes in your page..</p>';
        document.getElementById('validate-generic_approval-widget-" . esc_attr($widget_id) . "').outerHTML = bubble.outerHTML;
        var processingMessage = document.createElement('div');
        processingMessage.innerHTML = '<div id=\"billy-responding\" class=\"processing-message\">Processing<span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span></div>';
        document.getElementById('billy-chatbox').appendChild(processingMessage);
        // if there is a selector, get the value and add it to the data
        if (document.getElementById('bifm_generic_approval_selector')) {
            data['selector'] = ". esc_attr($selector) .";
            name = document.getElementById('bifm_generic_approval_selector').name;
            console.log('name: ', name);
            data[name] = document.getElementById('bifm_generic_approval_selector').value;
            console.log('data: ', data);
        }
        sendMessage(data, 'generic_approval', '" . esc_attr($run_id) . "', '" . esc_attr($tool_call_id) . "');
    });
    
    document.getElementById('reject-generic_approval-" . esc_attr($widget_id) . "').addEventListener('click', function() {
        var data = " . $parameters_json . "; 
        console.log('reject generic_approval');
        data['authorize'] = false;
        var bubble = document.createElement('div');
        bubble.classList.add('billy-bubble', 'bubble');
        bubble.innerHTML = '<p>You rejected changes Billy wanted to do on your site.</p>';
        document.getElementById('validate-generic_approval-widget-" . esc_attr($widget_id) . "').outerHTML = bubble.outerHTML;
        var processingMessage = document.createElement('div');
        processingMessage.innerHTML = '<div id=\"billy-responding\" class=\"processing-message\">Processing<span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span></div>';
        document.getElementById('billy-chatbox').appendChild(processingMessage);
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
?>
