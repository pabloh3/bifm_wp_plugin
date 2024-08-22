<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// Extract the 'client_folder' parameter from the URL
//echo "Get: ", var_dump($_GET); for debugging
$folderName = isset($_GET['foldername']) ? sanitize_text_field($_GET['foldername']) : ''; // phpcs:ignore
$client_folder = preg_replace("/[a-zA-Z]/", "", $folderName); // Remove any letters
require ( __DIR__ . '/../bifm-config.php' );// define base url for the API
$url = $WIDGET_URL . esc_attr($folderName) . "/widget.php"; 
$widgets_manager = \Elementor\Plugin::$instance->widgets_manager;
$widget_types = $widgets_manager->get_widget_types();
?>

<!-- This is the Widget Builder tool --> 
<div id="undo-button" class="undo-buttonesque waves-effect tooltipped" data-tooltip="Undo last request">
    <?php include(plugin_dir_path(__FILE__) . '../static/icons/Undo.svg'); ?>
</div>
<div class="plugin-page">
  <div class="plugin-menu">
    <ul id="slide-out" class="sidenav sidenav-fixed browser-default">
      <li>
        <div class="user-view">
          <div id="backButton" class="icon-button btn-floating btn-small waves-effect waves-light back-button">
            <i class="arrow-left material-icons">arrow_back</i>
          </div>
        </div>
      </li>
      <!-- billy menu -->
      <li>
        <div class="billy-menu">
          <div class="menu-label-center"><?php esc_html_e('Widgets','bifm'); ?></div>
          <div class="right-billy-buttons">
            <!--<a href="" id="bifm_new_chat_button" class="waves-effect billy-button tooltipped" data-tooltip="New widget">
              <div class="svg-icon inline-icon">
                <?php //include(plugin_dir_path(__FILE__) . '../static/icons/bifm_new_chat.svg'); ?>
              </div>
            </a> -->
            <a href="" id="reset-button" class="waves-effect billy-button tooltipped" data-tooltip="<?php esc_html_e('Reset widget','bifm'); ?>">
              <div class="svg-icon inline-icon">
                <?php include(plugin_dir_path(__FILE__) . '../static/icons/Broom.svg'); ?>
              </div>
            </a>
          </div>
        </div>
      </li>
      <li>
        <a href="" id="controls-stage" class="waves-effect tooltipped" data-tooltip="<?php esc_html_e('Ask bot to add Elementor controls','bifm'); ?>">
          <div class="svg-icon inline-icon">
            <?php include(plugin_dir_path(__FILE__) . '../static/icons/Tune.svg'); ?>
          </div>
          <?php esc_html_e('Add Elementor Controls','bifm'); ?>
        </a>
      </li>
      <li>
        <a href="" id="visual-stage" class="waves-effect tooltipped" data-tooltip="<?php esc_html_e('Back to modify widget','bifm'); ?>" style="display:none" >
          <div class="svg-icon inline-icon">
            <?php include(plugin_dir_path(__FILE__) . '../static/icons/Back.svg'); ?>
          </div>
          <?php esc_html_e('Back to edit widget','bifm'); ?>
        </a>
      </li>
      <li>
        <a href="" id="save-button" class="waves-effect">
          <div class="svg-icon coder-icon inline-icon">
            <?php include(plugin_dir_path(__FILE__) . '../static/icons/Save.svg'); ?>
          </div>
          Save Widget
        </a>
      </li>
      <!-- end of new -->
      <!-- spacer -->
      <li><div class="section-spacer"></div></li>
    </ul>
  </div>

  <!-- Body outside of menu -->
    <div class="plugin-content">
        <div class="section-header">
            <div class="svg-icon coder-icon inline-icon">
                <?php include(plugin_dir_path(__FILE__) . '../static/icons/Coder.svg'); ?>
            </div>
            <?php esc_html_e('Widget Builder','bifm'); ?>
        </div>
        <div id='builder-instructions'>
            <div id='builder-chatbox'> 
            </div>
            <div id='billy-chatbox-input'>
                <form id='builder-form' method='POST' class='input-field'>
                        <textarea id="builder_instructions" name="builder_instructions" class="materialize-textarea"></textarea>
                        <label for="builder_instructions"><?php esc_html_e('Build me a widget that...','bifm'); ?></label>
                        <button class="btn-floating btn-small waves-effect waves-light blue send-input" type="submit" id="builder-submit-chat">
                          <i class="material-icons">arrow_forward</i>
                        </button>
                </form>
            </div>
        </div>
        <div id='preview-section'>
            <p><?php esc_html_e('Preview','bifm'); ?></p>
            <div class='widget-preview aspect-16-9'>
                <iframe id='bifm-builder-frame' src=<?php echo esc_url($url); ?> frameborder="0" allowfullscreen></iframe>
            </div>
        </div>
    </div>
    <div id="myModal" class="bifm-modal">
        <div class="bifm-modal-content">
            <span class="bifm-close-button">&times;</span>
            <h5><?php esc_html_e('Welcome to the Widget Builder!','bifm'); ?></h5>
            <p><?php esc_html_e('This tool generates new Elementor widgets, and requires the Elementor plugin.','bifm'); ?> <br/><?php esc_html_e('As with all new code, it should be generated in a "staging" or testing environment, else you risk temporarily taking down your site.','bifm'); ?></p><br/>
            <a href="admin.php?page=bifm" id="backButton" class="bifm-btn bifm-modal-button bifm-backButton waves-effect waves-light purple light-grey" style="width: 120px;">
                <?php esc_html_e('Go Back','bifm'); ?>
            </a>
            <a href="" id="continueButton" class="bifm-btn bifm-modal-button bifm-continueButton waves-effect waves-light violet" style="width: 120px;">
                <?php esc_html_e('Continue to Builder','bifm'); ?>
            </a>
        </div>
    </div>
</div>