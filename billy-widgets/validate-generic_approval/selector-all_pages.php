<?php
// Fetch all pages
$args = array(
    'post_type' => 'page',
    'posts_per_page' => -1, // Get all pages
);
$pages = get_posts($args);
// fetch all posts
$args = array(
    'post_type' => 'post',
    'posts_per_page' => -1, // Get all posts
);
$posts = get_posts($args);

?>
<!-- echo a drop down selector with the pages, and passing page id as metadata -->
<div class="input-field bifm-col s12 l12" style="width: 100%; padding: 5px;">
    <select id="bifm_generic_approval_selector" name="page" class="materialize-select" required>
        <option value='' disabled selected>Select the page you want to modify</option>
        <?php foreach ($pages as $page) : ?>
            <option value="<?php echo esc_attr($page->ID) ?>"><?php echo esc_attr($page->ID) . " - " .esc_attr($page->post_title) ?></option>
        <?php endforeach; ?>
        <?php foreach ($posts as $post) : ?>
            <option value="<?php echo esc_attr($post->ID) ?>"><?php echo esc_attr($post->ID) . " - " .esc_attr($post->post_title) ?></option>
        <?php endforeach; ?>
    </select>
    <label for="pages"><?php esc_html_e('Select the page to be modified','bifm'); ?></label>   
</div>

