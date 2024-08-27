<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php esc_html_e('Design System Page','bifm'); ?></title> 
</head>
<body>
    <a href="admin.php?page=bifm" class="bifm-btn waves-effect waves-light purple light-grey" style="width: 120px;">
        <i class="material-icons left">arrow_back</i>
        <?php esc_html_e('Back','bifm'); ?>
    </a>
    <div class="bifm-row">
        <div class="plugin-menu">
            <ul id="slide-out" class="sidenav sidenav-fixed browser-default">
                <li>
                    <div class="user-view">
                        <div class="form-icon-button btn-floating btn-small waves-effect waves-light back-button">
                            <i class="arrow-left material-icons">arrow_back</i>
                        </div>
                    </div>
                </li>
                <li><a href="admin.php?page=smart-chat" class="waves-effect"><i class="material-icons">chat</i>
        <?php esc_html_e('Smart Chat','bifm'); ?></a></li>
                <li><a href="admin.php?page=blog-generator" class="waves-effect"><i class="material-icons">edit</i>
        <?php esc_html_e('Blog Generator','bifm'); ?></a></li>
                <li><a href="admin.php?page=widget-generator" class="waves-effect"><i class="material-icons">widgets</i>
        <?php esc_html_e('Widget Generator','bifm'); ?></a></li>
                <li><a href="admin.php?page=settings" class="waves-effect"><i class="material-icons">settings</i>
        <?php esc_html_e('Settings','bifm'); ?></a></li>
            </ul>
        </div>
        <div class="plugin-content">
            <button id="backButton" class="bifm-btn waves-effect waves-light red lighten-2">
                <i class="material-icons left">arrow_back</i>
        <?php esc_html_e('Back','bifm'); ?>
            </button>
            <div class="container">
                <div id="smart-chat" class="bifm-col s12">
                    <h1><?php esc_html_e('This is an h1','bifm'); ?></h1>
                    <h2><?php esc_html_e('This is an h2','bifm'); ?></h2>
                    <h3><?php esc_html_e('This is an h3','bifm'); ?></h3>
                    <h4><?php esc_html_e('This is an h4','bifm'); ?></h4>
                    <h5><?php esc_html_e('This is an h5','bifm'); ?></h5>
                    <h6><?php esc_html_e('This is an h6','bifm'); ?></h6>
                    <p><?php esc_html_e('This is a paragraph','bifm'); ?></p>
                    <?php esc_html_e('This is just text','bifm'); ?><br/>
                    <i><?php esc_html_e('This is italic text','bifm'); ?></i><br/>
                    <b><?php esc_html_e('This is bold text','bifm'); ?></b><br/>
                    <u><?php esc_html_e('This is underlined text','bifm'); ?></u><br/>
                    <a href="#"><?php esc_html_e('This is a link','bifm'); ?></a><br/>
                </div>
            </div>

    <div class="bifm-row">
        <?php
        $colors = [
            'secondary-color or blue' => 'blue base',
            'secondary-light-purple' => 'purple secondary-light-purple',
            'organge' => 'orange base',
            'light orange' => 'orange lighten-5',
            'violet' => 'violet base',
            'purple-lighten-5' => 'purple lighten-5',
            'dark-purple' => 'purple dark-purple',
            'gray-purple' => 'purple gray-purple',
            'main-Purple' => 'purple main-Purple',
            'light-purple' => 'purple light-purple',
            'light-grey' => 'grey light-grey',
            'off-white' => 'grey off-white',
            'primary-light' => 'red lighten-4',
            'primary-dark' => 'red darken-4',
            'secondary-light' => 'pink lighten-4',
            'secondary-dark' => 'pink darken-4',
            'background-color' => 'grey lighten-5',
            'surface-color' => 'white base',
            'error-color' => 'red base',
            'on-primary' => 'white base',
            'on-secondary' => 'black base',
            'on-background' => 'black base',
            'on-surface' => 'black base',
            'on-error' => 'white base',
        ];
        foreach ($colors as $colorName => $class) {
            ?><div class='col s12 m6 l4'>
            <div class='card'>
            <span class='card-title'><?php echo esc_html(ucfirst($colorName)); ?></span>
            <p><?php echo esc_html($class); ?></p>
            <div class='card-content <?php echo esc_attr($class); ?>'>
            </div></div></div><?php
        }
        ?>
    </div>
    <div class="bifm-row">
        <div class="file-field bifm-col s12 l6">
            <div class="bifm-btn waves-effect waves-light blue lighten-2">
                <i class="material-icons left">cloud_upload</i>
                <span><?php esc_html_e('Upload Files','bifm'); ?></span>
                <input type="file" id="fileUpload" name="files[]" multiple accept=".c, .cpp, application/vnd.openxmlformats-officedocument.wordprocessingml.document, .html, .java, application/json, .md, application/pdf, .php, application/vnd.openxmlformats-officedocument.presentationml.presentation, .py, .rb, .tex, .txt">
            </div>
        </div>
    </div>
    <button class="bifm-btn waves-effect waves-light" type="submit" name="action">
        <?php esc_html_e('Save changes','bifm'); ?></button>
    <button class="bifm-btn waves-effect waves-light red lighten-2" type="submit" name="action" id="reset_chat">
        <?php esc_html_e('Reset chatbot','bifm'); ?></button>
    <h5>Inputs</h5>
    <form id="smart-chat-form" action="#" method="post">
        <div class="bifm-row">
            <div class="input-field large bifm-col s12 l8">
                <textarea id="assistant_instructions" name="assistant_instructions" class="materialize-textarea"></textarea>
                <button class="btn-floating btn-small waves-effect waves-light blue send-input" type="submit">
                    <i class="material-icons">send</i>
                </button>
                <label for="text_input"><?php esc_html_e('Text Input','bifm'); ?></label>
            </div>
        </div>
        <div class="input-field">
            <input id="search" type="text" class="validate" placeholder="Ask Billy for any assistance you need">
            <button class="btn-floating btn-small waves-effect waves-light blue send-input" type="submit">
                <i class="material-icons">send</i>
            </button>
        </div>
        <div class="bifm-row">
            <div class="input-field bifm-col s12 l8">
                <select id="select_input_1" name="select_input_1">
                    <option value="" disabled selected><?php esc_html_e('Placeholder','bifm'); ?></option>
                    <option value="1"><?php esc_html_e('Option 1','bifm'); ?></option>
                    <option value="2"><?php esc_html_e('Option 2','bifm'); ?></option>
                    <option value="3"><?php esc_html_e('Option 3','bifm'); ?></option>
                </select>
                <label for="select_input_1"><?php esc_html_e('Label','bifm'); ?></label>
            </div>
        </div> 
        <div class="bifm-row">
            <div class="input-field bifm-col s12 l8">
                <textarea id="textarea_input_1" name="textarea_input_1" class="materialize-textarea" placeholder="Ask Billy for any assistance you need"></textarea>
                <label for="textarea_input_1"><?php esc_html_e('Label','bifm'); ?></label>
            </div>
        </div>
        <div class="bifm-row">
            <div class="input-field bifm-col s12 l8">
                <input id="email_input" type="email" name="email_input">
                <label for="email_input"><?php esc_html_e('Email Input','bifm'); ?></label>
            </div>
        </div>
        <div class="bifm-row">
            <div class="input-field bifm-col s12 l8">
                <input id="password_input" type="password" name="password_input">
                <label for="password_input"><?php esc_html_e('Password Input','bifm'); ?></label>
            </div>
        </div>
        <div class="bifm-row">
            <div class="input-field bifm-col s12 l8">
                <input id="date_input" type="text" class="datepicker" name="date_input">
                <label for="date_input"><?php esc_html_e('Date Input','bifm'); ?></label>
            </div>
        </div>
        <div class="bifm-row">
            <div class="input-field bifm-col s12 l8">
                <input id="time_input" type="text" class="timepicker" name="time_input">
                <label for="time_input"><?php esc_html_e('Time Input','bifm'); ?></label>
            </div>
        </div>
        <div class="bifm-row">
            <div class="input-field bifm-col s12 l8">
                <select id="select_input" name="select_input">
                    <option value="" disabled selected><?php esc_html_e('Choose your option','bifm'); ?></option>
                    <option value="1"><?php esc_html_e('Option 1','bifm'); ?></option>
                    <option value="2"><?php esc_html_e('Option 2','bifm'); ?></option>
                    <option value="3"><?php esc_html_e('Option 3','bifm'); ?></option>
                </select>
                <label for="select_input"><?php esc_html_e('Select Input','bifm'); ?></label>
            </div>
        </div>
        <div class="bifm-row">
        <div class="input-field bifm-col s12 l8">
            <label>
                <input type="checkbox" id="checkbox_input" name="checkbox_input">
                <span><?php esc_html_e('Checkbox Input','bifm'); ?></span>
            </label>
        </div>
    </div>

    <!-- Radio buttons -->
    <div class="bifm-row">
        <div class="input-field bifm-col s12 l8">
            <p>
                <label>
                    <input name="radio_group" type="radio" id="radio1" value="1">
                    <span><?php esc_html_e('Radio Option 1','bifm'); ?></span>
                </label>
            </p>
            <p>
                <label>
                    <input name="radio_group" type="radio" id="radio2" value="2">
                    <span><?php esc_html_e('Radio Option 2','bifm'); ?></span>
                </label>
            </p>
        </div>
    </div>

    <!-- Switch -->
    <div class="bifm-row">
        <div class="input-field bifm-col s12 l8">
            <div class="switch">
                <label>
                    <?php esc_html_e('Off','bifm'); ?>
                    <input type="checkbox" id="switch_input" name="switch_input">
                    <span class="lever"></span>
                    <?php esc_html_e('On','bifm'); ?>
                </label>
            </div>
        </div>
    </div>
