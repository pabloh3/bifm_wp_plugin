<?php
// simple PHP file that returns hello world 
function get_widget($parameters, $run_id, $tool_call_id) {
    // create a random id for the widget
    $widget_id = uniqid();
    $keyphrase = $parameters['keyphrase'];
    // widget as a piece of html with two buttons (authorize and reject)
    $widget = "<div id='validate-writer-widget-" . $widget_id . "' style='border: 1px solid black; padding: 10px; margin: 10px;'>";
    $widget .= "<b>Billy wants to write a blog post:</b>";
    $widget .= "<p>Do you want to authorize our writer bot to build blog posts for: <b>" . $keyphrase . "</b>?</p>";
    $widget .= "<button id='authorize-writer-" . $widget_id . "' style='margin:10px'>Go ahead</button>";
    $widget .= "<button id='reject-writer-" . $widget_id . "' style='margin:10px'>Nope</button>";
    $script = "
    document.getElementById('authorize-writer-" . $widget_id . "').addEventListener('click', function(event) {
        console.log('authorize writer');
        event.preventDefault();
        var data = {'authorize': true, 'keyphrase': '" . $keyphrase . "'};
        document.getElementById('validate-writer-widget-" . $widget_id . "').innerHTML = 'You authorized the writer bot to build blog posts for: <b>" . $keyphrase . "</b> <br> It usually takes about two minutes, you can keep track of your request in the <a href=\"/wp-admin/admin.php?page=create-blog\">requests page</a>.';
        processingMessage.innerHTML = '<div id=\"billy-responding\" class=\"processing-message\">Processing<span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span></div>';
        document.getElementById('billy-chatbox').appendChild(processingMessage);
        sendMessage(data, 'writer', '" . $run_id . "', '" . $tool_call_id . "');
    });
    document.getElementById('reject-writer-" . $widget_id . "').addEventListener('click', function() {
        console.log('reject writer');
        var data = {'authorize': false};
        document.getElementById('validate-writer-widget-" . $widget_id . "').innerHTML = 'You rejected to build blog posts for: <b>" . $keyphrase . "</b>';
        processingMessage.innerHTML = '<div id=\"billy-responding\" class=\"processing-message\">Processing<span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span></div>';
        document.getElementById('billy-chatbox').appendChild(processingMessage);
        sendMessage(data, 'writer', '" . $run_id . "', '" . $tool_call_id . "');
    });
    ";
    
    // return a dictionary with  both widget and script
    return array('widget' => $widget, 'script' => $script);
}