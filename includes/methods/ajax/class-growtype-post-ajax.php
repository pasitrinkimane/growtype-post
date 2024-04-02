<?php

class Growtype_Post_Ajax
{
    public function __construct()
    {
        add_action('wp_ajax_like_post', array ($this, 'growtype_post_like_ajax_callback'));
        add_action('wp_ajax_nopriv_like_post', array ($this, 'growtype_post_like_ajax_callback'));

        add_action('wp_ajax_get_post_likes', array ($this, 'growtype_post_get_post_likes_ajax_callback'));
        add_action('wp_ajax_nopriv_get_post_likes', array ($this, 'growtype_post_get_post_likes_ajax_callback'));

        add_action('wp_ajax_share_post', array ($this, 'growtype_post_share_post_ajax_callback'));
        add_action('wp_ajax_nopriv_share_post', array ($this, 'growtype_post_share_post_ajax_callback'));

        add_action('wp_ajax_ajax_load_content', array ($this, 'growtype_post_ajax_load_content_callback'));
        add_action('wp_ajax_nopriv_ajax_load_content', array ($this, 'growtype_post_ajax_load_content_callback'));
    }

    function growtype_post_ajax_load_content_callback()
    {
        $args = isset($_POST['args']) ? $_POST['args'] : [];

        ob_start();

        echo Growtype_Post_Shortcode::init($args);

        $render = ob_get_clean();

        wp_send_json([
            'success' => true,
            'render' => $render
        ]);
    }

    function growtype_post_share_post_ajax_callback()
    {
        $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : null;

        wp_send_json([
            'share_url' => get_permalink($post_id)
        ]);
    }

    function growtype_post_like_ajax_callback()
    {
        $action = isset($_POST['action']) ? $_POST['action'] : null;
        $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : null;
        $data_type = isset($_POST['data_type']) ? $_POST['data_type'] : null;

        $likes = growtype_post_like_post($post_id);

        wp_send_json([
            'likes' => count($likes)
        ]);
    }

    function growtype_post_get_post_likes_ajax_callback()
    {
        $action = isset($_POST['action']) ? $_POST['action'] : null;
        $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : null;
        $data_type = isset($_POST['data_type']) ? $_POST['data_type'] : null;

        $ip_key = growtype_post_get_ip_key();

        $likes = growtype_post_get_post_likes($post_id);

        wp_send_json([
            'likes' => count($likes),
            'liked' => in_array($ip_key, $likes)
        ]);
    }
}
