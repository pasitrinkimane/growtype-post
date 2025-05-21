<?php

class Growtype_Post_Service
{
    public function __construct()
    {
        require_once GROWTYPE_POST_PATH . 'includes/service/openai/class-growtype-post-service-openai.php';
        new Growtype_Post_Service_Openai();
    }
}
