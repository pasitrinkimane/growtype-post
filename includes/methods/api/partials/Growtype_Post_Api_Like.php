<?php

class Growtype_Post_Api_Like
{
    public function __construct()
    {
        add_action('rest_api_init', array (
            $this,
            'register_routes'
        ));
    }

    function register_routes()
    {
        register_rest_route('growtype-post/v1', 'post/like/(?P<id>\d+)', array (
            'methods' => 'GET',
            'callback' => array (
                $this,
                'like_post_callback'
            ),
            // SECURITY: Require authentication for liking posts
            'permission_callback' => function () {
                return is_user_logged_in();
            }
        ));
    }

    function like_post_callback($data)
    {
        $post_id = $data['id'] ?? '';

        if (empty($post_id)) {
            return wp_send_json([
                'data' => 'Post id is required',
            ], 400);
        }

        $likes = growtype_post_like_post($post_id);

        return wp_send_json([
            'message' => 'Post liked',
            'likes' => count($likes)
        ], 200);
    }
}
