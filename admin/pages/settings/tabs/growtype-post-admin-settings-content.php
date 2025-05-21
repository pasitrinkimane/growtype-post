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
//        $tabs['content'] = 'Content';
        return $tabs;
    }

    function admin_settings()
    {
    }
}
