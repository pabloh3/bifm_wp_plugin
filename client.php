    <?php
    // Extract the 'client_folder' parameter from the URL
    $folderName = isset($_GET['foldername']) ? sanitize_text_field($_GET['foldername']) : '';
    $client_folder = preg_replace("/[a-zA-Z]/", "", $folderName); // Remove any letters
    require 'bifm-config.php';
    $url = $API_URL ."/instance/". esc_attr($folderName) . "/widget.php";
    // for testing
    
    echo '<button id="backButton" class="btn waves-effect waves-light red lighten-2";"><i class="material-icons left">arrow_back</i>Back</button>';
    
    echo "
    <!doctype html>
    <html lang='en'>
    <head>
        <meta charset='utf-8'>
        <!-- Materialize CSS -->
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css'>
        <link href='https://fonts.googleapis.com/icon?family=Material+Icons' rel='stylesheet'>
        <title>Main App</title>
    </head>
    <body>
        <div class='container client'>
            <div class='chat-column'>
                <div id='chatbox'></div>
                <form id='my-form' method='POST' class='input-field'>
                    <textarea id='name' name='name' class='materialize-textarea' rows='4' cols='50' placeholder=' ' required></textarea>
                    <label for='name'>Enter your instructions:</label>
                    <button class='btn waves-effect waves-light' type='submit' id='submit_chat'>Submit</button>
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
                    <button class='btn waves-effect waves-light' id='reset-button'>Reset</button>
                    <button class='btn waves-effect waves-light' id='undo-button'>Undo</button>
                </div>
                <div class='aspect-16-9'>
                    <iframe id='myframe' src='{$url}' frameborder='0' allowfullscreen></iframe>
                    
                </div>
                
                <button class='btn waves-effect waves-light' id='save-button'>Save</button>
                <button class='btn waves-effect waves-light' id='previous-stage' style='display: none;'>< Back (modify visuals)</button>
                <button class='btn waves-effect waves-light' id='next-stage' >Next (modify controls) ></button>
            </div>
        </div>
        
        <!-- Materialize JavaScript -->
        <script src='https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js'></script>
    </body>
    </html>
    ";
