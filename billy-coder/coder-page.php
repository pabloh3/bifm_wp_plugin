<?php
// Extract the 'client_folder' parameter from the URL
//echo "Get: ", var_dump($_GET); for debugging
$folderName = isset($_GET['foldername']) ? sanitize_text_field($_GET['foldername']) : '';
$client_folder = preg_replace("/[a-zA-Z]/", "", $folderName); // Remove any letters
require ( __DIR__ . '/../bifm-config.php' );// define base url for the API
$url = $WIDGET_URL . esc_attr($folderName) . "/widget.php "; 
$widgets_manager = \Elementor\Plugin::$instance->widgets_manager;
$widget_types = $widgets_manager->get_widget_types();
//localhost:3013/<foldername>/widget.php
// for testing
?>

<!-- This is the Widget Builder tool -->
<!--Stylesheet for handling markup-->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel='stylesheet' href='//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.3.1/styles/default.min.css'>
<div id="undo-button" class="undo-buttonesque waves-effect tooltipped" data-tooltip="Undo last request">
    <?php echo file_get_contents(plugin_dir_path(__FILE__) . '../static/icons/Undo.svg'); ?>
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
          <div class="menu-label-center">Widgets</div>
          <div class="right-billy-buttons">
            <!--<a href="" id="new_chat_button" class="waves-effect billy-button tooltipped" data-tooltip="New widget">
              <div class="svg-icon inline-icon">
                <?php //echo file_get_contents(plugin_dir_path(__FILE__) . '../static/icons/New_chat.svg'); ?>
              </div>
            </a> -->
            <a href="" id="reset-button" class="waves-effect billy-button tooltipped" data-tooltip="Reset widget">
              <div class="svg-icon inline-icon">
                <?php echo file_get_contents(plugin_dir_path(__FILE__) . '../static/icons/Broom.svg'); ?>
              </div>
            </a>
          </div>
        </div>
      </li>
      <li>
        <a href="" id="controls-stage" class="waves-effect tooltipped" data-tooltip="Ask bot to add Elementor controls">
          <div class="svg-icon inline-icon">
            <?php echo file_get_contents(plugin_dir_path(__FILE__) . '../static/icons/Tune.svg'); ?>
          </div>
          Add Elementor Controls
        </a>
      </li>
      <li>
        <a href="" id="visual-stage" class="waves-effect tooltipped" data-tooltip="Back to modify widget" style="display:none" >
          <div class="svg-icon inline-icon">
            <?php echo file_get_contents(plugin_dir_path(__FILE__) . '../static/icons/Back.svg'); ?>
          </div>
          Back to edit widget
        </a>
      </li>
      <li>
        <a href="" id="save-button" class="waves-effect">
          <div class="svg-icon coder-icon inline-icon">
            <?php echo file_get_contents(plugin_dir_path(__FILE__) . '../static/icons/Save.svg'); ?>
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
                <?php echo file_get_contents(plugin_dir_path(__FILE__) . '../static/icons/Coder.svg'); ?>
            </div>
            Widget Builder
        </div>
        <div id='builder-instructions'>
            <div id='builder-chatbox'> 
            </div>
            <div id='billy-chatbox-input'>
                <form id='builder-form' method='POST' class='input-field'>
                        <textarea id="builder_instructions" name="builder_instructions" class="materialize-textarea"></textarea>
                        <label for="builder_instructions">Build me a widget that...</label>
                        <button class="btn-floating btn-small waves-effect waves-light blue send-input" type="submit" id="builder-submit-chat">
                            <i class="material-icons">arrow_forward</i>
                        </button>
                </form>
            </div>
        </div>
        <div id='preview-section'>
            <p>Preview</p>
            <div class='widget-preview aspect-16-9'>
                <iframe id='bifm-builder-frame' src=<?php echo $url; ?>frameborder='0' allowfullscreen></iframe>
            </div>
        </div>
    </div>
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h5>Welcome to the Widget Builder!</h5>
            <p>This tool generates new code for your website through AI. <br/>As with all new code, it should be generated in a "staging" or testing environment, else you risk temporarily taking down your site.</p><br/>
            <a href="admin.php?page=bifm-plugin" id="backButton" class="bifm-btn modal-button waves-effect waves-light purple light-grey" style="width: 120px;">
                Go Back
            </a>
            <a href="" id="continueButton" class="bifm-btn modal-button waves-effect waves-light violet" style="width: 120px;">
                Continue to Builder
            </a>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/markdown-it/dist/markdown-it.min.js'></script>
<script src='//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.3.1/highlight.min.js'></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('.tooltipped');
    var instances = M.Tooltip.init(elems);
  });
</script>