</form>

<h5><?php esc_html_e('Cards','bifm'); ?></h5>
<div class="bifm-row">
    <div class="bifm-col s12 m6 l4">
        <div class="card tool-s-chat-bubble writer-bot">
            <div class="card-content frame-10120667">
                <div class="frame">
                    <div class="svg-icon writer-icon">
                        <?php include(plugin_dir_path(__FILE__) . 'static/icons/Writer.svg'); ?>
                    </div>
                </div>
                <span class="card-title"><?php esc_html_e('Writer bot','bifm'); ?></span>
                <p>
                    <?php esc_html_e('Our AI-powered blog post generator crafts engaging articles efficiently, enhancing your blog’s content effortlessly.','bifm'); ?>
                </p>
            </div>
            <button class="bifm-btn waves-effect waves-light card-button writer-button" type="submit" name="action">Go to the writer bot</button>
        </div>
    </div>

    <div class="bifm-col s12 m6 l4">
        <div class="card tool-s-chat-bubble coder-bot">
            <div class="card-content frame-10120667">
                <div class="frame">
                    <div class="svg-icon coder-icon">
                        <?php include(plugin_dir_path(__FILE__) . 'static/icons/Coder.svg'); ?>
                    </div>
                </div>
                <span class="card-title"><?php esc_html_e('Coder Bot','bifm'); ?></span>
                <p>
                    <?php esc_html_e('Our AI-powered code generator creates efficient code, enhancing your projects effortlessly.','bifm'); ?>
                </p>
            </div>
            <button class="bifm-btn waves-effect waves-light card-button coder-button" type="submit" name="action">Go to the coder bot</button>
        </div>
    </div>
</div>


</body>
</html>