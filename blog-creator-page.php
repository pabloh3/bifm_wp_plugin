
<?php

echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">';
echo '<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">';
echo '<button id="backButton" class="btn waves-effect waves-light red lighten-2"><i class="material-icons left">arrow_back</i>Back</button>';
echo '<div class="container">';  // Using Materialize's container for alignment and spacing

// Added Tabs for clear separation
echo '<ul id="tabs-swipe-demo" class="tabs">';
echo '    <li class="tab col s6"><a href="#test-swipe-1">Create Single Blogpost</a></li>';
echo '    <li class="tab col s6"><a href="#test-swipe-2">Create Blogposts in Bulk</a></li>';
echo '</ul>';

// Single creation
echo '<div id="test-swipe-1" class="col s12">'; // Tab container for single post creation
echo '    <h5>Create Single Blogpost</h5>';
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
echo '        <button type="submit"  class="btn waves-effect waves-light">Submit<i class="material-icons right">send</i></button>';  // Standardized button color and icon
echo '        </div>';
echo '    </form>';
echo '</div>';

// Bulk creation
echo '<div id="test-swipe-2" class="col s12">'; // Tab container for bulk post creation
echo '  <h5>Create Blogpost in Bulk</h5>';
echo '  <form id="cbc_csv_upload_form" method="post" enctype="multipart/form-data">';
echo '      <div class="row">';  // using Materialize's row for structuring the form
echo '          <div class="input-field col s12">';
echo '              <input type="file" name="cbc_csv_file" id="cbc_csv_file">';
echo '              <b>CSV Instructions: </b>Single column containing the keyphrases you are trying to target. No header.<br/>';
echo '              <label for="cbc_csv_file" class="active">CSV with keyphrases</label>';
echo '          </div>';
echo '          <div class="input-field col s12">';
echo '              <select name="category" id="category_input2">';
echo '                  <option value="" disabled selected>Choose a category</option>';
echo '              </select>';
echo '              <label for="category_input2">Category</label>';
echo '          </div>';
echo '              <button type="submit" class="btn waves-effect waves-light">Submit<i class="material-icons right">send</i></button>'; // Materialize button styling
echo '      </div>';
echo '   </form>';
echo '</div>';  // End of container

echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>';
echo '<div class="card-panel red lighten-2">';
echo '    <span class="white-text" id="cbc_response"></span>';
echo '</div>';


// Display old blog requests
global $wpdb;
$table_name = $wpdb->prefix . 'cbc_blog_requests';
$requests = $wpdb->get_results("SELECT * FROM $table_name ORDER BY requested_at DESC");

ob_start();
echo '<br/>';
echo '<h5>Old Blog Requests</h5>';
echo '<table class="striped">';
echo '<thead>';
echo '<tr>';
echo '<th>Post ID</th>';
echo '<th>Title</th>';
echo '<th>Keyphrase</th>';
echo '<th>Category</th>';
echo '<th>Created By</th>';
echo '<th>Created Date</th>';
echo '<th>Status</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

// error log a single post, and display the meta
$post = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'any',
    'numberposts' => 1
));
// print the post's meta
$meta = get_post_meta($post[0]->ID);
error_log(print_r($meta, true));

foreach ($requests as $request) {

    $post = get_posts(array(
        'meta_key' => 'bifm_uuid',
        'meta_value' => $request->uuid,
        'post_type' => 'post',
        'post_status' => 'any',
        'numberposts' => 1
    ));


    if ($post) {
        $post_id = $post[0]->ID;
        $title = $post[0]->post_title;
        $status = get_post_status($post_id);
        $status_text = $status ? ucfirst($status) : 'Not ready or deleted by user';
        $title_link = get_permalink($post_id);
    } else {
        $post_id = '';
        $title = '';
        $status_text = 'Not ready or deleted by user';
        $title_link = '#';
    }

    echo '<tr>';
    echo '<td>' . esc_html($post_id) . '</td>';
    echo '<td><a href="' . esc_url($title_link) . '">' . esc_html($title) . '</a></td>';
    echo '<td>' . esc_html($request->keyphrase) . '</td>';
    echo '<td>' . esc_html($request->category) . '</td>';
    echo '<td>' . esc_html($request->requester) . '</td>';
    echo '<td>' . esc_html($request->requested_at) . '</td>';
    echo '<td>' . esc_html($status_text) . '</td>';
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';

?>
