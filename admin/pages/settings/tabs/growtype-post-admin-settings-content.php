<?php

class Growtype_Post_Admin_Settings_Content
{
    public function __construct()
    {
        add_action('admin_init', array ($this, 'admin_settings'));

        add_filter('growtype_post_admin_settings_tabs', array ($this, 'settings_tab'));
    }

    function settings_tab($tabs)
    {
        $tabs['content'] = 'Content';

        return $tabs;
    }

    function admin_settings()
    {
        /**
         *
         */
        add_settings_section(
            'growtype_post_settings_content_openai_section_id',
            'OpenAI',
            function () {
                echo '<p>OpenAI settings</p>';
            },
            'growtype_post_settings_content_openai_section'
        );

        /**
         * Client id
         */
        register_setting(
            'growtype_post_settings_content',
            'growtype_post_openai_api_key',
        );

        add_settings_field(
            'growtype_post_openai_api_key',
            'Api Key',
            function () {
                echo $this->render_input('growtype_post_openai_api_key');
            },
            'growtype_post_settings_content_openai_section',
            'growtype_post_settings_content_openai_section_id'
        );
    }

    function render_input($name)
    {
        echo '<input type="text" name="' . $name . '" style="min-width:400px;" value="' . get_option($name) . '" />';
    }
}
