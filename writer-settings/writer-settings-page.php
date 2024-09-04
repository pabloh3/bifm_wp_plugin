<?php 
if ( ! defined( 'ABSPATH' ) ) exit;
    require ( __DIR__ . '/../bifm-config.php' );
    function bifm_get_all_usernames() {
        $users = get_users();
        $usernames = [];

        foreach ($users as $user) {
            $usernames[] = $user->user_login;
        }

        return $usernames;
    }
?>
 
<br/>
<a href="admin.php?page=bifm" class="bifm-btn waves-effect waves-light purple light-grey" style="width: 120px;">
    <i class="material-icons left">arrow_back</i>
    <?php esc_html_e('Back','bifm'); ?>
</a>
<div class="container">
    <div id="settings" class="bifm-col s12">
        <h5><?php esc_html_e('Settings','bifm'); ?></h5>
        <p><?php esc_html_e('Here you can update the settings for blog creation.','bifm'); ?></p>
        <p><?php esc_html_e('This is the user in your account that will be shown as the author of your blog posts, and whose credentials Billy will use to review and modify the site.','bifm'); ?></p>
        <form id="bifm-settings-form" action="#" method="post">
            <?php $user_id = get_current_user_id(); ?>
            <?php $username = get_user_meta($user_id, 'username', true); ?>
            <div class="bifm-row">
                <?php $usernames = bifm_get_all_usernames(); ?>
                <div class="input-field bifm-col s12 l4">
                    <select id="blog_author_username" name="blog_author_username" class="materialize-select" required>
                        <option value="" disabled <?php echo is_null($username) ? 'selected' : '' ?>><?php esc_html_e('Choose an author','bifm'); ?></option>
                        <?php foreach ($usernames as $user): ?>
                            <option value="<?php echo esc_attr($user) ?>" <?php echo $user === $username ? 'selected' : '' ?>><?php echo esc_attr($user) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="blog_author_username"><?php esc_html_e('Blog author\'s username','bifm'); ?></label>
                </div>
                <!-- if the user is not admin, ask for the password too -->
                <?php if (!current_user_can('manage_options')): ?>
                    <div class="input-field bifm-col s12 l4">
                        <input id="blog_author_password" type="password" name="blog_author_password" class="validate materialize-textarea">
                        <label for="blog_author_password"><?php esc_html_e('Blog author\'s password','bifm'); ?></label>
                        <div><?php 
                            $message = sprintf(
                                __('Will you be credited as the author of Billy\'s posts? If so, create an "Application Password" <a href="%s">here</a>.<br/>If not, ask your admin for the author\'s "Application Password". This will be shared with Billy so it can access your site to create and review content and configuration.', 'bifm'),
                                '/wp-admin/profile.php'
                            );
                            echo wp_kses($message, ['a' => ['href' => []], 'br' => []]);
                            /* translators: %s: user profile page URL */?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="input-field bifm-col s12 l4">
                        <p><?php esc_html_e('Saving will generate an application password for the user you selected. This will be shared with Billy so it can access your site to create and review content and configuration.','bifm'); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <?php $blog_language = get_user_meta($user_id, 'blog_language', true); ?>
            <div class="bifm-row">
            <div class="input-field bifm-col s12 l4">
                <select id="blog_language" name="blog_language">
                    <option value="" disabled <?php echo empty($blog_language) ? 'selected' : '' ?>><?php esc_html_e('Choose your option','bifm'); ?></option>
                    <option value="english" <?php echo $blog_language == 'english' ? 'selected' : '' ?>><?php esc_html_e('English','bifm'); ?></option>
                    <option value="spanish" <?php echo $blog_language == 'spanish' ? 'selected' : '' ?>><?php esc_html_e('Spanish','bifm'); ?></option>
                    <option value="other" <?php echo !in_array($blog_language, ['english', 'spanish']) ? 'selected' : '' ?>><?php esc_html_e('Other','bifm'); ?></option>
                </select>
                <label for="blog_language"><?php esc_html_e('Language for new blog posts','bifm'); ?></label>
            </div>
            <div id="other_language_wrapper" class="input-field bifm-col s12 l4" style="display: <?php echo !in_array($blog_language, ['english', 'spanish']) ? 'block' : 'none'; ?>;">
                <input id="other_blog_language" type="text" name="other_blog_language" value="<?php echo !in_array($blog_language, ['english', 'spanish']) ? esc_attr($blog_language) : ''; ?>" />
                <label for="other_blog_language"><?php esc_html_e('Please specify your language','bifm'); ?></label>
            </div>
            </div>

            <?php $image_width = get_user_meta($user_id, 'image_width', true); ?>
            <?php $image_height = get_user_meta($user_id, 'image_height', true); ?>
            <div class="bifm-row">
                <div class="input-field bifm-col s12 l2">
                    <select id="image_width" name="image_width">
                        <option value="" disabled <?php echo empty($image_width) ? 'selected' : '' ?>><?php esc_html_e('Width','bifm'); ?></option>
                        <option value="1024" <?php echo $image_width == '1024' ? 'selected' : '' ?>><?php esc_html_e('1024','bifm'); ?></option>
                        <option value="1792" <?php echo $image_width == '1792' ? 'selected' : '' ?>><?php esc_html_e('1792','bifm'); ?></option>
                    </select>
                    <label for="image_width"><?php esc_html_e('Image width (px)','bifm'); ?></label>
                </div>

                <div class="input-field bifm-col s12 l2">
                    <select id="image_height" name="image_height">
                        <option value="" disabled <?php echo empty($image_height) ? 'selected' : '' ?>><?php esc_html_e('Height','bifm'); ?></option>
                        <option value="1024" <?php echo $image_height == '1024' ? 'selected' : '' ?>><?php esc_html_e('1024','bifm'); ?></option>
                        <option value="1792" <?php echo $image_height == '1792' ? 'selected' : '' ?>><?php esc_html_e('1792','bifm'); ?></option>
                    </select>
                    <label for="image_height"><?php esc_html_e('Image height (px)','bifm'); ?></label>
                </div>
            </div>

            <?php $website_description = get_user_meta($user_id, 'website_description', true); ?>
            <div class="bifm-row">
                <div class="input-field bifm-col s12 l10">
                    <textarea id="website_description" name="website_description" class="materialize-textarea"><?php echo esc_textarea($website_description) ?></textarea>
                    <label for="website_description"><b><?php esc_html_e('Instructions for the writer bot:','bifm'); ?></b> <?php esc_html_e('Describe your website and how you want your posts written.','bifm'); ?></label>
                </div>
            </div>

            <?php $image_style = get_user_meta($user_id, 'image_style', true); ?>
            <div class="bifm-row">
                <div class="input-field bifm-col s12 l10">
                    <textarea id="image_style" name="image_style" class="materialize-textarea"><?php echo esc_textarea($image_style) ?></textarea>
                    <label for="image_style"><b><?php esc_html_e('Instructions for the image generation bot:','bifm'); ?></b> <?php esc_html_e('Describe the style you want for your images.','bifm'); ?></label>
                </div>
            </div>

            <button class="bifm-btn waves-effect waves-light" type="submit" name="action"><?php esc_html_e('Update','bifm'); ?></button>
        </form>
        <div id="warningMessage" class="card-panel yellow darken-2" style="position: relative; left: 0; bottom: 0; width: 100%; text-align: center; display: none; z-index: 1000;"><?php esc_html_e('This is a warning message!','bifm'); ?></div>
    </div>
</div>
 

