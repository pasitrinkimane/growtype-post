<?php

class Growtype_Post_Admin_Methods_Meta
{
    public function __construct()
    {
        if (get_option('growtype_post_admin_edit_post_show_meta_boxes')) {
            add_action('admin_print_footer_scripts', array ($this, 'custom_admin_footer_script'));

            $this->load_partials();
        }
    }

    public function load_partials()
    {
        require_once GROWTYPE_POST_PATH . 'admin/methods/meta/partials/growtype-post-admin-methods-meta-image.php';
        $this->loader = new Growtype_Post_Admin_Methods_Meta_Image();

        require_once GROWTYPE_POST_PATH . 'admin/methods/meta/partials/growtype-post-admin-methods-meta-share.php';
        $this->loader = new Growtype_Post_Admin_Methods_Meta_Share();

        require_once GROWTYPE_POST_PATH . 'admin/methods/meta/partials/growtype-post-admin-methods-meta-content.php';
        $this->loader = new Growtype_Post_Admin_Methods_Meta_Content();
    }

    function custom_admin_footer_script()
    {
        ?>
        <script type="text/javascript">
            function growtype_post_admin_show_notice(data) {
                let message = data.message && data.message.length > 0 ? data.message : 'Data was updated.';

                if (data.url) {
                    message = message + ' <a href="' + data.url + '" target="_blank">View</a>';
                }

                $('body').append('<div class="notice ' + (data.success === false ? 'notice-error' : 'notice-success') + ' is-dismissible" style="position: fixed;top: 5%;right: 5%;z-index: 9999999;padding-top: 10px;padding-bottom: 10px;">' + message + '</div>')

                setTimeout(function () {
                    $('body .notice.is-dismissible').remove();
                }, 6000);

                if (data.redirectURL) {
                    console.log(data.redirectURL)
                    if (window.confirm('Auth is required. Do you want to proceed?')) {
                        window.location.href = data.redirectURL;
                    }
                }
            }
        </script>
        <?php
    }
}
