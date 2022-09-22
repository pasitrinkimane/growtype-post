<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Growtype_Post
 * @subpackage Growtype_Post/admin/partials
 */

trait AdminSettingsLogin
{
    public function login_content()
    {
        /**
         * Growtype_Post_json_content
         */
        register_setting(
            'Growtype_Post_settings_login', // settings group name
            'Growtype_Post_login_json_content' // option name
        );

        add_settings_field(
            'Growtype_Post_login_json_content',
            'Json Content',
            array ($this, 'Growtype_Post_login_json_content_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_login'
        );

        /**
         * Login page
         */
        register_setting(
            'Growtype_Post_settings_login',
            'Growtype_Post_login_page'
        );

        add_settings_field(
            'Growtype_Post_login_page',
            'Login Page',
            array ($this, 'Growtype_Post_login_page_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_login'
        );

        /**
         * Login page template
         */
        register_setting(
            'Growtype_Post_settings_login', // settings group name
            'Growtype_Post_login_page_template', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'Growtype_Post_login_page_template',
            'Login Form Template',
            array ($this, 'Growtype_Post_login_page_template_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_login'
        );

        /**
         * Redirect after login
         */
        register_setting(
            'Growtype_Post_settings_login', // settings group name
            'Growtype_Post_redirect_after_login_page', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'Growtype_Post_redirect_after_login_page',
            'Redirect After Login To',
            array ($this, 'Growtype_Post_redirect_after_login_page_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_login'
        );

        /**
         * Show footer
         */
        register_setting(
            'Growtype_Post_settings_login', // settings group name
            'Growtype_Post_login_show_footer', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'Growtype_Post_login_show_footer',
            'Show Footer',
            array ($this, 'Growtype_Post_login_show_footer_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_login'
        );
    }

    /**
     * General form
     */
    function Growtype_Post_login_json_content_callback()
    {
        $json_content = get_option('Growtype_Post_login_json_content');

        if (empty($json_content)) {
            $json_content = file_get_contents(plugin_dir_url(__DIR__) . 'examples/login.json');
        }
        ?>
        <textarea id="Growtype_Post_login_json_content" class="Growtype_Post_json_content" name="Growtype_Post_login_json_content" rows="40" cols="100" style="width: 100%;"><?= $json_content ?></textarea>
        <?php
    }

    /**
     * Login page
     */
    function Growtype_Post_login_page_callback()
    {
        $selected = Growtype_Post_login_page_ID();
        $pages = get_pages();
        ?>
        <select name='Growtype_Post_login_page'>
            <option value='none' <?php selected($selected, 'none'); ?>>None - Growtype form</option>
            <option value='default' <?php selected($selected, 'default'); ?>>Default - Growtype form</option>
            <?php
            foreach ($pages as $page) { ?>
                <option value='<?= $page->ID ?>' <?php selected($selected, $page->ID); ?>><?= __($page->post_title, "growtype-post") ?> - Page</option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Login page template
     */
    function Growtype_Post_login_page_template_callback()
    {
        $selected = Growtype_Post_get_login_page_template();
        $options = ['template-default', 'template-wide', 'template-2'];
        ?>
        <select name='Growtype_Post_login_page_template'>
            <?php
            foreach ($options as $option) { ?>
                <option value='<?= $option ?>' <?php selected($selected, $option); ?>><?= $option ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Redirect after login page
     */
    function Growtype_Post_redirect_after_login_page_callback()
    {
        $selected = Growtype_Post_redirect_after_login_page();
        $pages = get_pages();
        ?>
        <select name='Growtype_Post_redirect_after_login_page'>
            <option value='none' <?php selected($selected, 'none'); ?>>None - Growtype Form</option>
            <option value='default-profile' <?php selected($selected, 'default-profile'); ?>>Default profile page - Growtype Form</option>
            <option value='dashboard' <?php selected($selected, 'dashboard'); ?>>Dashboard</option>
            <?php
            foreach ($pages as $page) { ?>
                <option value='<?= $page->ID ?>' <?php selected($selected, $page->ID); ?>><?= __($page->post_title, "growtype-post") ?> - Page</option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Login show footer
     */
    function Growtype_Post_login_show_footer_callback()
    {
        $enabled = get_option('Growtype_Post_login_show_footer');
        ?>
        <input type="checkbox" name="Growtype_Post_login_show_footer" value="1" <?php echo checked(1, $enabled, false) ?> />
        <?php
    }
}


