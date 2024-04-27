<?php

class Growtype_Post_Admin_Methods
{
    public function __construct()
    {
        if (get_option('growtype_post_admin_edit_post_show_meta_boxes')) {
            include_once GROWTYPE_POST_PATH . '/admin/methods/share/growtype-post-admin-methods-share.php';
            new Growtype_Post_Admin_Methods_Share();

            include_once GROWTYPE_POST_PATH . '/admin/methods/meta/growtype-post-admin-methods-meta.php';
            new Growtype_Post_Admin_Methods_Meta();
        }
    }
}
