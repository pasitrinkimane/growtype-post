<?php

class Growtype_Post_Admin_Methods_Share_Tumblr
{
    public static function share($accounts_details, $post_details)
    {
        $send_details = self::prepare_send_details($post_details);

        $responses = [];
        foreach ($accounts_details as $auth_group_key => $accounts) {
            $credentials = Growtype_Auth::credentials(Growtype_Post_Admin_Methods_Meta_Share::TUMBLR)[$auth_group_key] ?? [];

            foreach ($accounts as $account_channels) {
                foreach ($account_channels as $account_channel) {
                    $already_shared = Growtype_Post_Admin_Methods_Share::check_if_post_is_already_shared_on_platform($post_details['id'], Growtype_Post_Admin_Methods_Meta_Share::TUMBLR, $auth_group_key, $account_channel);

                    $response = [];
                    if (!$already_shared) {
                        $auth_details = get_user_meta(get_current_user_id(), sprintf('growtype_post_tumblr_auth_%s_details', $auth_group_key), true);

                        if (empty($auth_details)) {
                            $growtype_auth_tumblr = new Growtype_Auth_Tumblr();

                            $auth_url = $growtype_auth_tumblr->auth_url(
                                Growtype_Auth::TYPE_AUTH,
                                $auth_group_key,
                                [
                                    'success_redirect_url' => htmlspecialchars_decode(get_edit_post_link($post_details['id']))
                                ]
                            );

                            if (empty($auth_url)) {
                                $response = [
                                    'success' => false,
                                    'message' => 'Tumblr credentials are missing'
                                ];
                            } else {
                                $response = [
                                    'success' => false,
                                    'message' => sprintf('%s Authorization is required. Click <a href="%s">here</a>.', ucfirst(Growtype_Post_Admin_Methods_Meta_Share::TUMBLR), $auth_url),
                                    'redirect_url' => $auth_url
                                ];
                            }
                        } else {
                            $response = self::post($account_channel, $credentials, $auth_details, $send_details);

                            $platform_share_details = Growtype_Post_Admin_Methods_Share::format_already_shared_on_platforms_details(Growtype_Post_Admin_Methods_Meta_Share::TUMBLR, $auth_group_key, $account_channel, $response);

                            if ($response['success']) {
                                $response['auth_group_key'] = $auth_group_key;
                                $response['account_channel'] = $account_channel;

                                Growtype_Post_Admin_Methods_Share::update_already_shared_on_platforms_details($post_details['id'], $platform_share_details);
                            }
                        }
                    } else {
                        $response['success'] = false;
                        $response['message'] = sprintf('Already shared on %s - %s - %s', ucfirst(Growtype_Post_Admin_Methods_Meta_Share::TUMBLR), $auth_group_key, $account_channel);
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
        $post_title = $post_details['title'];
        $post_content = Growtype_Post_Admin_Methods_Share::format_post_content($post_details['content']);
        $featured_img_url = $post_details['featured_img_url'];

        $cta_url = Growtype_Post_Admin_Methods_Share::get_params_from_post_content($post_content)['cta_url'] ?? '';
        $cta_url = !empty($cta_url) ? $cta_url : $post_details['cta_url'];

        $tags = Growtype_Post_Admin_Methods_Share::get_params_from_post_content($post_content)['tags'] ?? '';
        $tags = !empty($tags) ? Growtype_Post_Admin_Methods_Meta_Content::create_hashtags($tags) : null;

        $caption = Growtype_Post_Admin_Methods_Share::get_params_from_post_content($post_content)['caption'] ?? '';
        $content_title = !empty($caption) ? $caption : $post_title;
        $content_title = $content_title === 'Auto Draft' ? '' : $content_title;

        if (empty($content_title)) {
            return [
                'success' => false,
                'message' => 'Tumblr caption is missing',
            ];
        }

        $intro_title = Growtype_Post_Admin_Methods_Share::INTRO_TITLE;

        if (!empty($caption)) {
            $body = $content_title;
            $body .= "<p>";
            $body .= ' ' . Growtype_Post_Admin_Methods_Share::FIND_OUT_MORE_SENTENCES[array_rand(Growtype_Post_Admin_Methods_Share::FIND_OUT_MORE_SENTENCES)];
            $body .= "</p>";
        } else {
            $body = sprintf($intro_title[array_rand($intro_title)], $post_title);

            if (!empty($cta_url)) {
                $body .= "<p>";
                $body .= Growtype_Post_Admin_Methods_Share::READ_MORE_SENTENCES[array_rand(Growtype_Post_Admin_Methods_Share::READ_MORE_SENTENCES)];
                $body .= "</p>";
                $body .= " " . sprintf('<a href="%s" title="%s">%s</a>', $cta_url, $content_title, $cta_url);
            }
        }

        if (empty($featured_img_url)) {
            return [
                'success' => false,
                'message' => 'Tumblr image is missing',
            ];
        }

        $post_data = [
            'type' => 'photo',
            'caption' => $body,
            'source' => $featured_img_url, // URL of the image
            'state' => 'published'
        ];

        if (!empty($tags)) {
            $post_data['tags'] = $tags;
        }

        if (!empty($cta_url)) {
            $post_data['link'] = $cta_url;
        }

        return $post_data;
    }

    public static function post($blog_name, $credentials, $auth_details, $post_data)
    {
        $url = "https://api.tumblr.com/v2/blog/$blog_name/post";

        // OAuth parameters
        $oauthParams = [
            'oauth_consumer_key' => $credentials['consumer_key'],
            'oauth_nonce' => uniqid(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0',
            'oauth_token' => $auth_details['oauth_token'],
        ];

        // Combine OAuth parameters and post data for signature
        $signatureParams = array_merge($oauthParams, $post_data);
        ksort($signatureParams);

        // Build base string for signing
        $baseString = Growtype_Auth_Tumblr::build_base_string($url, 'POST', $signatureParams);
        $signature = Growtype_Auth_Tumblr::sign_request($baseString, $credentials['consumer_secret'], $auth_details['oauth_token_secret']);

        // Add the OAuth signature
        $oauthParams['oauth_signature'] = $signature;

        // Build the Authorization header
        $authHeader = Growtype_Auth_Tumblr::build_authorization_header($oauthParams);

        // Make the API request using wp_remote_post
        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => $authHeader,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => $post_data, // Include post data
        ]);

        // Check for errors in the response
        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => 'Error making API request to Tumblr: ' . $response->get_error_message(),
            ];
        }

        // Parse the response body
        $body = wp_remote_retrieve_body($response);
        $response = json_decode($body, true);

        // Return success or failure
        if (isset($response['meta']['status']) && $response['meta']['status'] == 201) {
            $response_url = sprintf('https://www.tumblr.com/%s/%s/', $blog_name, $response['response']['id'] ?? '');

            return [
                'success' => true,
                'message' => sprintf('%s post created successfully. View it <a href="%s" target="_blank">here</a>', ucfirst(Growtype_Post_Admin_Methods_Meta_Share::TUMBLR), $response_url),
                'response' => $response,
                'url' => $response_url
            ];
        } else {
            $error = $response['meta']['msg'] ?? 'Unknown error';

            if (isset($response['response']['errors'][0])) {
                $error = $response['response']['errors'][0];
            }

            return [
                'success' => false,
                'message' => sprintf('Tumblr post failed - %s. Please try again.', $error),
            ];
        }
    }
}
