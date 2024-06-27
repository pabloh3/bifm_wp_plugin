<?php require 'bifm-config.php'; ?>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<a href="admin.php?page=bifm-plugin" class="btn bifm-btn waves-effect waves-light purple light-grey" style="width: 120px;">
    <i class="material-icons left">arrow_back</i>
    Back
</a>

<div class="container">
    <div id="smart-chat" class="bifm-col s12">
        <h5>Smart chat</h5>
        <p>Here you can update the settings for smart chat.</p>
        
        <form id="smart-chat-form" action="#" method="post">
            <?php $assistant_instructions = get_option('assistant_instructions'); ?>
            <div class="bifm-row">
                <div class="input-field bifm-col s12 l8">
                    <textarea id="assistant_instructions" name="assistant_instructions" class="materialize-textarea"><?= stripslashes($assistant_instructions) ?: ''; ?></textarea>
                    <label for="assistant_instructions">Give the bot instructions for how to respond. Start with something like "We use Elementor to edit our pages..."</label>
                </div>
            </div>

            <h5>Define the files that will be used to answer chat questions.</h5>
            <div>You can upload up to 5 files. The maximum file size is 512 MB and no more than 2 million tokens (approx 1.5M words).</div>
            <div>Accepted file types: .c, .cpp, .docx, .html, .java, .json, .md, .pdf, .php, .pptx, .py, .rb, .tex, .txt</div>
            <div class="bifm-row">
                <div class="file-field input-field bifm-col s12 l6">
                    <div class="btn bifm-btn waves-effect waves-light">
                        <i class="material-icons left">cloud_upload</i>
                        <span>Upload Files</span>
                        <input type="file" id="fileUpload" name="files[]" multiple accept=".c, .cpp, application/vnd.openxmlformats-officedocument.wordprocessingml.document, .html, .java, application/json, .md, application/pdf, .php, application/vnd.openxmlformats-officedocument.presentationml.presentation, .py, .rb, .tex, .txt">
                    </div>
                    <div class="file-path-wrapper">
                        <input class="file-path validate" type="text" style="width:300px; border-bottom: 0;">
                    </div>
                </div>
            </div>

            <div id="uploadedFilesSection" class="bifm-col l6">
                <h5>Uploaded files</h5>
                <div id="uploadedFiles" class="l6">
                    <ul class="collection">
                        <?php
                        $dirPath = wp_upload_dir()['basedir'] . '/bifm-files/chat_files/';
                        if (is_dir($dirPath) && is_readable($dirPath)) {
                            if ($dh = opendir($dirPath)) {
                                while (($file = readdir($dh)) !== false) {
                                    if ($file != "." && $file != "..") {
                                        ?>
                                        <li class="collection-item">
                                            <div class="file-name-line" file-name="<?= htmlspecialchars($file) ?>">
                                                <?= htmlspecialchars($file) ?>
                                                <a href="#!" class="secondary-content">
                                                    <i class="material-icons" onclick="removeFile(this, '<?= htmlspecialchars($file) ?>')">delete</i>
                                                </a>
                                            </div>
                                        </li>
                                        <?php
                                    }
                                }
                                closedir($dh);
                            }
                        } else {
                            echo '<li class="collection-item">Directory not found or not readable</li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>

            <button class="btn bifm-btn waves-effect waves-light" type="submit" name="action">Save Changes</button>
            <br/>
            <button class="btn bifm-btn waves-effect waves-light purple grey-purple" type="submit" name="action" id="reset_chat">Reset chatbot</button>
        </form>
    </div>
</div>

<div id="warningMessage" class="card-panel yellow darken-2" style="display: none;"></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
