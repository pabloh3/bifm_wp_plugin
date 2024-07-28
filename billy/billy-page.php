<?php 
require ( __DIR__ . '/../bifm-config.php' );// define base url for the API 
// reset thread_id from user session
if (isset($_SESSION['thread_id'])) {
    unset($_SESSION['thread_id']);
}
?>

<!-- This is the Billy page tool -->
<!--Stylesheet for handling markup-->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel='stylesheet' href='//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.3.1/styles/default.min.css'>
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
            <?php echo file_get_contents(plugin_dir_path(__FILE__) . '../static/icons/AI.svg'); ?>
          </div>
          <div class="menu-label-center">Billy</div>
          <div class="right-billy-buttons">
            <a href="" id="new_chat_button" class="waves-effect billy-button tooltipped" data-tooltip="New chat">
              <div class="svg-icon inline-icon">
                <?php echo file_get_contents(plugin_dir_path(__FILE__) . '../static/icons/New_chat.svg'); ?>
              </div>
            </a>
            <a href="admin.php?page=create-chat" class="waves-effect billy-button tooltipped" data-tooltip="Chat settings">
              <div class="svg-icon inline-icon">
                <?php echo file_get_contents(plugin_dir_path(__FILE__) . '../static/icons/Tune.svg'); ?>
              </div>
            </a>
          </div>
        </div>
      </li>
      <li>
      <!-- load from uptions the values of assistant_thread_ids and add a button for each -->
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
                    <?php echo file_get_contents(plugin_dir_path(__FILE__) . '../static/icons/Writer.svg'); ?>
                </div>
              </div>
              <span class="card-title">Writer bot</span>
              <p>Our AI-powered blog post generator crafts engaging articles efficiently, enhancing your blog’s content effortlessly.</p>
            </button>
        </div>
        <!-- Second Column -->
        <div class="bifm-col s6">
            <button class="bifm-btn waves-effect waves-light suggestion-button transparent">Review my pages for spelling</button>
            <button class="bifm-btn waves-effect waves-light suggestion-button transparent">How do I capture SEO traffic?</button>
            <button id="use-coder-suggestion" class="bifm-btn btn-large waves-effect waves-light suggestion-button card transparent">
              <div class="frame">
                <div class="svg-icon coder-icon">
                  <?php echo file_get_contents(plugin_dir_path(__FILE__) . '../static/icons/Coder.svg'); ?>
                </div>
              </div>
              <span class="card-title">Widget Builder</span>
              <p>Create interactive widgets and calculators for your site (requires Elementor).</p>
            </button>
        </div>
      </div>
    </div>
    <div id='billy-chatbox-input'>
        <form id='billy-form' method='POST' class='input-field'>
                <textarea id="assistant_instructions" name="assistant_instructions" class="materialize-textarea"></textarea>
                <label for="assistant_instructions">Ask Billy for any assistance you need:</label>
                <button class="btn-floating btn-small waves-effect waves-light blue send-input" type="submit" id="billy-submit_chat">
                    <i class="material-icons">arrow_forward</i>
                </button>
        </form>
        <div id ="billy-form-footer" class="grey-text lighten-2">Go to <a href="admin.php?page=create-chat" class="black-text">Settings</a> to change the way Billy responds.</div>
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
