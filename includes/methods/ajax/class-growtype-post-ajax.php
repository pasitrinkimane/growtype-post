<?php

class Growtype_Post_Ajax
{
    public function __construct()
    {
        add_action('wp_ajax_growtype_post_like_post', array ($this, 'growtype_post_like_ajax_callback'));
        add_action('wp_ajax_nopriv_growtype_post_like_post', array ($this, 'growtype_post_like_ajax_callback'));

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
        $args = isset($_POST['args']) ? $_POST['args'] : [];

        if (empty($args)) {
            wp_send_json_error([
                'message' => 'No args provided'
            ], 400);
        }

        $existing_posts_ids = isset($args['existing_posts_ids']) && !empty($args['existing_posts_ids']) ? json_decode(stripslashes($args['existing_posts_ids']), true) : [];
        $amount_to_load = isset($args['amount_to_load']) ? $args['amount_to_load'] : [];
        $post_terms = isset($args['selected_terms_navigation_values']) ? $args['selected_terms_navigation_values'] : [];

        $external_args = array_merge($args, [
            'post__not_in' => $existing_posts_ids,
            'posts_per_page' => $amount_to_load
        ]);

        $formated_args = Growtype_Post_Shortcode::format_args($external_args);

        $formated_args = apply_filters('growtype_post_load_more_posts_args', $formated_args);

        $wp_query_response = Growtype_Post_Shortcode::query_posts($formated_args);

        $formated_args = array_merge($formated_args, $wp_query_response['args'] ?? []);
        $wp_query = $wp_query_response['wp_query'] ?? [];

        $render = Growtype_Post_Shortcode::render_posts(
            $wp_query,
            $formated_args,
            $post_terms
        );

        wp_send_json_success([
            'render' => $render,
            'posts_amount' => count($wp_query->posts)
        ], 200);
    }

    function growtype_post_ajax_load_content_callback()
    {
        $args = isset($_POST['args']) ? $_POST['args'] : [];

        $load_transient = $args['content_cache'] ?? false;

        $transient_content = '';
        if ($load_transient) {
            $transient_name = 'growtype_post_ajax_load_content_callback_transient_' . md5(json_encode($args));
            $transient_content = get_transient($transient_name);
        }

        if (empty($transient_content)) {
            $init = Growtype_Post_Shortcode::init($args);
            $render = $init['render'];
            $posts_amount = count($init['wp_query']->posts);

            if ($load_transient) {
                set_transient($transient_name, $render, 60 * 60);
            }
        }

        wp_send_json_success([
            'render' => $render,
            'posts_amount' => $posts_amount,
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
