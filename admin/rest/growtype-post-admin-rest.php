<?php

class Growtype_Post_Admin_Rest
{
    public function __construct()
    {
        add_action('growtype_auth_return_google_token_details', [$this, 'set_google_token_details'], 0, 2);
        add_action('growtype_auth_return_tumblr_token_details', [$this, 'set_tumblr_token_details'], 0, 2);
        add_action('growtype_auth_return_threads_token_details', [$this, 'set_threads_token_details'], 0, 2);

        add_action('rest_api_init', function () {
            // Register the REST API route
            register_rest_route('growtype-post/v1', '/post/import', [
                'methods' => WP_REST_Server::CREATABLE, // Allow POST requests
                'callback' => [$this, 'rest_post_callback'],
                'permission_callback' => array ($this, 'permission_check_callback')
            ]);
        });
    }

    function permission_check_callback()
    {
        return true;
    }

    function rest_post_callback($data)
    {
        $params = $data->get_params();

        // Validate required fields
        if (isset($params['title']) && isset($params['content'])) {
            $post_data = [
                'post_title' => sanitize_text_field($params['title']),
                'post_content' => wp_kses_post($params['content']),
                'post_status' => 'publish',
                'post_excerpt' => isset($params['excerpt']) ? sanitize_text_field($params['excerpt']) : '',
            ];

            // Insert the post
            $post_id = wp_insert_post($post_data);

            if (is_wp_error($post_id)) {
                return new WP_REST_Response(['error' => 'Failed to create post'], 500);
            }

            // Handle featured image
            if (isset($params['featured_img_url']) && filter_var($params['featured_img_url'], FILTER_VALIDATE_URL)) {
                $attachment_id = self::download_and_attach_image($params['featured_img_url'], $post_id);
                if (is_wp_error($attachment_id)) {
                    return new WP_REST_Response(['warning' => 'Post created, but failed to set featured image'], 200);
                }

                set_post_thumbnail($post_id, $attachment_id);
            }

            return new WP_REST_Response([
                'message' => 'Post created successfully',
                'post_id' => $post_id,
                'url' => get_permalink($post_id),
            ], 200);
        }

        return new WP_REST_Response(['error' => 'Invalid data'], 400);
    }

    function set_google_token_details($token_details, $state)
    {
        $credentials_group_key = $state['credentials_group_key'];
        $meta_key = sprintf('growtype_post_google_auth_%s_details', $credentials_group_key);
        update_user_meta(get_current_user_id(), $meta_key, $token_details);
    }

    function set_tumblr_token_details($token_details, $state)
    {
        $credentials_group_key = $state['credentials_group_key'];
        $meta_key = sprintf('growtype_post_tumblr_auth_%s_details', $credentials_group_key);
        update_user_meta(get_current_user_id(), $meta_key, $token_details);
    }

    function set_threads_token_details($token_details, $state)
    {
        $credentials_group_key = $state['credentials_group_key'];
        $meta_key = sprintf('growtype_post_threads_auth_%s_details', $credentials_group_key);

        $existing_tokens = get_user_meta(get_current_user_id(), $meta_key, true);
        $existing_tokens = !empty($existing_tokens) ? $existing_tokens : [];

        $user_details = self::get_threads_user_details($token_details['access_token']);
        $user_details['access_token'] = $token_details['access_token'];

        $existing_tokens[$user_details['username']] = $user_details;

        update_user_meta(get_current_user_id(), $meta_key, $existing_tokens);
    }

    public static function get_threads_user_details($accessToken)
    {
        $ch = curl_init();

        // Set the API endpoint URL
        $url = "https://graph.threads.net/v1.0/me?fields=id,username&access_token={$accessToken}";

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the request
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
            curl_close($ch);
            return null;
        }

        // Close cURL session
        curl_close($ch);

        // Decode the JSON response
        $data = json_decode($response, true);

        return $data ?? '';
    }

    /**
     * Download and attach an image to a post.
     */
    function download_and_attach_image($image_url, $post_id)
    {
        // Ensure required WordPress file functions are loaded
        if (!function_exists('download_url')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        if (!function_exists('media_handle_sideload')) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
        }
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $temp_file = download_url($image_url);

        if (is_wp_error($temp_file)) {
            return $temp_file;
        }

        // File name for the attachment
        $file_array = [
            'name' => basename($image_url),
            'tmp_name' => $temp_file,
        ];

        // Check file type and handle the sideload
        $attachment_id = media_handle_sideload($file_array, $post_id);

        if (is_wp_error($attachment_id)) {
            @unlink($temp_file); // Clean up temporary file
            return $attachment_id;
        }

        return $attachment_id;
    }
}
