<?php 
if ( ! defined( 'ABSPATH' ) ) exit;
require ( __DIR__ . '/../bifm-config.php' );// define base url for the API?> 
<br/>
<a href="admin.php?page=bifm" class="bifm-btn waves-effect waves-light purple light-grey" style="width: 120px;">
    <i class="material-icons left">arrow_back</i>
    <?php esc_html_e('Back','bifm'); ?>
</a>
<div class="container">
    <div id="smart-chat" class="bifm-col s12">
        <h5><?php esc_html_e('Smart chat','bifm'); ?></h5>
        <p><?php esc_html_e('Here you can update the settings for smart chat.','bifm'); ?></p>
        
        <form id="smart-chat-form" action="#" method="post">
            <?php $bifm_assistant_instructions = get_option('bifm_assistant_instructions'); ?>
            <div class="bifm-row">
                <div class="input-field bifm-col s12 l8">
                    <textarea id="bifm_assistant_instructions" name="bifm_assistant_instructions" class="materialize-textarea" style="height: 80px;"><?php echo esc_textarea($bifm_assistant_instructions) ?: ''; ?></textarea>
                    <label for="bifm_assistant_instructions"><?php esc_html_e('Give the bot instructions for how to respond. Start with something like "We use Elementor to edit our pages...','bifm'); ?>"</label>
                </div>
            </div>

            <h5><?php esc_html_e('Define the files that will be used to answer chat questions.','bifm'); ?></h5>
            <div><?php esc_html_e('You can upload up to 5 files. The maximum file size is 512 MB and no more than 2 million tokens (approx 1.5M words).','bifm'); ?></div>
            <div>Accepted file types: .c, .cpp, .docx, .html, .java, .json, .md, .pdf, .php, .pptx, .py, .rb, .tex, .txt</div>
            <div class="bifm-row">
                <div class="file-field input-field bifm-col s12 l6" style="border:none; background:none;">
                    <div class="bifm-btn waves-effect waves-light">
                        <i class="material-icons left">cloud_upload</i>
                        <span><?php esc_html_e('Upload Files','bifm'); ?></span>
                        <input type="file" id="fileUpload" name="files[]" multiple accept=".c, .cpp, application/vnd.openxmlformats-officedocument.wordprocessingml.document, .html, .java, application/json, .md, application/pdf, .php, application/vnd.openxmlformats-officedocument.presentationml.presentation, .py, .rb, .tex, .txt">
                    </div>
                    <div class="file-path-wrapper">
                        <input class="file-path validate" type="text" style="width:300px; border-bottom: 0;">
                    </div>
                </div>
            </div>

            <div id="uploadedFilesSection" class="bifm-col l6">
                <h5><?php esc_html_e('Uploaded files','bifm'); ?></h5>
                <div id="uploadedFiles" class="l6">
                    <ul class="collection">
                        <?php
                            // Get the list of files from the database
                            $stored_files = get_option('bifm_uploaded_file_names', array());

                            if (!empty($stored_files)) {
                                foreach ($stored_files as $file) {
                                    // Ensure file_name is set and not empty
                                    if (!empty($file['file_name'])) {
                                        ?>
                                        <li class="collection-item">
                                            <div class="file-name-line" file-name="<?php echo esc_attr($file['file_name']); ?>">
                                                <?php echo esc_html($file['file_name']); ?>
                                                <a href="#!" class="secondary-content">
                                                    <i class="material-icons" onclick="removeFile(this, '<?php echo esc_attr($file['file_name']); ?>')">delete</i>
                                                </a>
                                            </div>
                                        </li>
                                        <?php
                                    }
                                }
                            } else {
                                echo '<li class="collection-item">' . esc_html(__('No files uploaded yet.', 'bifm')) . '</li>';
                            }
                        ?>
                    </ul>
                </div>
            </div>

            <button class="bifm-btn waves-effect waves-light" type="submit" name="action"><?php esc_html_e('Save Changes','bifm'); ?></button>
            <br/>
            <button class="bifm-btn waves-effect waves-light purple grey-purple" type="submit" name="action" id="reset_chat"><?php esc_html_e('Reset chatbot','bifm'); ?></button>
        </form>
    </div>
    <div id="warningMessage" class="card-panel yellow darken-2" style="display: none;"></div>
</div>



