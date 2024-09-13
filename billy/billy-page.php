<?php 
if ( ! defined( 'ABSPATH' ) ) exit;
require ( __DIR__ . '/../bifm-config.php' );// define base url for the API 
// reset thread_id from user session
if (isset($_SESSION['thread_id'])) {
    unset($_SESSION['thread_id']);
}
?>

<!-- This is the Billy page tool -->
<!--Stylesheet for handling markup--> 
<div class="plugin-page">
  <div class="plugin-menu">
    <ul id="slide-out" class="sidenav sidenav-fixed browser-default">
      <li>
        <div class="user-view">
          <div class="icon-button btn-floating btn-small waves-effect waves-light back-button">
            <i class="arrow-left material-icons">arrow_back</i>
          </div>
        </div>
      </li>
      <!-- billy menu -->
      <li>
        <div class="billy-menu">
          <div class="svg-icon inline-icon">
            <?php include(plugin_dir_path(__FILE__) . '../static/icons/AI.svg'); ?>
          </div>
          <div class="menu-label-center">Billy</div>
          <div class="right-billy-buttons">
            <a href="" id="bifm_new_chat_button" class="waves-effect billy-button tooltipped" data-tooltip="<?php esc_html_e("New chat","bifm"); ?>">
              <div class="svg-icon inline-icon">
                <?php include(plugin_dir_path(__FILE__) . '../static/icons/New_chat.svg'); ?>
              </div>
            </a>
            <a href="admin.php?page=create-chat" class="waves-effect billy-button tooltipped" data-tooltip="<?php esc_html_e("Chat settings","bifm"); ?>">
              <div class="svg-icon inline-icon">
                <?php include(plugin_dir_path(__FILE__) . '../static/icons/Tune.svg'); ?>
              </div>
            </a>
          </div>
        </div>
      </li>
      <li>
      <!-- load from uptions the values of assistant_thread_ids and add a button for each -->
      <?php
        $assistant_thread_data = get_option('bifm_assistant_thread_data');
        // invert order
        if (is_array($assistant_thread_data)) {
        $assistant_thread_data = array_reverse($assistant_thread_data);
           ?><div class='thread-list'><?php
            foreach ($assistant_thread_data as $thread_id => $message_snippet) {
                $display_text = $message_snippet ?: htmlspecialchars($thread_id); // Fallback to thread ID if snippet is not available
                ?><a href='' class='waves-effect waves-light bifm-btn thread-btn' data-tooltip='<?php esc_html_e("Chat with Billy","bifm"); ?>' data-thread-id='<?php echo esc_attr($thread_id); ?>'><?php echo esc_attr($display_text); ?></a><?php
            }
            ?></div><?php
        }
      ?>
      <!-- spacer -->
      <li><div class="section-spacer"></div></li>
      <li><div class="section-header"><h6><?php esc_html_e('Tools','bifm'); ?></h6></div></li>
      <li>
        <a href="admin.php?page=create-blog" class="waves-effect">
          <div class="svg-icon writer-icon inline-icon">
            <?php include(BIFM_PATH . 'static/icons/Writer.svg'); ?>
          </div>
          <?php esc_html_e('Writer Bot','bifm'); ?>
        </a>
      </li>
      <li>
        <a href="admin.php?page=widget-manager" class="waves-effect">
          <div class="svg-icon coder-icon inline-icon">
            <?php include(plugin_dir_path(__FILE__) . '../static/icons/Coder.svg'); ?>
          </div>
          <?php esc_html_e('Widget Builder','bifm'); ?>
        </a>
      </li>
      <li>
        <a href="admin.php?page=writer-settings" class="waves-effect">
          <i class="material-icons">settings</i><?php esc_html_e('Writer settings','bifm'); ?>
        </a>
      </li>
    </ul>
  </div>

  <!-- Body outside of menu -->
  <div class="plugin-content">
  <?php
    // Get the current user ID
    $user_id = get_current_user_id();

    // Check if the user has accepted the terms and conditions
    $has_accepted = get_user_meta($user_id, 'accepted_terms_conditions', true);

    // Display the modal if the user has not accepted the terms and conditions
    if (!$has_accepted) {
        ?>
        <div id="myModal" class="bifm-modal">
            <div class="bifm-modal-content">
                <span class="bifm-close-button">&times;</span>
                <h5><?php esc_html_e('Welcome to Billy!','bifm'); ?></h5>
                <p>

<?php 

  printf(
  /* translators: %1$s: url and %2$s: email */
      wp_kses(__('Thank you for trying our Beta, we appreciate any feedback at <a href="%1$s">%2$s</a>','bifm'), ['p','a'=>['href']] )
      ,'mailto:pablo@builditforme.ai'
      ,'pablo@builditforme.ai'); ?>
                  </p>
                <p>


<?php 
    echo wp_kses( 
        sprintf(
            /* translators: %s: terms and conditions URL */ 
            __('Please note that by using this plugin you are consenting with sharing your email and username with us. You can view our <a href="%s">full privacy policy</a>.','bifm'),
            'https://www.builditforme.ai/terms-and-conditions/'
        ), 
        ['a' => ['href' => []]] 
    );
?>
                  </p><br/>
                <a href="admin.php?page=bifm" id="backButton" class="bifm-btn bifm-modal-button bifm-backButton waves-effect waves-light purple light-grey" style="width: 120px;">
                    <?php esc_html_e('Go Back','bifm'); ?>
                </a>
                <a href="#" id="iAgreeButton" class="bifm-btn bifm-modal-button bifm-continueButton waves-effect waves-light violet" style="width: 120px;">
                    <?php esc_html_e('I agree','bifm'); ?>
                </a>
            </div>
        </div>
        <div id="myModal2" class="bifm-modal">
          <div class="bifm-modal-content">
            <span class="bifm-close-button">&times;</span>
            <h5><?php esc_html_e('One more thing','bifm'); ?></h5>
            <p><?php esc_html_e('You can start using Billy, but it won\'t have access to your site\'s content and configuration until you give Billy a username and application password.','bifm'); ?></p>
            <p><?php esc_html_e('This also enables Billy to write blog posts.','bifm'); ?></p><br/>
            <a href="" id="laterButton" class="bifm-btn bifm-modal-button bifm-backButton waves-effect waves-light purple light-grey" style="width: 120px;">
                <?php esc_html_e('Later','bifm'); ?>
            </a>
            <a href="/wp-admin/admin.php?page=writer-settings" id="setupBilly" class="bifm-btn bifm-modal-button bifm-continueButton waves-effect waves-light violet" style="width: 120px;">
                <?php esc_html_e('Set up now','bifm'); ?>
            </a>
          </div>
        </div>
        <?php
    }
  ?>
  <div class="container">
    <div id='billy-chatbox'>
      <div id="suggestion-buttons" class="bifm-row suggestions">
        <!-- First Column -->
        <div class="bifm-col s6">
            <button class="bifm-btn waves-effect waves-light suggestion-button transparent">How do I create a new page on WP?</button>
            <button class="bifm-btn waves-effect waves-light suggestion-button transparent">Write a blog post on dogs</button>
            <button id="use-writer-suggestion" class="bifm-btn btn-large waves-effect waves-light suggestion-button card transparent">
              <div class="frame">
                <div class="svg-icon writer-icon">
                    <?php include(plugin_dir_path(__FILE__) . '../static/icons/Writer.svg'); ?>
                </div>
              </div>
              <span class="card-title"><?php esc_html_e('Writer bot','bifm'); ?></span>
              <p><?php esc_html_e('Our AI-powered blog post generator crafts engaging articles efficiently, enhancing your blog’s content effortlessly.','bifm'); ?></p>
            </button>
        </div>
        <!-- Second Column -->
        <div class="bifm-col s6">
            <button class="bifm-btn waves-effect waves-light suggestion-button transparent"><?php esc_html_e('Review my pages for spelling','bifm'); ?></button>
            <button class="bifm-btn waves-effect waves-light suggestion-button transparent"><?php esc_html_e('How do I capture SEO traffic?','bifm'); ?></button>
            <button id="use-coder-suggestion" class="bifm-btn btn-large waves-effect waves-light suggestion-button card transparent">
              <div class="frame">
                <div class="svg-icon coder-icon">
                  <?php include(plugin_dir_path(__FILE__) . '../static/icons/Coder.svg'); ?>
                </div>
              </div>
              <span class="card-title"><?php esc_html_e('Widget Builder','bifm'); ?></span>
              <p><?php esc_html_e('Create interactive widgets and calculators for your site (requires Elementor).','bifm'); ?></p>
            </button>
        </div>
      </div>
    </div>
    <div id='billy-chatbox-input'>
        <form id='billy-form' method='POST' class='input-field'>
                <textarea id="assistant_instructions" name="assistant_instructions" class="materialize-textarea"></textarea>
                <label for="assistant_instructions"><?php esc_html_e('Ask Billy for any assistance you need:','bifm'); ?></label>
                <button class="btn-floating btn-small waves-effect waves-light blue send-input" type="submit" id="billy-submit_chat">
                    <i class="material-icons">arrow_forward</i>
                </button>
        </form>
        <div id ="billy-form-footer" class="grey-text lighten-2">
          <?php /* translators: %s: create chat page URL */
           printf(esc_html_e('Go to ','bifm')) ?><a href="admin.php?page=create-chat" class="black-text"><?php esc_html_e('Settings','bifm'); ?></a> <?php printf(esc_html_e(' to change the way Billy responds.','bifm')); ?><br>
           <?php /* translators: %s: API information */
           printf(esc_html_e('This plugin uses our Build It For Me API. By using it you accept our ','bifm')) ?> <a href="https://www.builditforme.ai/terms-and-conditions/" class="black-text"><?php esc_html_e('Terms and Conditions','bifm'); ?></a> . 

          </div>
    </div>
  </div>
</div>