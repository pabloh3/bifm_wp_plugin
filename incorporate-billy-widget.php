<?php
// define function incorporate_widget that takes in a file's name and returns the file's contents
/*function incorporate_billy_widget($file_name) {
    // strip the file's extension
    $folder_name = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file_name);
    // check if the file exists
    $path = plugin_dir_path(__FILE__) . 'billy-widgets/'. $folder_name . '/' . $file_name;
    if (file_exists($path)) {
        // return the file's contents
        return file_get_contents($path);
    } else {
        // return an error message
        return "File not found";
    }
}*/