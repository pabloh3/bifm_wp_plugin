<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// simple PHP file that returns hello world 
function bifm_get_widget($parameters, $run_id, $tool_call_id) {
    // create a random id for the widget
    $widget_id = uniqid();
    $keyphrase = $parameters['keyphrase'];
    // escape chars in keyphrase
    $keyphrase = htmlspecialchars($keyphrase);
    $path = BIFM_PATH . 'static/icons/Writer.svg';
    $writer_icon = file_get_contents($path); //phpcs:ignore
    // widget as a piece of html with two buttons (authorize and reject)
    $widget = "
    <div id='validate-writer-widget-" . $widget_id . "'>
        <div class='card tool-s-chat-bubble writer-bot'>
            <div class='card-content frame-10120667'>
                <div class='frame'>
                    <div class='svg-icon writer-icon'>" . $writer_icon . "
                    </div>
                </div>
                <span class='card-title'>".__("Billy wants to write","bifm")."</span>
                <p>".__("Do you want to authorize our writer bot to build blog posts for:","bifm")." <b>" . $keyphrase . "</b>?</p>
            </div>
            <div style='display:flex;'>
                <button id='authorize-writer-" . $widget_id . "' class='bifm-btn waves-effect waves-light card-button writer-button' type='submit' name='action'>".__("Go ahead","bifm")."</button>
                <button id='reject-writer-" . $widget_id . "' class='bifm-btn waves-effect waves-light card-button grey'>".__("Nope","bifm")."</button>
            </div>
        </div>
    </div>";
    
    $script = "
    document.getElementById('authorize-writer-" . $widget_id . "').addEventListener('click', function(event) {
        console.log('authorize writer');
        event.preventDefault();
        var data = {'authorize': true, 'keyphrase': '" . $keyphrase . "'};
        let div = document.createElement('div');
        div.classList.add('writer-bubble');
        div.classList.add('bubble');
        div.style.backgroundColor = '#fffaf4';
        div.style.alignSelf = 'flex-start';
        div.innerHTML = '<p>You authorized the writer bot to build blog posts for: <b>" . $keyphrase . "</b> <br> It usually takes about two minutes, you can keep track of your request in the <a href=\"/wp-admin/admin.php?page=create-blog\">requests page</a>.</p>';
        document.getElementById('validate-writer-widget-" . $widget_id . "').outerHTML = div.outerHTML;
        var processingMessage = document.createElement('div');
        processingMessage.innerHTML = '<div id=\"billy-responding\" class=\"processing-message\">Processing<span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span></div>';
        // in chatbar there's no billy chatbox
        try {
            document.getElementById('billy-chatbox').appendChild(processingMessage);
        } catch (error) {
            console.error('No chatbox to append processing');
        }
        sendMessage(data, 'writer', '" . $run_id . "', '" . $tool_call_id . "');
    });
    document.getElementById('reject-writer-" . $widget_id . "').addEventListener('click', function() {
        console.log('reject writer');
        var data = {'authorize': false};
        let div = document.createElement('div');
        div.classList.add('writer-bubble');
        div.classList.add('bubble');
        div.style.backgroundColor = '#fffaf4';
        div.style.alignSelf = 'flex-start';
        div.innerHTML = '<p>You rejected to build blog posts for: <b>" . $keyphrase . "</b></p>';
        document.getElementById('validate-writer-widget-" . $widget_id . "').outerHTML = div.outerHTML;
        var processingMessage = document.createElement('div');
        processingMessage.innerHTML = '<div id=\"billy-responding\" class=\"processing-message\">Processing<span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span></div>';
        try {
            document.getElementById('billy-chatbox').appendChild(processingMessage);
        } catch (error) {
            console.error('No chatbox to append processing');
        }
        sendMessage(data, 'writer', '" . $run_id . "', '" . $tool_call_id . "');
    });
    ";
    
    // return a dictionary with  both widget and script
    return array('widget' => $widget, 'script' => $script);
}