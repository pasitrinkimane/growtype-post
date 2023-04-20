<?php

class Growtype_Post_Admin_Pages
{
    public function __construct()
    {
        $this->load_pages();
    }

    public function load_pages()
    {
        /**
         * Settings
         */
        require_once 'settings/growtype-post-admin-settings.php';
        new Growtype_Post_Admin_Settings();
    }
}
