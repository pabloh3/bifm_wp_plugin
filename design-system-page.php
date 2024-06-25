<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Design System Page</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <a href="admin.php?page=bifm-plugin" class="btn waves-effect waves-light purple light-grey" style="width: 120px;">
        <i class="material-icons left">arrow_back</i>
        Back
    </a>
    <div class="row">
        <div class="plugin-menu">
            <ul id="slide-out" class="sidenav sidenav-fixed browser-default">
                <li>
                    <div class="user-view">
                        <div class="form-icon-button btn-floating btn-small waves-effect waves-light back-button">
                            <i class="arrow-left material-icons">arrow_back</i>
                        </div>
                    </div>
                </li>
                <li><a href="admin.php?page=smart-chat" class="waves-effect"><i class="material-icons">chat</i>Smart Chat</a></li>
                <li><a href="admin.php?page=blog-generator" class="waves-effect"><i class="material-icons">edit</i>Blog Generator</a></li>
                <li><a href="admin.php?page=widget-generator" class="waves-effect"><i class="material-icons">widgets</i>Widget Generator</a></li>
                <li><a href="admin.php?page=settings" class="waves-effect"><i class="material-icons">settings</i>Settings</a></li>
            </ul>
        </div>
        <div class="plugin-content">
            <button id="backButton" class="btn waves-effect waves-light red lighten-2">
                <i class="material-icons left">arrow_back</i>Back
            </button>
            <div class="container">
                <div id="smart-chat" class="col s12">
                    <h1>This is an h1</h1>
                    <h2>This is an h2</h2>
                    <h3>This is an h3</h3>
                    <h4>This is an h4</h4>
                    <h5>This is an h5</h5>
                    <h6>This is an h6</h6>
                    <p>This is a paragraph</p>
                    This is just text<br/>
                    <i>This is italic text</i><br/>
                    <b>This is bold text</b><br/>
                    <u>This is underlined text</u><br/>
                    <a href="#">This is a link</a><br/>
                </div>
            </div>

    <div class="row">
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
            echo "<div class='col s12 m6 l4'>";
            echo "<div class='card'>";
            echo "<span class='card-title'>" . ucfirst($colorName) . "</span>";
            echo "<p>" . $class . "</p>";
            echo "<div class='card-content " . $class . "'>";
            echo "</div></div></div>";
        }
        ?>
    </div>
    <div class="row">
        <div class="file-field col s12 l6">
            <div class="btn waves-effect waves-light blue lighten-2">
                <i class="material-icons left">cloud_upload</i>
                <span>Upload Files</span>
                <input type="file" id="fileUpload" name="files[]" multiple accept=".c, .cpp, application/vnd.openxmlformats-officedocument.wordprocessingml.document, .html, .java, application/json, .md, application/pdf, .php, application/vnd.openxmlformats-officedocument.presentationml.presentation, .py, .rb, .tex, .txt">
            </div>
        </div>
    </div>
    <button class="btn waves-effect waves-light" type="submit" name="action">Save Changes</button>
    <button class="btn waves-effect waves-light red lighten-2" type="submit" name="action" id="reset_chat">Reset chatbot</button>
    <h5>Inputs</h5>
    <form id="smart-chat-form" action="#" method="post">
        <div class="row">
            <div class="input-field large col s12 l8">
                <textarea id="assistant_instructions" name="assistant_instructions" class="materialize-textarea"></textarea>
                <button class="btn-floating btn-small waves-effect waves-light blue send-input" type="submit">
                    <i class="material-icons">send</i>
                </button>
                <label for="text_input">Text Input</label>
            </div>
        </div>
        <div class="input-field">
            <input id="search" type="text" class="validate" placeholder="Ask Billy for any assistance you need">
            <button class="btn-floating btn-small waves-effect waves-light blue send-input" type="submit">
                <i class="material-icons">send</i>
            </button>
        </div>
        <div class="row">
            <div class="input-field col s12 l8">
                <select id="select_input_1" name="select_input_1">
                    <option value="" disabled selected>Placeholder</option>
                    <option value="1">Option 1</option>
                    <option value="2">Option 2</option>
                    <option value="3">Option 3</option>
                </select>
                <label for="select_input_1">Label</label>
            </div>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var elems = document.querySelectorAll("select");
                var instances = M.FormSelect.init(elems);
            });
        </script>
        <div class="row">
            <div class="input-field col s12 l8">
                <textarea id="textarea_input_1" name="textarea_input_1" class="materialize-textarea" placeholder="Ask Billy for any assistance you need"></textarea>
                <label for="textarea_input_1">Label</label>
            </div>
        </div>
        <div class="row">
            <div class="input-field col s12 l8">
                <input id="email_input" type="email" name="email_input">
                <label for="email_input">Email Input</label>
            </div>
        </div>
        <div class="row">
            <div class="input-field col s12 l8">
                <input id="password_input" type="password" name="password_input">
                <label for="password_input">Password Input</label>
            </div>
        </div>
        <div class="row">
            <div class="input-field col s12 l8">
                <input id="date_input" type="text" class="datepicker" name="date_input">
                <label for="date_input">Date Input</label>
            </div>
        </div>
        <div class="row">
            <div class="input-field col s12 l8">
                <input id="time_input" type="text" class="timepicker" name="time_input">
                <label for="time_input">Time Input</label>
            </div>
        </div>
        <div class="row">
            <div class="input-field col s12 l8">
                <select id="select_input" name="select_input">
                    <option value="" disabled selected>Choose your option</option>
                    <option value="1">Option 1</option>
                    <option value="2">Option 2</option>
                    <option value="3">Option 3</option>
                </select>
                <label for="select_input">Select Input</label>
            </div>
        </div>
        <div class="row">
        <div class="input-field col s12 l8">
            <label>
                <input type="checkbox" id="checkbox_input" name="checkbox_input">
                <span>Checkbox Input</span>
            </label>
        </div>
    </div>

    <!-- Radio buttons -->
    <div class="row">
        <div class="input-field col s12 l8">
            <p>
                <label>
                    <input name="radio_group" type="radio" id="radio1" value="1">
                    <span>Radio Option 1</span>
                </label>
            </p>
            <p>
                <label>
                    <input name="radio_group" type="radio" id="radio2" value="2">
                    <span>Radio Option 2</span>
                </label>
            </p>
        </div>
    </div>

    <!-- Switch -->
    <div class="row">
        <div class="input-field col s12 l8">
            <div class="switch">
                <label>
                    Off
                    <input type="checkbox" id="switch_input" name="switch_input">
                    <span class="lever"></span>
                    On
                </label>
            </div>
        </div>
    </div>
