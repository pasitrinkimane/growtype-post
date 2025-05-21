<?php

class Growtype_Post_Admin_Methods_Share_Medium
{
    public static function share($accounts_details, $post_details)
    {
        $send_details = self::prepare_send_details($post_details);

        $responses = [];
        foreach ($accounts_details as $auth_group_key => $accounts) {
            $credentials = Growtype_Auth::credentials(Growtype_Post_Admin_Methods_Meta_Share::MEDIUM)[$auth_group_key] ?? [];

            foreach ($accounts as $account_channels) {
                foreach ($account_channels as $account_channel) {
                    $already_shared = Growtype_Post_Admin_Methods_Share::check_if_post_is_already_shared_on_platform($post_details['id'], Growtype_Post_Admin_Methods_Meta_Share::MEDIUM, $auth_group_key, $account_channel);

                    $response = [];
                    if (!$already_shared) {
                        $response = self::post($credentials, $send_details);

                        $platform_share_details = Growtype_Post_Admin_Methods_Share::format_already_shared_on_platforms_details(Growtype_Post_Admin_Methods_Meta_Share::MEDIUM, $auth_group_key, $account_channel, $response);

                        if ($response['success']) {
                            $response['auth_group_key'] = $auth_group_key;

                            Growtype_Post_Admin_Methods_Share::update_already_shared_on_platforms_details($post_details['id'], $platform_share_details);
                        }
                    } else {
                        $response['success'] = false;
                        $response['message'] = sprintf('Already shared on %s - %s - %s', ucfirst(Growtype_Post_Admin_Methods_Meta_Share::MEDIUM), $auth_group_key, $account_channel);
                    }

                    $responses[] = $response;
                }
            }
        }

        return $responses;
    }

    public static function prepare_send_details($post_details)
    {
        $post_id = $post_details['id'];
        $post_content = Growtype_Post_Admin_Methods_Share::format_post_content($post_details['content']);
        $post_title = $post_details['title'];
        $post_excerpt = $post_details['excerpt'];
        $featured_img_url = $post_details['featured_img_url'];

        if (!empty($featured_img_url)) {
            $post_content = "<img src='" . $featured_img_url . "' alt='Photo on https://unsplash.com/. Source " . home_url() . "'>" . $post_content;
        }

        /**
         * Resources
         */
        if (!empty($main_meta_title) && !empty($website_domain)) {
            ob_start();
            echo '<h3>🔗 Resources</h3>';
            echo '<ul>';
            echo '<li><a href="' . home_url() . '" target="_blank">' . $website_domain . ' - ' . $main_meta_title . '</a></li>';
            echo '<li><a href="' . get_permalink($post_id) . '" target="_blank">' . $website_domain . ' - Blog</a></li>';
            echo '</ul>';
            $resources = ob_get_clean();
            $post_content = $post_content . "\n\n" . $resources;
        }

        $send_details = [
            'title' => $post_title,
            'body' => $post_content,
            'subtitle' => $post_excerpt,
            'canonicalUrl' => get_permalink(get_page_by_path('blog')),
            'tags' => ['ai', 'chat', 'soulmates', 'assistant', 'chatbot'],
        ];

        return $send_details;
    }

    public static function post($credentials, $post_details)
    {
        try {
            $medium_token = $credentials['token'] ?? '';

            if (empty($medium_token)) {
                return [
                    'success' => false,
                    'error' => 'Medium token not found',
                ];
            }

            $author_id = self::get_author_id($medium_token);

            $title = $post_details['title'];
            $body = $post_details['body'];
            $subtitle = $post_details['subtitle'];
            $canonicalUrl = $post_details['canonicalUrl'];

            $url = "https://api.medium.com/v1/users/{$author_id}/posts";

            $headers = array (
                "Accept: application/json",
                "Content-Type: application/json",
                "Authorization: Bearer " . $medium_token, // Replace YOUR_ACCESS_TOKEN with your actual access token
            );

            $data = array (
                "title" => $title,
                "contentFormat" => "html",
                "content" => $body,
                "canonicalUrl" => $canonicalUrl,
                "publishStatus" => "public",
                "authorId" => $author_id,
                "tags" => $post_details['tags'],
            );

            $curl = curl_init($url);

            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($curl);

            curl_close($curl);

            $response = json_decode($response, true);

            if (isset($response['data']['id'])) {
                $response_url = $response['data']['url'];
                $response_message = sprintf('%s post created successfully. View it <a href="%s" target="_blank">here</a>', ucfirst(Growtype_Post_Admin_Methods_Meta_Share::MEDIUM), $response_url);

                return [
                    'success' => true,
                    'message' => $response_message,
                    'url' => $response_url
                ];
            } else {
                return [
                    'success' => false,
                    'message' => sprintf('%s - %s', ucfirst(Growtype_Post_Admin_Methods_Meta_Share::MEDIUM), implode(',', array_pluck($response['errors'] ?? [], 'message')))
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => sprintf('%s Error: %s', ucfirst(Growtype_Post_Admin_Methods_Meta_Share::MEDIUM), $e->getMessage())
            ];
        }
    }

    public static function get_author_id($token)
    {
        $url = "https://api.medium.com/v1/me";

        $headers = array (
            "Authorization: Bearer " . $token,
        );

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);

        if ($response === false) {
            echo 'Curl error: ' . curl_error($curl);
            return null;
        }

        curl_close($curl);

        $data = json_decode($response, true);

        if (isset($data['data']['id'])) {
            return $data['data']['id'];
        }

        return null;
    }
}
