<?php

class Growtype_Post_Admin_Settings_Social
{
    public function __construct()
    {
        add_action('admin_init', array ($this, 'admin_settings'));
        add_filter('growtype_post_admin_settings_tabs', array ($this, 'settings_tab'));
    }

    function settings_tab($tabs)
    {
//        $tabs['social'] = 'Social';
        return $tabs;
    }

    function admin_settings()
    {
        /**
         *
         */
        add_settings_section(
            'growtype_form_settings_social_reddit_section_id',
            'Reddit',
            function () {
                echo '<p>Reddit settings</p>';
            },
            'growtype_form_settings_social_reddit_section'
        );

        /**
         * Client id
         */
        register_setting(
            'growtype_post_settings_social',
            'growtype_post_reddit_client_id',
        );

        add_settings_field(
            'growtype_post_reddit_client_id',
            'Client ID',
            function () {
                echo $this->render_input('growtype_post_reddit_client_id');
            },
            'growtype_form_settings_social_reddit_section',
            'growtype_form_settings_social_reddit_section_id'
        );

        /**
         * client_secret
         */
        register_setting(
            'growtype_post_settings_social',
            'growtype_post_reddit_client_secret',
        );

        add_settings_field(
            'growtype_post_reddit_client_secret',
            'Client Secret',
            function () {
                echo $this->render_input('growtype_post_reddit_client_secret');
            },
            'growtype_form_settings_social_reddit_section',
            'growtype_form_settings_social_reddit_section_id'
        );

        /**
         * username
         */
        register_setting(
            'growtype_post_settings_social',
            'growtype_post_reddit_username',
        );

        add_settings_field(
            'growtype_post_reddit_username',
            'Username',
            function () {
                echo $this->render_input('growtype_post_reddit_username');
            },
            'growtype_form_settings_social_reddit_section',
            'growtype_form_settings_social_reddit_section_id'
        );

        /**
         * password
         */
        register_setting(
            'growtype_post_settings_social',
            'growtype_post_reddit_password',
        );

        add_settings_field(
            'growtype_post_reddit_password',
            'Password',
            function () {
                echo $this->render_input('growtype_post_reddit_password');
            },
            'growtype_form_settings_social_reddit_section',
            'growtype_form_settings_social_reddit_section_id'
        );

        /**
         * password
         */
        register_setting(
            'growtype_post_settings_social',
            'growtype_post_reddit_default_subreddits',
        );

        add_settings_field(
            'growtype_post_reddit_default_subreddits',
            'Default Subreddits (separated by comma)',
            function () {
                echo $this->render_input('growtype_post_reddit_default_subreddits');
            },
            'growtype_form_settings_social_reddit_section',
            'growtype_form_settings_social_reddit_section_id'
        );

        /**
         *
         */
        add_settings_section(
            'growtype_form_settings_social_google_section_id',
            'Google',
            function () {
                echo '<p>Google settings</p>';
            },
            'growtype_form_settings_social_google_section'
        );

        /**
         * Client id
         */
        register_setting(
            'growtype_post_settings_social',
            'growtype_post_google_client_id',
        );

        add_settings_field(
            'growtype_post_google_client_id',
            'Client ID',
            function () {
                echo $this->render_input('growtype_post_google_client_id');
            },
            'growtype_form_settings_social_google_section',
            'growtype_form_settings_social_google_section_id'
        );

        /**
         * client_secret
         */
        register_setting(
            'growtype_post_settings_social',
            'growtype_post_google_client_secret',
        );

        add_settings_field(
            'growtype_post_google_client_secret',
            'Client Secret',
            function () {
                echo $this->render_input('growtype_post_google_client_secret');
            },
            'growtype_form_settings_social_google_section',
            'growtype_form_settings_social_google_section_id'
        );

        /**
         * client_secret
         */
        register_setting(
            'growtype_post_settings_social',
            'growtype_post_google_blogger_default_blogs_ids',
        );

        add_settings_field(
            'growtype_post_google_blogger_default_blogs_ids',
            'Blogger Default Blogs ids (separated by comma)',
            function () {
                echo $this->render_input('growtype_post_google_blogger_default_blogs_ids');
            },
            'growtype_form_settings_social_google_section',
            'growtype_form_settings_social_google_section_id'
        );

        /**
         *
         */
        add_settings_section(
            'growtype_form_settings_social_medium_section_id',
            'Medium',
            function () {
                echo '<p>Medium settings</p>';
            },
            'growtype_form_settings_social_medium_section'
        );

        /**
         * Token
         */
        register_setting(
            'growtype_post_settings_social',
            'growtype_post_medium_token',
        );

        add_settings_field(
            'growtype_post_medium_token',
            'Token',
            function () {
                echo $this->render_input('growtype_post_medium_token');
            },
            'growtype_form_settings_social_medium_section',
            'growtype_form_settings_social_medium_section_id'
        );
    }

    function render_input($name)
    {
        echo '<input type="text" name="' . $name . '" style="min-width:400px;" value="' . get_option($name) . '" />';
    }
}
