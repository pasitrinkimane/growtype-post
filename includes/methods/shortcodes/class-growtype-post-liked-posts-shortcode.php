<?php

/**
 *
 */
class Growtype_Post_Liked_Posts_Shortcode
{
    function __construct()
    {
        if (!is_admin() && !wp_is_json_request()) {
            add_shortcode('growtype_post_liked_posts', array ($this, 'render_shortcode'));
        }
    }

    /**
     *
     */
    function render_shortcode($args)
    {
        $user_id = $args['user_id'] ?? '';
        $post_type = $args['post_type'] ?? 'any';
        $preview_style = $args['preview_style'] ?? 'basic';
        $columns = $args['columns'] ?? '5';

        $liked_posts = growtype_post_get_user_liked_posts_ids($user_id);

        ob_start();

        if (!empty($liked_posts)) {
            $wp_query = new WP_Query([
                'post_type' => $post_type,
                'post__in' => $liked_posts,
                'posts_per_page' => -1,
                'orderby' => 'post__in',
            ]);

            echo Growtype_Post_Shortcode::render_all(
                $wp_query,
                [
                    'preview_style' => $preview_style,
                    'columns' => $columns,
                    'parent_class' => 'growtype-post-liked-posts-container-wrapper',
                ]
            );
        } else {
            echo '<p class="text-center mt-2 pt-2">' . __('No posts found.', 'growtype-child') . '</p>';
        }

        $content = ob_get_clean();

        return $content ?? '';
    }
}
