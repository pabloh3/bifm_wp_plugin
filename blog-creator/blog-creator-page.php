<?php 
if ( ! defined( 'ABSPATH' ) ) exit;
require ( __DIR__ . '/../bifm-config.php' );// define base url for the API 
// reset thread_id from user session
?>
 
<body>
<div class="plugin-page">
    <div id="backButton" class="icon-button btn-floating btn-small waves-effect waves-light non-menu-back-button">
        <i class="arrow-left material-icons">arrow_back</i>    
    </div>
    <!-- Body outside of menu -->
    <div class="container">
        <div class="writer-content">
            <div class="header-bot">
                <div class="svg-icon writer-icon">
                    <?php include(plugin_dir_path(__FILE__) . '../static/icons/Writer.svg'); ?>
                </div>
                <div class="bot-title"><?php esc_html_e('Writer Bot','bifm'); ?></div>
            </div>

            <div id="suggestion-buttons" class="bifm-row suggestions">
                <!-- First Column -->
                <div class="bifm-col s6">
                    <button class="bifm-btn waves-effect waves-light suggestion-button transparent"><?php esc_html_e('Architecture ideas for tiny houses','bifm'); ?></button>
                    <button class="bifm-btn waves-effect waves-light suggestion-button transparent"><?php esc_html_e('How to make the most of credit card rewards','bifm'); ?></button>
                </div>
                <!-- Second Column -->
                <div class="bifm-col s6">
                    <button class="bifm-btn waves-effect waves-light suggestion-button transparent"><?php esc_html_e('What to do in Mexico City','bifm'); ?></button>
                    <button class="bifm-btn waves-effect waves-light suggestion-button transparent"><?php esc_html_e('Manufacturing trends 2024','bifm'); ?></button>
                </div>

            </div>
            <div class='writer-input'>
                <div class="input-field bifm-col s12">
                    <input id="description" type="text" class="writer-input-box validate">
                    <label for="description"><?php esc_html_e('Keyphrase (online search term) you\'d like to capture','bifm'); ?></label>
                    <select id="category_input" class="browser-default category-dropdown">
                        <option value="1" disabled selected><?php esc_html_e('Category','bifm'); ?></option>
                    </select>
                </div>
            </div>
            <div id="writer-buttons">
                <a class="add-more">
                    <?php esc_html_e('Add more','bifm'); ?> <i class="material-icons">add</i>
                </a> 
            </div>
            <div id="generate-blogposts">
                <button id="generate-blogposts-button" class="waves-effect waves-light btn-large generate-blogposts purple"><?php esc_html_e('Generate Blogposts','bifm'); ?>
                    <div class="svg-icon">
                        <?php include(plugin_dir_path(__FILE__) . '../static/icons/Writer.svg'); ?>
                    </div>
                </button>
            </div>
            <div id ="billy-form-footer" class="grey-text">
                <?php esc_html_e('Go to &#8203;', 'text-domain'); /* translators: %s: writer settings page URL */?> 
                <a href="admin.php?page=create-chat" class="black-text"><?php esc_html_e('Writer Settings', 'text-domain'); ?></a>
                <?php esc_html_e('&#8203; to change the tone, language and image style.', 'text-domain'); ?>
            <div id="cbc_response" class="card-panel grey" style="display:none;"></div>
        </div>
    


        <!-- table for displaying blogposts -->
        <?php
        global $wpdb;
        $table_name = $wpdb->prefix . 'cbc_blog_requests';
        $requests = $wpdb->get_results( "SELECT * FROM ".$table_name." ORDER BY requested_at DESC"); //phpcs:ignore
        ?>
        <br/>
        <div id="posts-table-div">
            <h6><?php esc_html_e('Recently generated','bifm'); ?></h6>
            <table id="posts-table" class="highlight">
                <tbody>
                    <?php
                    // for each request, get the post id and status, if none, then display the keyphrase
                    foreach ($requests as $request) {
                        // phpcs:disable
                        $post = get_posts(array(
                            'meta_key' => 'bifm_uuid',
                            'meta_value' => $request->uuid,
                            'post_type' => 'post',
                            'post_status' => 'any',
                            'numberposts' => 1
                        ));
                        // phpcs:enable
                        if ($post) {
                            $post_id = $post[0]->ID;
                            $title = $post[0]->post_title ? $post[0]->post_title : NULL;
                            $status = get_post_status($post_id);
                            $status_text = $status ? ucfirst($status) : 'Undefined';
                            $title_link = get_permalink($post_id);
                        } else {
                            // if requested_at > 20 mins ago, status is "Request failed"
                            $request_time = strtotime($request->requested_at);
                            $current_time = time();
                            $time_diff = $current_time - $request_time;
                            // count how many requests were made at the same time as this one
                            //phpcs:ignore
                            $same_time_requests = $wpdb->get_results(
                                $wpdb->prepare("SELECT * FROM ".$table_name." WHERE requested_at = %s",$request->requested_at) // phpcs:ignore
                            );
                            $count_same_time_requests = count($same_time_requests);
                            if ($time_diff > 600 && $count_same_time_requests < 5) {
                                # don't fail batches of requests that are less than 10 minutes old
                                $status_text = __('Request failed','bifm');
                            } else if ($time_diff > 12000) {
                                # fail requests that are older than 3 hours regardless of how many requests were made at the same time
                                $status_text =  __('Request failed','bifm');
                            } else {
                                $status_text = __('Writing in progress...','bifm'); 
                            }
                            $post_id = false;
                            $title = false;
                            $title_link = '#';
                        }
                        ?>
                        <tr>
                            <td>
                                <!-- display title, if no title, show keyphrase -->
                                <div class = "post-title">
                                    <?php echo isset($request->title) ? esc_html($request->title) : esc_html($request->keyphrase); ?>
                                </div>
                                <div class="post-author"><?php echo esc_html($request->requester); ?> - <?php echo esc_html($request->requested_at); ?></div>
                            </td>
                            <td><?php echo esc_html($status_text); ?></td>
                            <td>
                                <!-- add uuid as part of data -->
                                <a href="#" id="<?php echo 'delete_' . esc_attr($post_id); ?>" class="waves-effect billy-button tooltipped delete-post" data-tooltip="<?php esc_html_e('Delete','bifm'); ?>" data-post-id="<?php echo esc_attr($post_id); ?>" data-uuid="<?php echo esc_attr($request->uuid); ?>">
                                    <div class="svg-icon inline-icon">
                                        <?php include(plugin_dir_path(__FILE__) . '../static/icons/Trash.svg'); ?>
                                    </div>
                                </a>
                                <!-- only display the view button if the post has a post id else, display the hourglass -->
                                <?php if ($post_id) { ?>
                                    <a href="<?php echo esc_url($title_link); ?>" class="waves-effect billy-button tooltipped" data-tooltip="<?php esc_html_e('View','bifm'); ?>">    
                                        <?php include(plugin_dir_path(__FILE__) . '../static/icons/Glasses.svg'); ?>
                                    </a>
                                <?php } else { ?>
                                    <a class="waves-effect billy-button tooltipped disabled" data-tooltip="<?php esc_html_e('Not ready','bifm'); ?>">    
                                        <i class="material-icons">hourglass_empty</i>
                                    </a>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>


    <!-- end of table -->

    </div>
    
</div>
 

