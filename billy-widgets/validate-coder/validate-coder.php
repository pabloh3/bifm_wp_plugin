<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// simple PHP file that returns hello world 
function bifm_get_widget($parameters, $run_id, $tool_call_id) {
    // create a random id for the widget
    $widget_id = uniqid();
    $keyphrase = $parameters['keyphrase'];
    // escape chars in keyphrase
    $keyphrase = htmlspecialchars($keyphrase);
    //make keyphrase a url
    $keyphrase_url = urlencode($keyphrase);
    $path = BIFM_PATH . 'static/icons/Coder.svg';
    $coder_icon = file_get_contents($path); //phpcs:ignore
    $acceptance_text = __("You authorized Billy to build a new Elementor widget.", "bifm");
    $rejection_text = __("You rejected Billy from building a new Elementor widget for: ", "bifm");
    // widget as a piece of html with two buttons (authorize and reject)
    $widget = "
    <div id='validate-coder-widget-" . $widget_id . "'>
        <div class='card tool-s-chat-bubble coder-bot'>
            <div class='card-content frame-10120667'>
                <div class='frame'>
                    <div class='svg-icon coder-icon'>" . $coder_icon . "
                    </div>
                </div>
                <span class='card-title'>".__("Billy suggests to create a new elementor widget. You can use the Billy Builder.","bifm")."</span>
                <p>".__("Do you want to authorize our Widget Builder bot to create a new widget:","bifm")." <b>" . $keyphrase . "</b>?</p>
            </div>
            <div style='display:flex;'>
                <button id='authorize-coder-" . $widget_id . "' class='bifm-btn waves-effect waves-light card-button coder-button'>".__("Let's go!","bifm")."</button>
                <button id='reject-coder-" . $widget_id . "' class='bifm-btn waves-effect waves-light card-button grey'>".__("Nope","bifm")."</button>
            </div>
        </div>
    </div>";
    
    $script = "
    document.getElementById('authorize-coder-" . $widget_id . "').addEventListener('click', function(event) {
        console.log('authorize coder');
        event.preventDefault();
        var data = {'authorize': true, 'keyphrase': '" . $keyphrase . "'};
        let div = document.createElement('div');
        div.classList.add('coder-bubble');
        div.classList.add('bubble');
        // make the bubble style backgound green
        div.style.backgroundColor = '#fef7ff';
        div.style.alignSelf = 'flex-start';
        div.innerHTML = '<p>" . $acceptance_text . " </p>';
        document.getElementById('validate-coder-widget-" . $widget_id . "').outerHTML = div.outerHTML;
        window.location.href = '/wp-admin/admin.php?page=widget-manager&keyphrase=" . $keyphrase_url . "';
    });
    document.getElementById('reject-coder-" . $widget_id . "').addEventListener('click', function() {
        console.log('reject coder');
        var data = {'authorize': false};
        let div = document.createElement('div');
        div.classList.add('coder-bubble');
        div.classList.add('bubble');
        div.style.backgroundColor = '#fef7ff';
        div.style.alignSelf = 'flex-start';
        div.innerHTML = '<p>" . $rejection_text ."<b>" . $keyphrase . "</b></p>';
        document.getElementById('validate-coder-widget-" . $widget_id . "').outerHTML = div.outerHTML;
        var processingMessage = document.createElement('div');
        processingMessage.innerHTML = '<div id=\"billy-responding\" class=\"processing-message\">Processing<span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span><span class=\"processing-dot\">.</span></div>';
        try {
            document.getElementById('billy-chatbox').appendChild(processingMessage);
        } catch (error) {
            console.error('No chatbox to append processing');
        }
        sendMessage(data, 'coder', '" . $run_id . "', '" . $tool_call_id . "');
    });
    ";
    
    // return a dictionary with  both widget and script
    return array('widget' => $widget, 'script' => $script);
}