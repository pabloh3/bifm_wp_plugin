<?php
// Extract the 'client_folder' parameter from the URL
//echo "Get: ", var_dump($_GET); for debugging
$folderName = isset($_GET['foldername']) ? sanitize_text_field($_GET['foldername']) : '';
$client_folder = preg_replace("/[a-zA-Z]/", "", $folderName); // Remove any letters
require ( __DIR__ . '/../bifm-config.php' );// define base url for the API
$url = $WIDGET_URL . esc_attr($folderName) . "/widget.php "; 
//localhost:3013/<foldername>/widget.php
// for testing
?>

<!-- This is the Widget Builder tool -->
<!--Stylesheet for handling markup-->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel='stylesheet' href='//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.3.1/styles/default.min.css'>
<div id="undo-button" class="undo-buttonesque">
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
            <a href="" id="new_chat_button" class="waves-effect billy-button tooltipped" data-tooltip="New widget">
              <div class="svg-icon inline-icon">
                <?php echo file_get_contents(plugin_dir_path(__FILE__) . '../static/icons/New_chat.svg'); ?>
              </div>
            </a>
            <a href="" id="reset-button" class="waves-effect billy-button tooltipped" data-tooltip="Reset widget">
              <div class="svg-icon inline-icon">
                <?php echo file_get_contents(plugin_dir_path(__FILE__) . '../static/icons/Broom.svg'); ?>
              </div>
            </a>
          </div>
        </div>
      </li>
      <li>
      <!-- load from uptions the values of builder_thread_ids and add a button for each -->
      <?php
        $assistant_thread_data = get_option('assistant_thread_data');
        // invert order
        $assistant_thread_data = array_reverse($assistant_thread_data);
        if ($assistant_thread_data) {
            echo "<div class='thread-list'>";
            foreach ($assistant_thread_data as $thread_id => $message_snippet) {
                $display_text = $message_snippet ?: htmlspecialchars($thread_id); // Fallback to thread ID if snippet is not available
                echo "<a href='' class='waves-effect waves-light bifm-btn thread-btn' data-tooltip='Chat with Billy' data-thread-id='" . htmlspecialchars($thread_id) . "'>" . $display_text . "</a>";
            }
            echo "</div>";
        }
      ?>
      <!-- spacer -->
      <li><div class="section-spacer"></div></li>
      <li><div class="section-header"><h6>Tools</h6></div></li>
      <li>
        <a href="admin.php?page=create-blog" class="waves-effect">
          <div class="svg-icon writer-icon inline-icon">
            <?php echo file_get_contents(plugin_dir_path(__FILE__) . '../static/icons/Writer.svg'); ?>
          </div>
          Writer Bot
        </a>
      </li>
      <li>
        <a href="admin.php?page=widget-manager" class="waves-effect">
          <div class="svg-icon coder-icon inline-icon">
            <?php echo file_get_contents(plugin_dir_path(__FILE__) . '../static/icons/Coder.svg'); ?>
          </div>
          Widget Builder
        </a>
      </li>
      <li>
        <a href="admin.php?page=writer-settings" class="waves-effect">
          <i class="material-icons">settings</i>Writer settings
        </a>
      </li>
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
