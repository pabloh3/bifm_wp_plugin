<?php require 'bifm-config.php'; ?>

<!-- This file tests my design system -->
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
            <?php echo file_get_contents(plugin_dir_path(__FILE__) . 'static/icons/AI.svg'); ?>
          </div>
          <div class="menu-label-center">Billy</div>
          <div class="right-billy-buttons">
            <a href="" id="new_chat_button" class="waves-effect billy-button tooltipped" data-tooltip="New chat">
              <div class="svg-icon inline-icon">
                <?php echo file_get_contents(plugin_dir_path(__FILE__) . 'static/icons/New_chat.svg'); ?>
              </div>
            </a>
            <a href="admin.php?page=create-chat" class="waves-effect billy-button tooltipped" data-tooltip="Chat settings">
              <div class="svg-icon inline-icon">
                <?php echo file_get_contents(plugin_dir_path(__FILE__) . 'static/icons/Tune.svg'); ?>
              </div>
            </a>
          </div>
        </div>
      </li>
      <!-- spacer -->
      <li><div class="section-spacer"></div></li>
      <li><div class="section-header"><h6>Tools</h6></div></li>
      <li>
        <a href="admin.php?page=create-blog" class="waves-effect">
          <div class="svg-icon writer-icon inline-icon">
            <?php echo file_get_contents(plugin_dir_path(__FILE__) . 'static/icons/Writer.svg'); ?>
          </div>
          Writer Bot
        </a>
      </li>
      <li>
        <a href="admin.php?page=create-widget" class="waves-effect">
          <div class="svg-icon coder-icon inline-icon">
            <?php echo file_get_contents(plugin_dir_path(__FILE__) . 'static/icons/Coder.svg'); ?>
          </div>
          Coder Bot
        </a>
      </li>
      <li>
        <a href="admin.php?page=bifm-plugin" class="waves-effect">
          <i class="material-icons">settings</i>Settings
        </a>
      </li>
    </ul>
  </div>

  <!-- Body outside of menu -->
  <div class="plugin-content">
  <div class="container">
    <div id='billy-chatbox'>
        <form id='billy-form' method='POST' class='input-field'>
                <textarea id="assistant_instructions" name="assistant_instructions" class="materialize-textarea"></textarea>
                <label for="assistant_instructions">Ask Billy for any assistance you need:</label>
                <button class="btn-floating btn-small waves-effect waves-light blue send-input" type="submit">
                    <i class="material-icons">send</i>
                </button>
        </form>
        <div id ="billy-form-footer" class="grey-text lighten-2">Go to <a href="admin.php?page=create-chat" class="black-text">Settings</a> to change the way Billy responds.</div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('.tooltipped');
    var instances = M.Tooltip.init(elems);
  });
</script>
