<?php

$nonce = wp_create_nonce('my_custom_action');

// Materialize CSS and Icons
//echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">';
echo '<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">';

// enqueue billy-page.css
wp_enqueue_style('billy-page', plugin_dir_url(__FILE__) . 'static/billy-page.css');
include_once 'billy-page.php';

// Materialize JavaScript
echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>';


?>
    




