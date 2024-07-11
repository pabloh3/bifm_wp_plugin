<?php
function billy_add_admin_bar_button($wp_admin_bar) {
    $args = array(
        'id'    => 'billy-chat-button',
        'title' => '<img src="' . plugin_dir_url(__FILE__) . '../static/icons/bar-logo-full.png" style="height: 20px; margin: 6px;" />',
        'href'  => '#',
        'meta'  => array(
            'class' => 'billy-chat-button',
            'title' => 'Ask Billy'
        )
    );
    $wp_admin_bar->add_node($args);
}
add_action('admin_bar_menu', 'billy_add_admin_bar_button', 999);

function billy_button_enqueue_scripts() {
    wp_enqueue_script('billy_chat_script', plugin_dir_url(__FILE__) . '/chat-bar.js', array('jquery'), '1.0', true);
    wp_enqueue_style('billy_chat_style', plugin_dir_url(__FILE__) . '/chat-bar.css');
    // localize
    wp_localize_script('billy_chat_script', 'billy_chat_vars', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('billy-nonce')
    ));
}
add_action('admin_enqueue_scripts', 'billy_button_enqueue_scripts');

function billy_add_chat_widget() {
    ?>
    <div id="billy_chat_widget" class="collapsed">
        <!-- Chat widget HTML here -->
        <div id="billy_chat_container">
            <div class="billy_top_bar">
                <!-- show AI.svg -->
                Billy <img src="<?php echo plugin_dir_url(__FILE__) . '../static/icons/AI.svg'; ?>" style="height: 20px; margin: 6px;" />
                <!-- three round buttons aligned to the right -->
                <div class="billy_top_bar_buttons">
                    <div class="billy_top_bar_button" id="billy_chat_close">
                        <img src="<?php echo plugin_dir_url(__FILE__) . '../static/icons/Side-menu.svg'; ?>" style="height: 20px; margin: 6px;" />
                    </div>
                    <div class="billy_top_bar_button" id="billy_chat_minimize">
                        <img src="<?php echo plugin_dir_url(__FILE__) . '../static/icons/Minimize.svg'; ?>" style="height: 20px; margin: 6px;" />
                    </div>
                </div>
            </div>
            <div id="billy_chat_messages"></div>
            <div id="billy_chat_input_container">
                <textarea type="text" id="billy_chat_input" placeholder="Ask Billy for any assistance you need"></textarea>
                <button id="billy_chat_send">
                <img src="<?php echo plugin_dir_url(__FILE__) . '../static/icons/Send.svg'; ?>" alt="send" />
                </button>
            </div>
        </div>
    </div>
    <script src='https://cdn.jsdelivr.net/npm/markdown-it/dist/markdown-it.min.js'></script>
    <?php
}
add_action('admin_footer', 'billy_add_chat_widget');
