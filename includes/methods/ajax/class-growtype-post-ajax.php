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

        add_action('wp_ajax_growtype_post_ajax_load_content', array ($this, 'growtype_post_ajax_load_content_callback'));
        add_action('wp_ajax_nopriv_growtype_post_ajax_load_content', array ($this, 'growtype_post_ajax_load_content_callback'));

        add_action('wp_ajax_growtype_post_load_more_posts', array ($this, 'growtype_post_load_more_posts_callback'));
        add_action('wp_ajax_nopriv_growtype_post_load_more_posts', array ($this, 'growtype_post_load_more_posts_callback'));
    }

    function growtype_post_load_more_posts_callback()
    {
        $existing_posts_ids = isset($_POST['existing_posts_ids']) && !empty($_POST['existing_posts_ids']) ? json_decode(stripslashes($_POST['existing_posts_ids']), true) : [];
        $amount_to_show = isset($_POST['amount_to_show']) ? $_POST['amount_to_show'] : [];
        $amount_to_load = isset($_POST['amount_to_load']) ? $_POST['amount_to_load'] : [];
        $filter_params = isset($_POST['filter_params']) && !empty($_POST['filter_params']) ? json_decode(stripslashes($_POST['filter_params']), true) : [];
        $preview_style = isset($_POST['preview_style']) ? $_POST['preview_style'] : 'basic';

        $args = Growtype_Post_Shortcode::format_args([
            'post__not_in' => $existing_posts_ids,
            'posts_per_page' => $amount_to_load,
            'preview_style' => $preview_style,
        ]);

        $args = apply_filters('growtype_post_load_more_posts_args', $args);

        $wp_query_response = Growtype_Post_Shortcode::query_posts($args);
        $args = array_merge($args, $wp_query_response['args'] ?? []);
        $wp_query = $wp_query_response['wp_query'] ?? [];

        $render = Growtype_Post_Shortcode::render_posts(
            $wp_query,
            $args
        );

        wp_send_json_success([
            'render' => $render
        ], 200);
    }

    function growtype_post_ajax_load_content_callback()
    {
        $args = isset($_POST['args']) ? $_POST['args'] : [];

        $render = Growtype_Post_Shortcode::init($args);

        wp_send_json_success([
            'render' => $render
        ], 200);
    }

    function growtype_post_share_post_ajax_callback()
    {
        $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : null;

        wp_send_json_success([
            'share_url' => get_permalink($post_id)
        ], 200);
    }

    function growtype_post_like_ajax_callback()
    {
        $action = isset($_POST['action']) ? $_POST['action'] : null;
        $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : null;
        $data_type = isset($_POST['data_type']) ? $_POST['data_type'] : null;

        $likes = growtype_post_like_post($post_id);

        wp_send_json_success([
            'likes' => count($likes)
        ], 200);
    }

    function growtype_post_get_post_likes_ajax_callback()
    {
        $action = isset($_POST['action']) ? $_POST['action'] : null;
        $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : null;
        $data_type = isset($_POST['data_type']) ? $_POST['data_type'] : null;

        $ip_key = growtype_post_get_ip_key();

        $likes = growtype_post_get_post_likes($post_id);

        wp_send_json_success([
            'likes' => count($likes),
            'liked' => in_array($ip_key, $likes)
        ], 200);
    }
}