</form>

<h5>Cards</h5>
<div class="row">
    <div class="col s12 m6 l4">
        <div class="card tool-s-chat-bubble writer-bot">
            <div class="card-content frame-10120667">
                <div class="frame">
                    <div class="svg-icon writer-icon">
                        <?php echo file_get_contents(plugin_dir_path(__FILE__) . 'static/icons/Writer.svg'); ?>
                    </div>
                </div>
                <span class="card-title">Writer bot</span>
                <p>
                    Our AI-powered blog post generator crafts engaging articles efficiently, enhancing your blog’s content effortlessly.
                </p>
            </div>
            <button class="btn waves-effect waves-light card-button writer-button" type="submit" name="action">Go to the writer bot</button>
        </div>
    </div>

    <div class="col s12 m6 l4">
        <div class="card tool-s-chat-bubble coder-bot">
            <div class="card-content frame-10120667">
                <div class="frame">
                    <div class="svg-icon coder-icon">
                        <?php echo file_get_contents(plugin_dir_path(__FILE__) . 'static/icons/Coder.svg'); ?>
                    </div>
                </div>
                <span class="card-title">Coder bot</span>
                <p>
                    Our AI-powered code generator creates efficient code, enhancing your projects effortlessly.
                </p>
            </div>
            <button class="btn waves-effect waves-light card-button coder-button" type="submit" name="action">Go to the coder bot</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>