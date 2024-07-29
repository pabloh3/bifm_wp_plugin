<?php 
    require ( __DIR__ . '/../bifm-config.php' );
    function get_all_usernames() {
        $users = get_users();
        $usernames = [];

        foreach ($users as $user) {
            $usernames[] = $user->user_login;
        }

        return $usernames;
    }
?>

<style>
    .input-field {
        margin: 10px;
    }
</style>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<br/>
<a href="admin.php?page=bifm-plugin" class="bifm-btn waves-effect waves-light purple light-grey" style="width: 120px;">
    <i class="material-icons left">arrow_back</i>
    Back
</a>
<div class="container">
    <div id="settings" class="bifm-col s12">
        <h5>Settings</h5>
        <p>Here you can update the settings for blog creation.</p>
        <p>This is the user in your account that will be shown as the author of your blog posts, and whose credentials Billy will use to review and modify the site.</p>
        <form id="bifm-settings-form" action="#" method="post">
            <?php $user_id = get_current_user_id(); ?>
            <?php $username = get_user_meta($user_id, 'username', true); ?>
            <div class="bifm-row">
                <?php $usernames = get_all_usernames(); ?>
                <div class="input-field bifm-col s12 l4">
                    <select id="blog_author_username" name="blog_author_username" class="materialize-select" required>
                        <option value="" disabled <?= is_null($username) ? 'selected' : '' ?>>Choose an author</option>
                        <?php foreach ($usernames as $user): ?>
                            <option value="<?= htmlspecialchars($user) ?>" <?= $user === $username ? 'selected' : '' ?>><?= htmlspecialchars($user) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="blog_author_username">Blog author's username</label>
                </div>
                <!-- if the user is not admin, ask for the password too -->
                <?php if (!current_user_can('manage_options')): ?>
                    <div class="input-field bifm-col s12 l4">
                        <input id="blog_author_password" type="password" name="blog_author_password" class="validate materialize-textarea">
                        <label for="blog_author_password">Blog author's password</label>
                        <div>Will you be credited as the author of Billy's posts? If so, create an "Application Password" <a href="/wp-admin/profile.php">here</a>.<br/>If not, ask your admin for the author's "Application Password".</div>
                    </div>
                <?php endif; ?>
            </div>

            <?php $blog_language = get_user_meta($user_id, 'blog_language', true); ?>
            <div class="bifm-row">
                <div class="input-field bifm-col s12 l4">
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
            <div class="bifm-row">
                <div class="input-field bifm-col s12 l2">
                    <select id="image_width" name="image_width">
                        <option value="" disabled <?= empty($image_width) ? 'selected' : '' ?>>Width</option>
                        <option value="1024" <?= $image_width == '1024' ? 'selected' : '' ?>>1024</option>
                        <option value="1792" <?= $image_width == '1792' ? 'selected' : '' ?>>1792</option>
                    </select>
                    <label for="image_width">Image width (px)</label>
                </div>

                <div class="input-field bifm-col s12 l2">
                    <select id="image_height" name="image_height">
                        <option value="" disabled <?= empty($image_height) ? 'selected' : '' ?>>Height</option>
                        <option value="1024" <?= $image_height == '1024' ? 'selected' : '' ?>>1024</option>
                        <option value="1792">1792</option>
                    </select>
                    <label for="image_height">Image height (px)</label>
                </div>
            </div>

            <?php $website_description = get_user_meta($user_id, 'website_description', true); ?>
            <div class="bifm-row">
                <div class="input-field bifm-col s12 l10">
                    <textarea id="website_description" name="website_description" class="materialize-textarea"><?= htmlspecialchars($website_description) ?></textarea>
                    <label for="website_description"><b>Instructions for the writer bot:</b> Describe your website and how you want your posts written.</label>
                </div>
            </div>

            <?php $image_style = get_user_meta($user_id, 'image_style', true); ?>
            <div class="bifm-row">
                <div class="input-field bifm-col s12 l10">
                    <textarea id="image_style" name="image_style" class="materialize-textarea"><?= htmlspecialchars($image_style) ?></textarea>
                    <label for="image_style"><b>Instructions for the image generation bot:</b> Describe the style you want for your images.</label>
                </div>
            </div>

            <button class="bifm-btn waves-effect waves-light" type="submit" name="action">Update</button>
        </form>
        <div id="warningMessage" class="card-panel yellow darken-2" style="position: relative; left: 0; bottom: 0; width: 100%; text-align: center; display: none; z-index: 1000;">This is a warning message!</div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var elems = document.querySelectorAll(".tabs");
        var instances = M.Tabs.init(elems, {});
        var elems2 = document.querySelectorAll("select");
        var instances2 = M.FormSelect.init(elems2, {});
    });
</script>

