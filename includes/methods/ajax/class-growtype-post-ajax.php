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
    }

    function growtype_post_share_post_ajax_callback()
    {
        $action = isset($_POST['action']) ? $_POST['action'] : null;
        $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : null;
        $data_type = isset($_POST['data_type']) ? $_POST['data_type'] : null;

        wp_send_json([
            'share_url' => get_permalink($post_id)
        ]);
    }

    function growtype_post_like_ajax_callback()
    {
        $action = isset($_POST['action']) ? $_POST['action'] : null;
        $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : null;
        $data_type = isset($_POST['data_type']) ? $_POST['data_type'] : null;

        $ip_key = growtype_post_get_ip_key();

        $likes = $this->growtype_post_likes_data($post_id);

        if (!empty($action) && !empty($post_id)) {
            if (!in_array($ip_key, $likes)) {
                array_push($likes, $ip_key);
                update_post_meta($post_id, 'growtype_post_likes', $likes);
            } else {
                $likes = array_diff($likes, [$ip_key]);
                update_post_meta($post_id, 'growtype_post_likes', $likes);
            }
        }

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

        $likes = $this->growtype_post_likes_data($post_id);

        wp_send_json([
            'likes' => count($likes),
            'liked' => in_array($ip_key, $likes)
        ]);
    }

    public static function growtype_post_likes_data($post_id)
    {
        $likes = get_post_meta($post_id, 'growtype_post_likes', true);

        if (empty($likes)) {
            $likes = [];
        }

        return $likes;
    }
}
