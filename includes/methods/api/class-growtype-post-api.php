<?php

class Growtype_Post_Api
{
    public function __construct()
    {
        $this->load_partials();
    }

    public function load_partials()
    {
        include_once GROWTYPE_POST_PATH . '/includes/methods/api/partials/Growtype_Post_Api_Like.php';
        new Growtype_Post_Api_Like();

        include_once GROWTYPE_POST_PATH . '/includes/methods/api/partials/Growtype_Post_Api_Content.php';
        new Growtype_Post_Api_Content();
    }
}
