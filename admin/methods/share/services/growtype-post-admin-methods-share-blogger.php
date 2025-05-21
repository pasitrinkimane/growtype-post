<?php

class Growtype_Post_Admin_Methods_Share_Blogger
{
    public static function share($accounts_details, $post_details)
    {
        $post_id = $post_details['id'];
        $send_details = self::prepare_send_details($post_details);

        foreach ($accounts_details as $auth_group_key => $accounts) {
            foreach ($accounts as $account_channels) {
                foreach ($account_channels as $blog_id) {
                    $already_shared = Growtype_Post_Admin_Methods_Share::check_if_post_is_already_shared_on_platform($post_details['id'], Growtype_Post_Admin_Methods_Meta_Share::BLOGGER, $auth_group_key, $blog_id);

                    $response = [];
                    if (!$already_shared) {
                        $auth_details_meta_key = sprintf('growtype_post_google_auth_%s_details', $auth_group_key);
                        $access_details = get_user_meta(get_current_user_id(), $auth_details_meta_key, true);

                        $access_token = $access_details['access_token'] ?? '';

                        if (empty($access_token)) {
                            $auth_redirect = self::auth_redirect($auth_group_key, $post_id);

                            if (isset($auth_redirect['success']) && !$auth_redirect['success']) {
                                $response = $auth_redirect;
                            }
                        } else {
                            $response = self::post($access_token, $blog_id, $send_details);

                            $platform_share_details = Growtype_Post_Admin_Methods_Share::format_already_shared_on_platforms_details(Growtype_Post_Admin_Methods_Meta_Share::BLOGGER, $auth_group_key, $blog_id, $response);

                            if (!$response['success']) {
                                $growtype_auth_google = new Growtype_Auth_Google();
                                $access_token = $growtype_auth_google->get_access_token_from_refresh_token($auth_group_key, $access_details['refresh_token']);

                                $access_details['access_token'] = $access_token;
                                update_user_meta(get_current_user_id(), $auth_details_meta_key, $access_details);

                                $response = self::post($access_token, $blog_id, $send_details);

                                if (!$response['success']) {
                                    $response['success'] = false;
                                    $response['message'] = sprintf('%s refresh token failed for %s %s', ucfirst(Growtype_Post_Admin_Methods_Meta_Share::BLOGGER), $auth_group_key, $blog_id);
                                } else {
                                    $response['auth_group_key'] = $auth_group_key;
                                    $response['account_channel'] = $blog_id;

                                    Growtype_Post_Admin_Methods_Share::update_already_shared_on_platforms_details($post_details['id'], $platform_share_details);
                                }
                            } else {
                                $response['auth_group_key'] = $auth_group_key;
                                $response['account_channel'] = $blog_id;

                                Growtype_Post_Admin_Methods_Share::update_already_shared_on_platforms_details($post_details['id'], $platform_share_details);
                            }
                        }
                    } else {
                        $response['success'] = false;
                        $response['message'] = sprintf('Already shared on %s - %s - %s', ucfirst(Growtype_Post_Admin_Methods_Meta_Share::BLOGGER), $auth_group_key, $blog_id);
                    }

                    $responses[] = $response;
                }
            }
        }

        return $responses;
    }

    public static function prepare_send_details($post_details)
    {
        $post_content = Growtype_Post_Admin_Methods_Share::format_post_content($post_details['content']);
        $post_title = $post_details['title'];
        $featured_img_url = $post_details['featured_img_url'];

        /**
         * Add intro text
         */
        if (!empty($main_meta_title) && !empty($website_domain)) {
            $intro_text = sprintf('%s — %s', $main_meta_title, $website_domain);
            $source_url = $post_details['cta_url'];
            $intro_text = sprintf("Source: 🔗 <a href='%s' target='_blank'>%s</a>", $source_url, $intro_text);
            $post_content = $intro_text . "\n\n" . $post_content;
        }

        if (!empty($featured_img_url)) {
            $post_content = "<img src='" . $featured_img_url . "'> \n\n " . $post_content;
        }

        return [
            'kind' => 'blogger#post',
            'title' => $post_title,
            'content' => $post_content,
            'labels' => ['connect', 'virtual characters', 'conversations', 'chat', 'learn', 'interact', 'lifelike personalities', 'experience', 'engaging conversations'],
        ];
    }

    public static function auth_redirect($auth_group_key, $post_id)
    {
        $growtype_auth_google = new Growtype_Auth_Google();

        $auth_url = $growtype_auth_google->auth_url(
            Growtype_Auth::TYPE_AUTH,
            $auth_group_key,
            [
                'success_redirect_url' => htmlspecialchars_decode(get_edit_post_link($post_id))
            ],
            [
                "https://www.googleapis.com/auth/blogger"
            ]
        );

        if (empty($auth_url)) {
            return [
                'success' => false,
                'message' => 'Error generating redirect URL. Probably credentials are missing!',
            ];
        }

        return [
            'success' => false,
            'message' => sprintf('Google Authorization is required. Click <a href="%s">here</a>.', $auth_url),
            'redirect_url' => $auth_url
        ];
    }

    public static function post($access_token, $blog_id, $post_data)
    {
        try {
            $api_endpoint = 'https://www.googleapis.com/blogger/v3/blogs/' . $blog_id . '/posts';

            $post_data['blog']['id'] = $blog_id;

            $options = [
                CURLOPT_URL => $api_endpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($post_data),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $access_token,
                ],
            ];

            $curl = curl_init();

            curl_setopt_array($curl, $options);

            $response = curl_exec($curl);

            curl_close($curl);

            $response = json_decode($response, true);

            if (!isset($response['error'])) {
                $response_url = $response['url'];
                $response_message = sprintf('%s post created successfully. View it <a href="%s" target="_blank">here</a>', ucfirst(Growtype_Post_Admin_Methods_Meta_Share::BLOGGER), $response_url);

                return [
                    'success' => true,
                    'message' => $response_message,
                    'url' => $response_url
                ];
            } else {
                return [
                    'success' => false,
                    'message' => sprintf('%s - %s.', ucfirst(Growtype_Post_Admin_Methods_Meta_Share::BLOGGER), $response['error']['message'] ?? 'Something went wrong')
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => sprintf('%s Error: %s', ucfirst(Growtype_Post_Admin_Methods_Meta_Share::BLOGGER), $e->getMessage())
            ];
        }
    }
}
