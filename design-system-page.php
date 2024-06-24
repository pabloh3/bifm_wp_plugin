<?php
// This file tests my design system
//sidenav menu
echo '<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">';
echo '<div class="row">';
echo '  <div class="plugin-menu">';
echo '    <ul id="slide-out" class="sidenav sidenav-fixed browser-default">';
echo '      <li>';
echo '        <div class="user-view">';
echo '          <div class="form-icon-button btn-floating btn-small waves-effect waves-light back-button">';
echo '            <i class="arrow-left material-icons">arrow_back</i>';
echo '          </div>';
echo '        </div>';
echo '      </li>';
echo '      <li><a href="admin.php?page=smart-chat" class="waves-effect"><i class="material-icons">chat</i>Smart Chat</a></li>';
echo '      <li><a href="admin.php?page=blog-generator" class="waves-effect"><i class="material-icons">edit</i>Blog Generator</a></li>';
echo '      <li><a href="admin.php?page=widget-generator" class="waves-effect"><i class="material-icons">widgets</i>Widget Generator</a></li>';
echo '      <li><a href="admin.php?page=settings" class="waves-effect"><i class="material-icons">settings</i>Settings</a></li>';
echo '    </ul>';
echo '  </div>';

// Materialize CSS and Icons
echo '  <div class="plugin-content">';
echo '    <button id="backButton" class="btn waves-effect waves-light red lighten-2"><i class="material-icons left">arrow_back</i>Back</button>';
echo '    <div class="container">';  // using Materialize's container for alignment and spacing
echo '      <div id="smart-chat" class="col s12">';
echo '        <h1>This is an h1</h1>';
echo '        <h2>This is an h2</h2>';
echo '        <h3>This is an h3</h3>';
echo '        <h4>This is an h4</h4>';
echo '        <h5>This is an h5</h5>';
echo '        <h6>This is an h6</h6>';
echo '        <p>This is a paragraph</p>';
echo '        This is just text<br/>';
echo '        <i>This is italic text</i><br/>';
echo '        <b>This is bold text</b><br/>';
echo '        <u>This is underlined text</u><br/>';
echo '        <a href="#">This is a link</a><br/>';
echo '      </div>';
echo '    </div>';

// Display all the colors using materialize items
$colors = [
    'secondary-color or blue' => color("blue", "base"),
    'secondary-light-purple' => color("purple", "secondary-light-purple"),
    'primary-light' => color("red", "lighten-4"),
    'primary-dark' => color("red", "darken-4"),
    'secondary-light' => color("pink", "lighten-4"),
    'secondary-dark' => color("pink", "darken-4"),
    'background-color' => color("grey", "lighten-5"),
    'surface-color' => color("white", "base"),
    'error-color' => color("red", "base"),
    'on-primary' => color("white", "base"),
    'on-secondary' => color("black", "base"),
    'on-background' => color("black", "base"),
    'on-surface' => color("black", "base"),
    'on-error' => color("white", "base"),
    // New colors based on your provided variables
    'purple-lighten-5' => color("purple", "lighten-5"),
    'dark-purple' => color("purple", "dark-purple"),
    'gray-purple' => color("purple", "gray-purple"),
    'main-Purple' => color("purple", "main-Purple"),
    'light-purple' => color("purple", "light-purple"),
    'light-grey' => color("grey", "light-grey"),
    'off-white' => color("grey", "off-white"),
];

