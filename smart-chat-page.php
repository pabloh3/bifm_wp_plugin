<?php
// Materialize CSS and Icons
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">';
echo '<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">';
echo '<button id="backButton" class="btn waves-effect waves-light red lighten-2"><i class="material-icons left">arrow_back</i>Back</button>';
echo '<div class="container">';  // using Materialize's container for alignment and spacing
echo '<div id="smart-chat" class="col s12">';
echo '<h5>Smart chat</h5>';
// Add settings content here...
echo '<p>Here you can update the settings for smart chat.</p>';
echo '<p>Please note that the smart chat incurrs in extra costs ($0.05 per request).</p><br/>';

// Start of the form
echo '<form id="smart-chat-form" action="#" method="post">';

// Bot description
$assistant_instructions = get_option( 'assistant_instructions' );
echo '<div class="row"><div class="input-field col s12 l8">';
if (!is_null($assistant_instructions)) {
    $assistant_instructions_unescaped = stripslashes($assistant_instructions);
    echo '<textarea id="assistant_instructions" name="assistant_instructions" class="materialize-textarea">' . $assistant_instructions_unescaped . '</textarea>';
} else {
    echo '<textarea id="assistant_instructions" name="assistant_instructions" class="materialize-textarea"></textarea>';
}
echo '<label for="assistant_instructions">Give the bot instructions for how to respond. Start with something like "You are a support representative that...".</label>';
echo '</div></div>';

// File upload section
echo '<H5>Define the files that will be used to answer chat questions.</H5>';
echo '<div>You can upload up to 5 files. The maximum file size is 512 MB and no more than 2 million tokens (approx 1.5M words).</div>';
echo '<div>Accepted file types: .c, .cpp, .docx, .html, .java, .json, .md, .pdf, .php, .pptx, .py, .rb, .tex, .txt</div>';
echo '<div class="row">';
echo '<div class="file-field input-field col s12 l6">';
echo '<div  class="btn waves-effect waves-light red lighten-2" red>';
echo '  <i class="material-icons left">cloud_upload</i>'; // Icon added here
echo '  <span>Upload Files</span>';
echo '  <input type="file" id="fileUpload" name="files[]" multiple accept=".c, .cpp, application/vnd.openxmlformats-officedocument.wordprocessingml.document, .html, .java, application/json, .md, application/pdf, .php, application/vnd.openxmlformats-officedocument.presentationml.presentation, .py, .rb, .tex, .txt">';
echo '</div>';
echo '<div class="file-path-wrapper">';
echo '<input class="file-path validate" type="text" style="width:300px; border-bottom: 0;">';
echo '</div>';
echo '</div>';
echo '</div>';

// Display uploaded files section
echo '<div id="uploadedFilesSection" class="col l6">';
echo    '<h5>Uploaded files</h5>';
echo    '<div id="uploadedFiles" class ="l6">';
echo    '<ul class="collection">';

// Directory path
$dirPath = plugin_dir_path(__FILE__) . 'shared-widgets/smart_chat/chat_files/';

// Check if directory exists and is readable
if (is_dir($dirPath) && is_readable($dirPath)) {
    // Open the directory
    if ($dh = opendir($dirPath)) {
        // Read files from the directory
        while (($file = readdir($dh)) !== false) {
            if ($file != "." && $file != "..") { // Exclude current and parent directory links
                echo '<li class="collection-item">';
                //in the div include the file name as an attribute
                echo '<div class="file-name-line" file-name="'. htmlspecialchars($file) .'">';
                echo htmlspecialchars($file); // Display file name
                echo '  <a href="#!" class="secondary-content">';
                echo '      <i class="material-icons" onclick="removeFile(this, \''. htmlspecialchars($file) .'\')">delete</i>';
                echo '  </a>';
                echo '</div>';
                echo '</li>';
            }
        }
        closedir($dh);
    }
} else {
    echo '<li class="collection-item">Directory not found or not readable</li>';
}

echo '</ul>';
echo '</div>';


// Submit button
echo '<button class="btn waves-effect waves-light" type="submit" name="action" >Save Changes</button>';

echo '</form>';
echo '</div>'; // Close the settings tab
echo '</div>'; // Close the container

echo '<div id="warningMessage" class="card-panel yellow darken-2" style="display: none;"></div>';

echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>';