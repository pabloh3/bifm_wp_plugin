<?php
function get_widget($parameters, $run_id, $tool_call_id) {
    // create a random id for the widget
    $widget_id = uniqid();
    $parameters_json = json_encode($parameters);
    // widget as a piece of html with two buttons (authorize and reject)
    $widget = "<div id='validate-API_get-widget-" . $widget_id . "' style='border: 1px solid black; padding: 10px; margin: 10px;'>";
    $widget .= "<b>Billy wants to review your site:</b>";
    $widget .= "<p>Billy requested access to read your site's configuration and draft content.</p>";
    $widget .= "<button id='authorize-API_get-" . $widget_id . "' style='margin:10px'>Go ahead</button>";
    $widget .= "<button id='reject-API_get-" . $widget_id . "' style='margin:10px'>Nope</button>";
    $script = "
    document.getElementById('authorize-API_get-" . $widget_id . "').addEventListener('click', function(event) {
        var data =" . $parameters_json . "; 
        console.log('authorize API_get');
        event.preventDefault();
        data['authorize'] = true;
        document.getElementById('validate-API_get-widget-" . $widget_id . "').innerHTML = 'You authorized Billy read drafts and site configuration.';
        processingMessage.innerHTML = '<div id=\"billy-responding\" class=\"processing-message\">Processing<span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span></div>';
        document.getElementById('billy-chatbox').appendChild(processingMessage);
        sendMessage(data, 'API_get', '" . $run_id . "', '" . $tool_call_id . "');
    });
    document.getElementById('reject-API_get-" . $widget_id . "').addEventListener('click', function() {
        var data =" . $parameters_json . "; 
        console.log('reject API_get');
        data['authorize'] = false;
        document.getElementById('validate-API_get-widget-" . $widget_id . "').innerHTML = 'You rejected granting Billy private access to your site.';
        sendMessage(data, 'API_get', '" . $run_id . "', '" . $tool_call_id . "');
    });
    ";
    
    // return a dictionary with  both widget and script
    return array('widget' => $widget, 'script' => $script);
}