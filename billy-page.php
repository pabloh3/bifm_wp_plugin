<?php
    require 'bifm-config.php';
    $url = $WIDGET_URL . esc_attr($folderName) . "/widget.php ";
    
    echo "
    <div class='future-column'>
        <div id='billy-chatbox'></div>
        <form id='billy-form' method='POST' class='input-field'>
            <textarea id='name' name='name' class='materialize-textarea' rows='4' cols='50' placeholder='What would you like to change in your website?' required></textarea>
            <label for='name'>Ask Billy:</label>
            <button class='btn waves-effect waves-light' type='submit' id='billy-submit_chat'>Submit</button>
        </form>
    </div>
    <div class='future-column'>   
        <div class='button-container'>
            <button class='btn waves-effect waves-light' id='billy-reset-button' style='display:none'>Reset</button>
            <button class='btn waves-effect waves-light' id='billy-undo-button' style='display:none'>Undo</button>
        </div>        
    </div>
    ";
