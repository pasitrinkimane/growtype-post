<?php

class Growtype_Post_Admin_Settings_General
{
    public function __construct()
    {
        add_action('admin_init', array ($this, 'admin_settings'));

        add_filter('growtype_post_admin_settings_tabs', array ($this, 'settings_tab'));
    }

    function settings_tab($tabs)
    {
        $tabs['general'] = 'General';

        return $tabs;
    }

    function admin_settings()
    {
        /**
         * Date format
         */
        register_setting(
            'growtype_post_settings_general', // settings group name
            'growtype_post_date_format', // option name
//            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'growtype_post_date_format',
            'Date Format',
            array ($this, 'growtype_post_date_format_callback'),
            Growtype_Post_Admin::SETTINGS_PAGE_SLUG,
            'growtype_post_settings_general_render'
        );

        /**
         * Date format
         */
        register_setting(
            'growtype_post_settings_general', // settings group name
            'growtype_post_admin_edit_post_show_meta_boxes', // option name
//            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'growtype_post_admin_edit_post_show_meta_boxes',
            'Show meta boxes in admin edit post',
            array ($this, 'growtype_post_admin_edit_post_show_meta_boxes_callback'),
            Growtype_Post_Admin::SETTINGS_PAGE_SLUG,
            'growtype_post_settings_general_render'
        );
    }

    /**
     * Input
     */
    function growtype_post_date_format_callback()
    {
        $html = '<input type="text" name="growtype_post_date_format" style="min-width:400px;" value="' . get_option('growtype_post_date_format') . '" />';
        echo $html;
    }

    /**
     * Input checkbox
     */
    function growtype_post_admin_edit_post_show_meta_boxes_callback()
    {
        ?>
        <input type='checkbox' name='growtype_post_admin_edit_post_show_meta_boxes' value='1'
            <?php if (1 == get_option('growtype_post_admin_edit_post_show_meta_boxes')) {
                echo 'checked="checked"';
            } ?> />

        <?php
    }
}
