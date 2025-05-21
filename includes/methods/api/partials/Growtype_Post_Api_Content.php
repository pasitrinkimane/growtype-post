<?php

class Growtype_Post_Api_Content
{
    public function __construct()
    {
        add_action('rest_api_init', array ($this, 'register_routes'));
    }

    public function register_routes()
    {
        register_rest_route(
            'growtype-post/v1',
            '/generate/content',
            array (
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array ($this, 'generate_content'),
                'permission_callback' => function () {
                    return current_user_can('read'); // Adjust capability as needed
                },
            )
        );
    }

    public function generate_content(WP_REST_Request $request)
    {
        // Retrieve request parameters
        $data = $request->get_params();

        // Validate required parameters
        $post_title = isset($data['title']) ? sanitize_text_field($data['title']) : '';
        $post_content = isset($data['content']) ? sanitize_textarea_field($data['content']) : '';
        $category_name = isset($data['category_name']) ? sanitize_text_field($data['category_name']) : '';

        if (empty($post_title) || empty($post_content)) {
            return new WP_REST_Response(
                array (
                    'error' => 'Title and content are required.',
                ),
                400 // Bad Request
            );
        }

        // Retrieve the category ID by name
        $category = get_term_by('name', $category_name, 'category');

        if (!$category) {
            return new WP_REST_Response(
                array (
                    'error' => "Category '{$category_name}' does not exist.",
                ),
                400 // Bad Request
            );
        }

        $category_id = $category->term_id;

        // Prepare post data
        $post_data = array (
            'post_title' => $post_title,
            'post_content' => $post_content,
            'post_status' => 'draft', // Set to 'draft' if you don't want it published immediately
            'post_author' => get_current_user_id(), // Use the current logged-in user as the author
            'post_category' => array ($category_id), // Assign the category by ID
        );

        // Insert the post
        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            return new WP_REST_Response(
                array (
                    'error' => 'Failed to create the post. Please try again.',
                    'details' => $post_id->get_error_message(),
                ),
                500 // Internal Server Error
            );
        }

        return new WP_REST_Response(
            array (
                'message' => 'Successfully created the post.',
                'post_id' => $post_id,
                'post_url' => get_permalink($post_id),
            ),
            200 // OK
        );
    }
}
