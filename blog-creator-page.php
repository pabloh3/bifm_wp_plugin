<?php

echo '   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">';
echo '   <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">';
echo '<button id="backButton" class="btn waves-effect waves-light red lighten-2"><i class="material-icons left">arrow_back</i>Back</button>';
echo '<div class="container">';  // using Materialize's container for alignment and spacing
echo '    <h5>Create Blogpost</h5>';
echo '    <form id="cbc_form">';
echo '        <div class="row">';  // using Materialize's row for structuring the form
echo '            <div class="input-field col s12">';  // input-field class for Materialize styled inputs
echo '                <input type="text" name="keyphrase" id="keyphrase_input" required>';
echo '                <label for="keyphrase_input">Keyphrase</label>';  // label AFTER input for Materialize styling
echo '            </div>';
echo '            <div class="input-field col s12">';
echo '                <select name="category" id="category_input">';
echo '                    <option value="" disabled selected>Choose a category</option>';
echo '                </select>';
echo '                <label for="category_input">Category</label>';
echo '            </div>';
echo '            <button type="submit" class="btn waves-effect waves-light" id="submit_single_post">Submit<i class="material-icons right">send</i></button>';  // Materialize button styling
echo '        </div>';
echo '    </form>';


// Bulk creation
echo '  <h5>Create Blogpost in bulk</h5>';
echo '  <form id="cbc_csv_upload_form" method="post" enctype="multipart/form-data">';
echo '      <div class="row">';  // using Materialize's row for structuring the form
echo '            <div class="input-field col s12">';
echo '                <select name="category" id="category_input2">';
echo '                    <option value="" disabled selected>Choose a category</option>';
echo '          </select>';
echo '          <label for="category_input">Category</label>';
echo '        </div>';
echo '    </div>';
echo ' <div><b>CSV Instructions: </b>Single column containing the keyphrases you are trying to target. No header.<br/>';
echo '    <input type="file" name="cbc_csv_file" id="cbc_csv_file">';
echo '    <button type="submit" class="btn waves-effect waves-light">Submit</button>'; // Materialize button styling
echo '  </form>';
echo '</div>';
echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>';
echo '<div class="card-panel red lighten-2">';
echo '  <span class="white-text" id="cbc_response"></span>';
echo '</div>';

?>
