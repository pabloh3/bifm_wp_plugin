<?php
// This file tests my design system

// Materialize CSS and Icons
echo '<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">';
echo '<button id="backButton" class="btn waves-effect waves-light red lighten-2"><i class="material-icons left">arrow_back</i>Back</button>';
echo '<div class="container">';  // using Materialize's container for alignment and spacing
echo '<div id="smart-chat" class="col s12">';
echo '<h1>This is an h1</h1>';
echo '<h2>This is an h2</h2>';
echo '<h3>This is an h3</h3>';
echo '<h4>This is an h4</h4>';
echo '<h5>This is an h5</h5>';
echo '<h6>This is an h6</h6>';
echo '<p>This is a paragraph</p>';
echo 'This is just text<br/>';
echo '<i>This is italic text</i><br/>';
echo '<b>This is bold text</b><br/>';
echo '<u>This is underlined text</u><br/>';
echo '<a href="#">This is a link</a><br/>';

// Display all the colors using materialize items
$colors = [
    'primary-color' => 'blue',
    'secondary-color' => 'blue',
    'accent-color' => '#9c27b0',
    'primary-light' => '#ff7961',
    'primary-dark' => '#ba000d',
    'secondary-light' => '#ff6090',
    'secondary-dark' => '#b0003a',
    'background-color' => '#f5f5f5',
    'surface-color' => '#ffffff',
    'error-color' => '#d32f2f',
    'on-primary' => '#ffffff',
    'on-secondary' => '#000000',
    'on-background' => '#000000',
    'on-surface' => '#000000',
    'on-error' => '#ffffff'
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
echo '</div>'; // Close the settings tab
echo '</div>'; // Close the container


// Input fields
echo '<h5>Inputs</h5>';
// Start of the form
echo '<form id="smart-chat-form" action="#" method="post">';

// Text input
echo '<div class="row">';
echo '<div class="input-field large col s12 l8">';
echo '<textarea id="assistant_instructions" name="assistant_instructions" class="materialize-textarea"></textarea>';
echo '<label for="text_input">Text Input</label>';
echo '</div>';
echo '</div>';

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
echo '<div class="card">';
echo '<div class="card-content">';
echo '<span class="card-title">Card Title</span>';
echo '<p>This is a card content. It can hold text, images, and more.</p>';
echo '</div>';
echo '<div class="card-action">';
echo '<a href="#">This is a link</a>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '<div>';
echo '<div id="warningMessage" class="card-panel yellow darken-2" style="display: inline;">This is a warning message.</div>';
echo '</div>';


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

echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>';
?>
