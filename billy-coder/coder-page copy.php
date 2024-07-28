<?php
// Extract the 'client_folder' parameter from the URL
//echo "Get: ", var_dump($_GET); for debugging
$folderName = isset($_GET['foldername']) ? sanitize_text_field($_GET['foldername']) : '';
$client_folder = preg_replace("/[a-zA-Z]/", "", $folderName); // Remove any letters
require ( __DIR__ . '/../bifm-config.php' );// define base url for the API
$url = $WIDGET_URL . esc_attr($folderName) . "/widget.php "; 
//localhost:3013/<foldername>/widget.php
// for testing
?>

<div id="backButton" class="icon-button btn-floating btn-small waves-effect waves-light non-menu-back-button">
    <i class="arrow-left material-icons">arrow_back</i>    
</div>

<head>
    <meta charset='utf-8'>
    <link href='https://fonts.googleapis.com/icon?family=Material+Icons' rel='stylesheet'>
    <title>Main App</title>
</head>
<body>
    <div class='container client'>
        <div class='chat-column'>
            <div id='chatbox'></div>
            <form id='my-form' method='POST' class='input-field'>
                <textarea id='user_message' name='user_message' class='materialize-textarea' rows='4' cols='50' placeholder=' ' required></textarea>
                <label for='user_message'>Enter your instructions:</label>
                <button class='bifm-btn waves-effect waves-light' type='submit' id='submit_chat'>Submit</button>
            </form>
        </div>
        <div class='future-column'>   
            <div class='title-container'>
                <h4>builditforme.ai</h4>
                <div style='color:red'>You should ONLY use this tool on your staging site!</div>
                <div data-stage='visual' id='stageDisplay'>Modify how your widget looks.</div>
                <div id='warningMessage' class='card-panel yellow darken-2' style='display: none;'></div>
            </div>  
            <div class='button-container'>
                <button class='bifm-btn waves-effect waves-light' id='reset-button'>Reset</button>
                <button class='bifm-btn waves-effect waves-light' id='undo-button'>Undo</button>
            </div>
            <div class='aspect-16-9' style="margin: 10px;">
                <iframe id='myframe' src=<?php echo $url; ?>frameborder='0' allowfullscreen></iframe>
            </div>
            <div id="coder-buttons">
                <button class='bifm-btn waves-effect waves-light' id='previous-stage' style='display: none;'>< Back (modify visuals)</button>
                <button class='bifm-btn waves-effect waves-light' id='next-stage' >Next (modify controls) ></button>
                <button class='bifm-btn waves-effect waves-light' id='save-button' style="margin-left: auto;">Save</button>
            </div>
        </div>
    </div>
    
    <!-- Materialize JavaScript -->
    <script src='https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/markdown-it/dist/markdown-it.min.js'></script>
</body>
</html>