echo '<div class="row">';
foreach ($colors as $colorName => $class) {
    
    echo '<div class="col s12 m6 l4">';
    echo '<div class="card">';
    echo '<span class="card-title">' . ucfirst($colorName) . '</span>';
    echo '<p>' . $class . '</p>';
    echo '<div class="card-content ' .$class. ' ">';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
echo '</div>';

// Buttons
echo '<div class="row">';
echo '<div class="file-field col s12 l6">';
echo '<div class="btn waves-effect waves-light blue lighten-2">';
echo '  <i class="material-icons left">cloud_upload</i>'; // Icon added here
echo '  <span>Upload Files</span>';
echo '  <input type="file" id="fileUpload" name="files[]" multiple accept=".c, .cpp, application/vnd.openxmlformats-officedocument.wordprocessingml.document, .html, .java, application/json, .md, application/pdf, .php, application/vnd.openxmlformats-officedocument.presentationml.presentation, .py, .rb, .tex, .txt">';
echo '</div>';
echo '</div>';
echo '</div>';
// Submit button
echo '<button class="btn waves-effect waves-light" type="submit" name="action" >Save Changes</button>';
echo '<button class="btn waves-effect waves-light red lighten-2" type="submit" name="action" id="reset_chat">Reset chatbot</button>';


// Input fields
echo '<h5>Inputs</h5>';
// Start of the form
echo '<form id="smart-chat-form" action="#" method="post">';

// Text input
echo '<div class="row">';
echo '<div class="input-field large col s12 l8">';
echo '<textarea id="assistant_instructions" name="assistant_instructions" class="materialize-textarea"></textarea>';
echo '<button class="btn-floating btn-small waves-effect waves-light blue send-input" type="submit">';
echo '  <i class="material-icons">send</i>';
echo '</button>';
echo '<label for="text_input">Text Input</label>';
echo '</div>';
echo '</div>';

// text input with button
echo '<div class="input-field">';
echo '    <input id="search" type="text" class="validate" placeholder="Ask Billy for any assistance you need">';
echo '    <button class="btn-floating btn-small waves-effect waves-light blue send-input" type="submit">';
echo '        <i class="material-icons">send</i>';
echo '    </button>';
echo '</div>';


// Example of select input with label and placeholder
echo '<div class="row">';
echo '<div class="input-field col s12 l8">';
echo '<select id="select_input_1" name="select_input_1">';
echo '<option value="" disabled selected>Placeholder</option>';
echo '<option value="1">Option 1</option>';
echo '<option value="2">Option 2</option>';
echo '<option value="3">Option 3</option>';
echo '</select>';
echo '<label for="select_input_1">Label</label>';
echo '</div>';
echo '</div>';

// Initialize Materialize select
echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        var elems = document.querySelectorAll("select");
        var instances = M.FormSelect.init(elems);
    });
</script>';


// Example of text area with label and placeholder
echo '<div class="row">';
echo '<div class="input-field col s12 l8">';
echo '<textarea id="textarea_input_1" name="textarea_input_1" class="materialize-textarea" placeholder="Ask Billy for any assistance you need"></textarea>';
echo '<label for="textarea_input_1">Label</label>';
echo '</div>';
echo '</div>';

echo '</form>';


// Email input
echo '<div class="row">';
echo '<div class="input-field col s12 l8">';
echo '<input id="email_input" type="email" name="email_input">';
echo '<label for="email_input">Email Input</label>';
echo '</div>';
echo '</div>';

// Password input
echo '<div class="row">';
echo '<div class="input-field col s12 l8">';
echo '<input id="password_input" type="password" name="password_input">';
echo '<label for="password_input">Password Input</label>';
echo '</div>';
echo '</div>';

// Date input
echo '<div class="row">';
echo '<div class="input-field col s12 l8">';
echo '<input id="date_input" type="text" class="datepicker" name="date_input">';
echo '<label for="date_input">Date Input</label>';
echo '</div>';
echo '</div>';

// Time input
echo '<div class="row">';
echo '<div class="input-field col s12 l8">';
echo '<input id="time_input" type="text" class="timepicker" name="time_input">';
echo '<label for "time_input">Time Input</label>';
echo '</div>';
echo '</div>';

// Select input
echo '<div class="row">';
echo '<div class="input-field col s12 l8">';
echo '<select id="select_input" name="select_input">';
echo '<option value="" disabled selected>Choose your option</option>';
echo '<option value="1">Option 1</option>';
echo '<option value="2">Option 2</option>';
echo '<option value="3">Option 3</option>';
echo '</select>';
echo '<label for="select_input">Select Input</label>';
echo '</div>';
echo '</div>';

