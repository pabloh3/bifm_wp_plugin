<?php require 'bifm-config.php'; ?>

<style>
    .input-field {
        margin: 10px;
    }
</style>

<a href="admin.php?page=bifm-plugin" class="btn bifm-btn waves-effect waves-light purple light-grey" style="width: 120px;">
    <i class="material-icons left">arrow_back</i>
    Back
</a>

<div id="settings" class="col s12">
    <h5>Settings</h5>
    <p>Here you can update the settings for blog creation.</p>
    <p>Please note that the blog creator requires the <a href="https://github.com/WP-API/Basic-Auth">"JSON Basic Authentication"</a> plugin by the Wordpress API team to be installed.</p>

    <form id="bifm-settings-form" action="#" method="post">
        <?php $user_id = get_current_user_id(); ?>
        <?php $username = get_user_meta($user_id, 'username', true); ?>
        <div class="row">
            <div class="input-field col s12 l4">
                <input id="blog_author_username" type="text" name="blog_author_username" class="validate materialize-textarea" value="<?= htmlspecialchars($username) ?>" <?= is_null($username) ? '' : 'required' ?>>
                <label for="blog_author_username">Blog author's username</label>
                <p style="color:red">Create an author account that only has author access, DO NOT use an admin account.</p>
            </div>

            <div class="input-field col s12 l4">
                <input id="blog_author_password" type="password" name="blog_author_password" class="validate materialize-textarea">
                <label for="blog_author_password">Blog author's password</label>
            </div>
        </div>

        <?php $blog_language = get_user_meta($user_id, 'blog_language', true); ?>
        <div class="row">
            <div class="input-field col s12 l4">
                <select id="blog_language" name="blog_language">
                    <option value="" disabled <?= empty($blog_language) ? 'selected' : '' ?>>Choose your option</option>
                    <option value="english" <?= $blog_language == 'english' ? 'selected' : '' ?>>English</option>
                    <option value="spanish" <?= $blog_language == 'spanish' ? 'selected' : '' ?>>Spanish</option>
                </select>
                <label for="blog_language">Language for new blog posts</label>
            </div>
        </div>

        <?php $image_width = get_user_meta($user_id, 'image_width', true); ?>
        <?php $image_height = get_user_meta($user_id, 'image_height', true); ?>
        <div class="row">
            <div class="input-field col s12 l2">
                <select id="image_width" name="image_width">
                    <option value="" disabled <?= empty($image_width) ? 'selected' : '' ?>>Width</option>
                    <option value="1024" <?= $image_width == '1024' ? 'selected' : '' ?>>1024</option>
                    <option value="1792" <?= $image_width == '1792' ? 'selected' : '' ?>>1792</option>
                </select>
                <label for="image_width">Image width (px)</label>
            </div>

            <div class="input-field col s12 l2">
                <select id="image_height" name="image_height">
                    <option value="" disabled <?= empty($image_height) ? 'selected' : '' ?>>Height</option>
                    <option value="1024" <?= $image_height == '1024' ? 'selected' : '' ?>>1024</option>
                    <option value="1792">1792</option>
                </select>
                <label for="image_height">Image height (px)</label>
            </div>
        </div>

        <?php $website_description = get_user_meta($user_id, 'website_description', true); ?>
        <div class="row">
            <div class="input-field col s12 l8">
                <textarea id="website_description" name="website_description" class="materialize-textarea"><?= htmlspecialchars($website_description) ?></textarea>
                <label for="website_description"><b>Instructions for the writer bot:</b> Describe your website and how you want your posts written.</label>
            </div>
        </div>

        <?php $image_style = get_user_meta($user_id, 'image_style', true); ?>
        <div class="row">
            <div class="input-field col s12 l8">
                <textarea id="image_style" name="image_style" class="materialize-textarea"><?= htmlspecialchars($image_style) ?></textarea>
                <label for="image_style"><b>Instructions for the image generation bot:</b> Describe the style you want for your images.</label>
            </div>
        </div>

        <button class="bifm-btn btn waves-effect waves-light" type="submit" name="action">Update</button>
    </form>
</div>

<div id="warningMessage" class="card-panel yellow darken-2" style="position: fixed; left: 0; bottom: 0; width: 100%; text-align: center; display: none; z-index: 1000;">This is a warning message!</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var elems = document.querySelectorAll(".tabs");
        var instances = M.Tabs.init(elems, {});
        var elems2 = document.querySelectorAll("select");
        var instances2 = M.FormSelect.init(elems2, {});
    });
</script>

<script>var my_script_object = { ajax_url: "<?= esc_js(admin_url('admin-ajax.php')) ?>", nonce: "<?= esc_js($nonce) ?>" };</script>
