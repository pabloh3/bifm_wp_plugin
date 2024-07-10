<?php
function get_widget($parameters, $run_id, $tool_call_id) {
    // Create a random id for the widget
    $widget_id = uniqid();
    $parameters_json = json_encode($parameters);

    // Use Materialize card layout for the widget with two buttons (authorize and reject)
    $widget = "<div id='validate-API_get-widget-" . $widget_id . "' class='card'>
    <div class='card-content'>
        <span class='card-title'>Billy wants to review your site</span>
        <p>Billy requested access to read your site's configuration and draft content.</p>
    </div>
    <div style='display: flex;'>
        <button id='authorize-API_get-" . $widget_id . "' class='bifm-btn waves-effect waves-light card-button'>Go ahead</button>
        <button id='reject-API_get-" . $widget_id . "' class='bifm-btn waves-effect waves-light grey card-button'>Nope</button>
    </div>
</div>";


    // JavaScript for handling button events and display messages as bubbles
    $script = "
    document.getElementById('authorize-API_get-" . $widget_id . "').addEventListener('click', function(event) {
        var data = " . $parameters_json . "; 
        console.log('authorize API_get');
        event.preventDefault();
        data['authorize'] = true;
        var bubble = document.createElement('div');
        bubble.classList.add('billy-bubble', 'bubble');
        bubble.innerHTML = '<p>You authorized Billy to read drafts and site configuration.</p>';
        document.getElementById('validate-API_get-widget-" . $widget_id . "').outerHTML = bubble.outerHTML;
        var processingMessage = document.createElement('div');
        processingMessage.innerHTML = '<div id=\"billy-responding\" class=\"processing-message\">Processing<span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span></div>';
        document.getElementById('billy-chatbox').appendChild(processingMessage);
        sendMessage(data, 'API_get', '" . $run_id . "', '" . $tool_call_id . "');
    });
    document.getElementById('reject-API_get-" . $widget_id . "').addEventListener('click', function() {
        var data = " . $parameters_json . "; 
        console.log('reject API_get');
        data['authorize'] = false;
        var bubble = document.createElement('div');
        bubble.classList.add('billy-bubble', 'bubble');
        bubble.innerHTML = '<p>You rejected granting Billy private access to your site.</p>';
        document.getElementById('validate-API_get-widget-" . $widget_id . "').outerHTML = bubble.outerHTML;
        var processingMessage = document.createElement('div');
        processingMessage.innerHTML = '<div id=\"billy-responding\" class=\"processing-message\">Processing<span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span></div>';
        document.getElementById('billy-chatbox').appendChild(processingMessage);
        sendMessage(data, 'API_get', '" . $run_id . "', '" . $tool_call_id . "');
    });
    ";

    // Return a dictionary with both widget and script
    return array('widget' => $widget, 'script' => $script);
}
?>