// Checkbox
echo '<div class="row">';
echo '<div class="input-field col s12 l8">';
echo '<label>';
echo '<input type="checkbox" id="checkbox_input" name="checkbox_input" />';
echo '<span>Checkbox Input</span>';
echo '</label>';
echo '</div>';
echo '</div>';

// Radio buttons
echo '<div class="row">';
echo '<div class="input-field col s12 l8">';
echo '<p>';
echo '<label>';
echo '<input name="radio_group" type="radio" id="radio1" value="1" />';
echo '<span>Radio Option 1</span>';
echo '</label>';
echo '</p>';
echo '<p>';
echo '<label>';
echo '<input name="radio_group" type="radio" id="radio2" value="2" />';
echo '<span>Radio Option 2</span>';
echo '</label>';
echo '</p>';
echo '</div>';
echo '</div>';

// Switch
echo '<div class="row">';
echo '<div class="input-field col s12 l8">';
echo '<div class="switch">';
echo '<label>';
echo 'Off';
echo '<input type="checkbox" id="switch_input" name="switch_input">';
echo '<span class="lever"></span>';
echo 'On';
echo '</label>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '</form>';


// Cards
echo '<h5>Cards</h5>';
echo '<div class="row">';
    echo '<div class="col s12 m6 l4">';
        echo '<div class="card tool-s-chat-bubble writer-bot">';
            echo '<div class="card-content frame-10120667">';
                echo '<div class="frame">';
                    echo '<div class="svg-icon writer-icon">' . file_get_contents(plugin_dir_path(__FILE__) . 'static/icons/Writer.svg') . '</div>';
                echo '</div>';
                echo '<span class="card-title">Writer bot</span>';
                echo '<p class="our-ai-powered-blog-post-generator-crafts-engaging-articles-efficiently-enhancing-your-blog-s-content-effortlessly">';
                    echo 'Our AI-powered blog post generator crafts engaging articles efficiently, enhancing your blog’s content effortlessly.';
                echo '</p>';
            echo '</div>';
            echo '<button class="btn waves-effect waves-light card-button writer-button" type="submit" name="action" >Go to the writer bot</button>';
        echo '</div>';
    echo '</div>';
echo '<div>';


echo '<div class="row">';
    echo '<div class="col s12 m6 l4">';
        echo '<div class="card tool-s-chat-bubble coder-bot">';
            echo '<div class="card-content frame-10120667">';
                echo '<div class="frame">';
                echo '<div class="svg-icon coder-icon">' . file_get_contents(plugin_dir_path(__FILE__) . 'static/icons/Coder.svg') . '</div>';
                echo '</div>';
                echo '<span class="card-title">Coder bot</span>';
                echo '<p class="our-ai-powered-blog-post-generator-crafts-engaging-articles-efficiently-enhancing-your-blog-s-content-effortlessly">';
                    echo 'Our AI-powered blog post generator crafts engaging articles efficiently, enhancing your blog’s content effortlessly.';
                echo '</p>';
            echo '</div>';
            echo '<button class="btn waves-effect waves-light card-button coder-button" type="submit" name="action" >Go to the writer bot</button>';
        echo '</div>';
    echo '</div>';
echo '<div>';

// Menus (Navbar)
echo '<h5>Navbar</h5>';
echo '<nav>';
echo '<div class="nav-wrapper">';
echo '<a href="#" class="brand-logo">Logo</a>';
echo '<ul id="nav-mobile" class="right hide-on-med-and-down">';
echo '<li><a href="#">Home</a></li>';
echo '<li><a href="#">About</a></li>';
echo '<li><a href="#">Contact</a></li>';
echo '</ul>';
echo '</div>';
echo '</nav>';

echo '</div>';
echo '</div>';

echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>';
?>
