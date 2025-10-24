<?php

class Growtype_Post_Admin_Methods_Meta
{
    private $loader;

    public function __construct()
    {
        if (get_option('growtype_post_admin_edit_post_show_meta_boxes')) {
            add_action('admin_print_footer_scripts', array ($this, 'custom_admin_footer_script'));

            $this->load_partials();
        }
    }

    public static function latest_custom_data_entered_by_user_transient_key()
    {
        return 'growtype_post_generate_content_last_entered_customer_data_' . get_current_user_id();
    }

    public static function get_user_entered_latest_generate_content_data($post_id = null)
    {
        $latest_generate_content_data = get_transient(self::latest_custom_data_entered_by_user_transient_key());

        if (!empty($post_id)) {
            $post_latest_generate_content_data = get_post_meta($post_id, 'growtype_post_generate_content_last_entered_customer_data', true);

            if (!empty($post_latest_generate_content_data)) {
                $latest_generate_content_data = $post_latest_generate_content_data;
            }
        }

        return $latest_generate_content_data;
    }

    public static function update_user_entered_latest_generate_content_data($params)
    {
        $post_id = isset($params['post_id']) && !empty($params['post_id']) ? $params['post_id'] : null;
        $user_entered_latest_custom_data = self::get_user_entered_latest_generate_content_data($post_id);
        $user_entered_latest_custom_data = !empty($user_entered_latest_custom_data) ? $user_entered_latest_custom_data : [];

        foreach ($params as $key => $param) {
            $user_entered_latest_custom_data[$key] = $param;
        }

        set_transient(self::latest_custom_data_entered_by_user_transient_key(), $user_entered_latest_custom_data, 60 * 60 * 24);

        if (!empty($post_id)) {
            update_post_meta($post_id, 'growtype_post_generate_content_last_entered_customer_data', $user_entered_latest_custom_data);
        }
    }

    public function load_partials()
    {
        require_once GROWTYPE_POST_PATH . 'admin/methods/meta/image/growtype-post-admin-methods-meta-image.php';
        new Growtype_Post_Admin_Methods_Meta_Image();

        require_once GROWTYPE_POST_PATH . 'admin/methods/meta/share/growtype-post-admin-methods-meta-share.php';
        new Growtype_Post_Admin_Methods_Meta_Share();

        require_once GROWTYPE_POST_PATH . 'admin/methods/meta/content/growtype-post-admin-methods-meta-content.php';
        new Growtype_Post_Admin_Methods_Meta_Content();
    }

    function custom_admin_footer_script()
    {
        ?>
        <style>
            .gp-form {
                position: relative;
            }

            .gp-form.is-loading {
                pointer-events: none;
            }

            .gp-form.is-loading:before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255, 255, 255, 0.9);
                z-index: 1;
            }

            .growtype-post-loader-wrapper {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                margin: auto;
                z-index: 1;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-direction: column;
                gap: 20px;
            }

            .growtype-post-loader-text {
                text-align: center;
                display: flex;
                flex-direction: column;
                gap: 10px;
                font-size: 14px;
            }

            .growtype-post-loader-text .loader-step {
                opacity: 0;
                transition: all 0.5s;
            }

            .growtype-post-loader-text .loader-step.is-active {
                opacity: 1;
                transition: all 0.5s;
            }

            .growtype-post-loader-spinner {
                border: 7px solid #f3f3f3; /* Light grey */
                border-top: 7px solid #3498db; /* Blue */
                border-radius: 50%;
                width: 40px;
                height: 40px;
                animation: growtype-post-spin 2s linear infinite;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 1;
            }

            @keyframes growtype-post-spin {
                0% {
                    transform: rotate(0deg);
                }
                100% {
                    transform: rotate(360deg);
                }
            }
        </style>
        <?php
    }
}
