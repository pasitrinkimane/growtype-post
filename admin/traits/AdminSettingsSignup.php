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

trait AdminSettingsSignup
{
    /**
     * @return void
     */
    public function signup_content()
    {
        /**
         * Growtype_Post_json_content
         */
        register_setting(
            'Growtype_Post_settings_signup', // settings group name
            'Growtype_Post_signup_json_content' // option name
        );

        add_settings_field(
            'Growtype_Post_signup_json_content',
            'Json Content',
            array ($this, 'Growtype_Post_signup_json_content_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_signup'
        );

        /**
         * Redirect after signup
         */
        register_setting(
            'Growtype_Post_settings_signup', // settings group name
            'Growtype_Post_redirect_after_signup_page', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'Growtype_Post_redirect_after_signup_page',
            '<span style="color: orange;">Redirect After Signup To</span>',
            array ($this, 'Growtype_Post_redirect_after_signup_page_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_signup'
        );

        /**
         * Default user role
         */
        register_setting(
            'Growtype_Post_settings_signup', // settings group name
            'Growtype_Post_default_user_role', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'Growtype_Post_default_user_role',
            '<span style="color: orange;">Default User Role</span>',
            array ($this, 'Growtype_Post_default_user_role_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_signup'
        );

        /**
         * Active user role
         */
        register_setting(
            'Growtype_Post_settings_signup', // settings group name
            'Growtype_Post_active_user_role', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'Growtype_Post_active_user_role',
            '<span style="color: orange;">Active User Role</span>',
            array ($this, 'Growtype_Post_active_user_role_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_signup'
        );

        /**
         * Signup page
         */
        register_setting(
            'Growtype_Post_settings_signup', // settings group name
            'Growtype_Post_signup_page'
        );

        add_settings_field(
            'Growtype_Post_signup_page',
            'Signup Page',
            array ($this, 'Growtype_Post_signup_page_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_signup'
        );

        /**
         * Signup page template
         */
        register_setting(
            'Growtype_Post_settings_signup', // settings group name
            'Growtype_Post_signup_page_template', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'Growtype_Post_signup_page_template',
            'Signup Page Template',
            array ($this, 'Growtype_Post_signup_page_template_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_signup'
        );

        /**
         * Allow simple password
         */
        register_setting(
            'Growtype_Post_settings_signup', // settings group name
            'Growtype_Post_allow_simple_password', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'Growtype_Post_allow_simple_password',
            'Allow simple password',
            array ($this, 'Growtype_Post_allow_simple_password_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_signup'
        );

        /**
         * Terms page
         */
        register_setting(
            'Growtype_Post_settings_signup', // settings group name
            'Growtype_Post_signup_terms_page'
        );

        add_settings_field(
            'Growtype_Post_signup_terms_page',
            '"Terms And Conditions" Page',
            array ($this, 'Growtype_Post_signup_terms_page_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_signup'
        );

        /**
         * Privacy page
         */
        register_setting(
            'Growtype_Post_settings_signup', // settings group name
            'Growtype_Post_signup_privacy_page'
        );

        add_settings_field(
            'Growtype_Post_signup_privacy_page',
            '"Privacy policy" Page',
            array ($this, 'Growtype_Post_signup_privacy_page_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_signup'
        );

        /**
         * Show footer
         */
        register_setting(
            'Growtype_Post_settings_signup', // settings group name
            'Growtype_Post_signup_show_footer', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'Growtype_Post_signup_show_footer',
            'Show Footer in Signup page',
            array ($this, 'Growtype_Post_signup_show_footer_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_signup'
        );

        /**
         * Account requires evaluation
         */
        register_setting(
            'Growtype_Post_settings_signup', // settings group name
            'Growtype_Post_signup_requires_confirmation', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'Growtype_Post_signup_requires_confirmation',
            'Signup requires confirmation',
            array ($this, 'Growtype_Post_signup_requires_confirmation_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_signup'
        );

        /**
         * Platform page
         */
        register_setting(
            'Growtype_Post_settings_signup', // settings group name
            'Growtype_Post_account_verification_platform_page'
        );

        add_settings_field(
            'Growtype_Post_account_verification_platform_page',
            'Platform Page (Main page after account verification to redirect)',
            array ($this, 'Growtype_Post_account_verification_platform_page_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_signup'
        );
    }

    /**
     * General form
     */
    function Growtype_Post_signup_json_content_callback()
    {
        $json_content = get_option('Growtype_Post_signup_json_content');

        if (empty($json_content)) {
            $json_content = file_get_contents(plugin_dir_url(__DIR__) . 'examples/signup.json');
        }
        ?>
        <textarea id="Growtype_Post_signup_json_content" class="Growtype_Post_json_content" name="Growtype_Post_signup_json_content" rows="40" cols="100" style="width: 100%;"><?= $json_content ?></textarea>
        <?php
    }

    /**
     * Register page
     */
    function Growtype_Post_signup_page_callback()
    {
        $selected = Growtype_Post_signup_page_ID();
        $pages = get_pages();
        ?>
        <select name='Growtype_Post_signup_page'>
            <option value='none' <?php selected($selected, 'none'); ?>>None - Growtype Form</option>
            <option value='default' <?php selected($selected, 'default'); ?>>Default - Growtype Form</option>
            <?php
            foreach ($pages as $page) { ?>
                <option value='<?= $page->ID ?>' <?php selected($selected, $page->ID); ?>><?= __($page->post_title, "growtype-post") ?> - Page</option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Terms page
     */
    function Growtype_Post_signup_terms_page_callback()
    {
        $selected = get_option('Growtype_Post_signup_terms_page');
        $pages = get_pages();
        ?>
        <select name='Growtype_Post_signup_terms_page'>
            <option value='none' <?php selected($selected, 'none'); ?>>none</option>
            <?php
            foreach ($pages as $page) { ?>
                <option value='<?= $page->ID ?>' <?php selected($selected, $page->ID); ?>><?= __($page->post_title, "growtype-post") ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Privacy page
     */
    function Growtype_Post_signup_privacy_page_callback()
    {
        $selected = get_option('Growtype_Post_signup_privacy_page');
        $pages = get_pages();
        ?>
        <select name='Growtype_Post_signup_privacy_page'>
            <option value='none' <?php selected($selected, 'none'); ?>>none</option>
            <?php
            foreach ($pages as $page) { ?>
                <option value='<?= $page->ID ?>' <?php selected($selected, $page->ID); ?>><?= __($page->post_title, "growtype-post") ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Login page template
     */
    function Growtype_Post_signup_page_template_callback()
    {
        $selected = Growtype_Post_get_signup_page_template();
        $options = ['template-default', 'template-wide', 'template-2'];
        ?>
        <select name='Growtype_Post_signup_page_template'>
            <?php
            foreach ($options as $option) { ?>
                <option value='<?= $option ?>' <?php selected($selected, $option); ?>><?= $option ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Register page
     */
    function Growtype_Post_redirect_after_signup_page_callback()
    {
        $selected = Growtype_Post_redirect_after_signup_page();
        $pages = get_pages();
        ?>
        <select name='Growtype_Post_redirect_after_signup_page'>
            <option value='none' <?php selected($selected, 'none'); ?>>none</option>
            <option value='default-profile' <?php selected($selected, 'default-profile'); ?>>Default profile page - Growtype Form</option>
            <?php
            foreach ($pages as $page) { ?>
                <option value='<?= $page->ID ?>' <?php selected($selected, $page->ID); ?>><?= __($page->post_title, "growtype-post") ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * User role
     */
    function Growtype_Post_default_user_role_callback()
    {
        global $wp_roles;

        $selected = get_option('Growtype_Post_default_user_role');
        $roles = $wp_roles->roles;
        ?>
        <select name='Growtype_Post_default_user_role'>
            <?php
            foreach ($roles as $role => $role_details) { ?>
                <option value='<?= $role ?>' <?php selected($selected, $role); ?>><?= __($role_details['name'], "growtype-post") ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * User role
     */
    function Growtype_Post_active_user_role_callback()
    {
        global $wp_roles;

        $selected = get_option('Growtype_Post_active_user_role');
        $roles = $wp_roles->roles;
        ?>
        <select name='Growtype_Post_active_user_role'>
            <?php
            foreach ($roles as $role => $role_details) { ?>
                <option value='<?= $role ?>' <?php selected($selected, $role); ?>><?= __($role_details['name'], "growtype-post") ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Allow simple password for signup
     */
    function Growtype_Post_allow_simple_password_callback()
    {
        $enabled = get_option('Growtype_Post_allow_simple_password');
        ?>
        <input type="checkbox" name="Growtype_Post_allow_simple_password" value="1" <?php echo checked(1, $enabled, false) ?> />
        <?php
    }

    /**
     * Show footer
     */
    function Growtype_Post_signup_show_footer_callback()
    {
        $enabled = get_option('Growtype_Post_signup_show_footer');
        ?>
        <input type="checkbox" name="Growtype_Post_signup_show_footer" value="1" <?php echo checked(1, $enabled, false) ?> />
        <?php
    }

    /**
     * Allow simple password for signup
     */
    function Growtype_Post_signup_requires_confirmation_callback()
    {
        $enabled = get_option('Growtype_Post_signup_requires_confirmation');
        ?>
        <input type="checkbox" name="Growtype_Post_signup_requires_confirmation" value="1" <?php echo checked(1, $enabled, false) ?> />
        <?php
    }

    /**
     * Platform page
     */
    function Growtype_Post_account_verification_platform_page_callback()
    {
        $selected = get_option('Growtype_Post_account_verification_platform_page');
        $pages = get_pages();
        ?>
        <select name='Growtype_Post_account_verification_platform_page'>
            <option value='none' <?php selected($selected, 'none'); ?>>none</option>
            <?php
            foreach ($pages as $page) { ?>
                <option value='<?= $page->ID ?>' <?php selected($selected, $page->ID); ?>><?= __($page->post_title, "growtype-post") ?></option>
            <?php } ?>
        </select>
        <?php
    }
}
