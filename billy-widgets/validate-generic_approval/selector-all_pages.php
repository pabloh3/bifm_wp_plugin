<?php
function bifm_get_selector_html($parameters) {
    // Fetch all pages and posts
    $pages_and_posts = array_merge(
        get_posts(array('post_type' => 'page', 'posts_per_page' => -1, 'post_status' => array('publish', 'draft'))),
        get_posts(array('post_type' => 'post', 'posts_per_page' => -1, 'post_status' => array('publish', 'draft')))
    );

    // Extract the IDs of all pages and posts
    $pages_and_posts_ids = array_map(function($item) {
        return $item->ID;
    }, $pages_and_posts);

    // Check if parameters include a page, and validate it, check if it's in the pages and posts list
    $pre_selected_page = isset($parameters['page']) && is_int($parameters['page']) && in_array($parameters['page'], $pages_and_posts_ids) ? $parameters['page'] : '';

    // Start building the HTML for the selector
    ob_start(); // Start output buffering
    ?>
    <div class="input-field bifm-col s12 l12" style="width: 100%; padding: 5px;">
        <select id="bifm_generic_approval_selector" name="page" class="materialize-select" required>
            <?php if (empty($pre_selected_page)) : ?>
                <option value="" disabled selected><?php esc_html_e('Select the page you want to modify', 'bifm'); ?></option>
            <?php endif; ?>
            <?php foreach ($pages_and_posts as $item) : ?>
                <option value="<?php echo esc_attr($item->ID); ?>" <?php selected($item->ID, $pre_selected_page); ?>>
                    <?php echo esc_html($item->ID . ' - ' . $item->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="bifm_generic_approval_selector"><?php esc_html_e('Select the page to be modified', 'bifm'); ?></label>
    </div>
    <?php
    return ob_get_clean(); // Return the buffered HTML
}
