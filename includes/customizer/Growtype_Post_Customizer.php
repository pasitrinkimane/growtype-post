<?php

class Growtype_Post_Customizer
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('customize_register', array ($this, 'customizer_init'));
    }

    function customizer_init($wp_customize)
    {
        if (!class_exists('Skyrocket_Simple_Notice_Custom_control')) {
            return;
        }

        require_once GROWTYPE_POST_PATH . 'includes/customizer/tabs/content.php';
        require_once GROWTYPE_POST_PATH . 'includes/customizer/tabs/preview.php';
    }
}
