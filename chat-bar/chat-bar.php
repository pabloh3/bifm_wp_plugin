<?php
if ( ! defined( 'ABSPATH' ) ) exit;
function bifm_billy_add_admin_bar_button($wp_admin_bar) {
    $args = array(
        'id'    => 'billy-chat-button',
        'title' => '<img src="' . plugin_dir_url(__FILE__) . '../static/icons/bar-logo-full.png" style="height: 20px; margin: 6px;" />',
        'href'  => '/wp-admin/admin.php?page=bifm',
        'meta'  => array(
            'class' => 'billy-chat-button',
            'title' => __('Ask Billy','bifm')
        )
    );
    $wp_admin_bar->add_node($args);
}
add_action('admin_bar_menu', 'bifm_billy_add_admin_bar_button', 999);

function bifm_billy_button_enqueue_scripts() {
    wp_enqueue_script('highlightjs',BIFM_URL.'static/highlightjs/highlight.min.js',['jquery'],BIFM_VERSION,true);
    wp_enqueue_script('markdownit',BIFM_URL.'static/markdownit/markdown-it.min.js',['jquery'],BIFM_VERSION,true);
    wp_enqueue_script('materialize',BIFM_URL.'static/materialize/materialize.min.js',['jquery'],BIFM_VERSION,true);
    wp_enqueue_script('billy_chat_script', plugin_dir_url(__FILE__) . 'chat-bar.js', array('jquery','markdownit'), BIFM_VERSION, true);
    wp_enqueue_style('billy_chat_style', plugin_dir_url(__FILE__)  . '/chat-bar.css',[],BIFM_VERSION);
    // localize
    wp_localize_script('billy_chat_script', 'billy_chat_vars', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('billy-nonce')
    ));
}
add_action('admin_enqueue_scripts', 'bifm_billy_button_enqueue_scripts');

function bifm_billy_add_chat_widget() {
    ?>
    <div id="billy_chat_widget" class="collapsed" style="display:none;">
        <!-- Chat widget HTML here -->
        <div id="billy_chat_container">
            <div class="billy_top_bar">
                <!-- show AI.svg -->
                <?php esc_html_e('Billy','bifm'); ?> <img src="<?php echo esc_url(plugin_dir_url(__FILE__)) . '../static/icons/AI.svg'; ?>" style="height: 20px; margin: 6px;" />
                <!-- three round buttons aligned to the right -->
                <div class="billy_top_bar_buttons">
                    <div class="billy_top_bar_button" id="billy_chat_close">
                        <img src="<?php echo esc_url(plugin_dir_url(__FILE__)) . '../static/icons/Side-menu.svg'; ?>" style="height: 20px; margin: 6px;" />
                    </div>
                    <div class="billy_top_bar_button" id="billy_chat_minimize">
                        <img src="<?php echo esc_url(plugin_dir_url(__FILE__)) . '../static/icons/Minimize.svg'; ?>" style="height: 20px; margin: 6px;" />
                    </div>
                </div>
            </div>
            <div id="billy_chat_messages"></div>
            <div id="billy_chat_input_container">
                <textarea type="text" id="billy_chat_input" placeholder="<?php esc_html_e('Ask Billy for any assistance you need','bifm'); ?>"></textarea>
                <button id="billy_chat_send">
                <img src="<?php echo esc_url(plugin_dir_url(__FILE__)) . '../static/icons/Send.svg'; ?>" alt="send" />
                </button>
            </div>
        </div>
    </div>
    
    <?php
}
add_action('admin_footer', 'bifm_billy_add_chat_widget');
