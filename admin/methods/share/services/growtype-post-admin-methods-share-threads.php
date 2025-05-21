<?php

class Growtype_Post_Admin_Methods_Share_Threads
{
    public static function share($accounts_details, $post_details)
    {
        $send_details = self::prepare_send_details($post_details);

        $responses = [];
        foreach ($accounts_details as $auth_group_key => $accounts) {
            $credentials = Growtype_Auth::credentials(Growtype_Post_Admin_Methods_Meta_Share::THREADS)[$auth_group_key] ?? [];

            foreach ($accounts as $account_channels) {
                foreach ($account_channels as $account_channel) {
                    $already_shared = Growtype_Post_Admin_Methods_Share::check_if_post_is_already_shared_on_platform($post_details['id'], Growtype_Post_Admin_Methods_Meta_Share::THREADS, $auth_group_key, $account_channel);

                    $response = [];
                    if (!$already_shared) {
                        $auth_details = get_user_meta(get_current_user_id(), sprintf('growtype_post_threads_auth_%s_details', $auth_group_key), true);

                        if (empty($auth_details)) {
                            $response = self::return_auth_url($post_details['id']);
                        } else {
                            $auth_details = $auth_details[$account_channel] ?? [];

                            $response = self::post($auth_details, $send_details);

                            $platform_share_details = Growtype_Post_Admin_Methods_Share::format_already_shared_on_platforms_details(Growtype_Post_Admin_Methods_Meta_Share::THREADS, $auth_group_key, $account_channel, $response);

                            if ($response['success']) {
                                $response['auth_group_key'] = $auth_group_key;
                                $response['account_channel'] = $account_channel;

                                Growtype_Post_Admin_Methods_Share::update_already_shared_on_platforms_details($post_details['id'], $platform_share_details);
                            } else {
                                if (isset($response['reason']) && $response['reason'] === 'id') {
                                    $response = self::return_auth_url($post_details['id']);
                                }
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
        $post_content = Growtype_Post_Admin_Methods_Share::format_post_content($post_details['content']);
        $post_title = $post_details['title'];
        $featured_img_url = $post_details['featured_img_url'];

        $auth_details = get_user_meta(get_current_user_id(), 'growtype_post_threads_auth_details', true);

        if (empty($auth_details)) {
            return self::return_auth_url($post_id);
        }

        $intro_title = Growtype_Post_Admin_Methods_Share::INTRO_TITLE;
        $intro_sentences = Growtype_Post_Admin_Methods_Share::INTRO_SENTENCES;
        $read_more_sentences = Growtype_Post_Admin_Methods_Share::READ_MORE_SENTENCES;

        $body = sprintf($intro_title[array_rand($intro_title)], $post_title);
        $body .= "\n";
        $body .= html_entity_decode(growtype_post_get_limited_content($post_content, 100), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $body .= "\n";
        $body .= $read_more_sentences[array_rand($read_more_sentences)] . " " . get_permalink($post_id);

        $cta_url = Growtype_Post_Admin_Methods_Share::get_params_from_post_content($post_content)['cta_url'] ?? '';
        $cta_url = !empty($cta_url) ? $cta_url : $post_details['cta_url'];

        $tags = Growtype_Post_Admin_Methods_Share::get_params_from_post_content($post_content)['tags'] ?? '';
        $tags = !empty($tags) ? Growtype_Post_Admin_Methods_Meta_Content::create_hashtags($tags) : null;

        $caption = Growtype_Post_Admin_Methods_Share::get_params_from_post_content($post_content)['caption'] ?? '';
        $caption = !empty($caption) ? $caption : $post_title;

        $submit_details = [
            'media_type' => 'IMAGE',
            'image_url' => $featured_img_url,
            'text' => $caption
        ];

        return $submit_details;
    }

    public static function return_auth_url($post_id)
    {
        $growtype_auth_threads = new Growtype_Auth_Threads();

        $auth_url = $growtype_auth_threads->auth_url(
            Growtype_Auth::TYPE_AUTH,
            'growtype_post',
            [
                'success_redirect_url' => htmlspecialchars_decode(get_edit_post_link($post_id)),
                'auth_origin_url' => htmlspecialchars_decode(get_edit_post_link($post_id))
            ],
            ['threads_basic', 'threads_content_publish']
        );

        if (empty($auth_url)) {
            return [
                'success' => false,
                'message' => sprintf('%s credentials are missing', ucfirst(Growtype_Post_Admin_Methods_Meta_Share::THREADS))
            ];
        }

        return [
            'success' => false,
            'message' => sprintf('%s Authorization is required. Click <a href="%s">here</a>.', ucfirst(Growtype_Post_Admin_Methods_Meta_Share::THREADS), $auth_url),
        ];
    }

    public static function post($auth_details, $send_details)
    {
        try {

            if (empty($auth_details)) {
                return [
                    'success' => false,
                    'message' => 'Missing auth details'
                ];
            }

            $threadsUserId = $auth_details['id'];
//            $url = "https://graph.threads.net/me/threads";
            $url = "https://graph.threads.net/v1.0/$threadsUserId/threads";

            $send_details = array_merge($send_details, $auth_details);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($send_details));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded'
            ]);

            $response = curl_exec($ch);

            curl_close($ch);

            $id = json_decode($response, true);

            $url = 'https://graph.threads.net/me/threads_publish';

            if (!isset($id['id'])) {
                return [
                    'success' => false,
                    'reason' => 'id',
                    'message' => sprintf('%s id is missing', ucfirst(Growtype_Post_Admin_Methods_Meta_Share::THREADS))
                ];
            }

            $data = [
                'creation_id' => $id['id'],
                'access_token' => $auth_details['access_token']
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded'
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            $response = json_decode($response, true);

            if (isset($response['error'])) {
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? '',
                    'error_code' => $responseData['error']['code'] ?? '',
                ];
            }

            $response_url = sprintf('https://www.threads.net/@%s', $auth_details['username']);

            return [
                'success' => true,
                'message' => sprintf('%s post created successfully. View it <a href="%s" target="_blank">here</a>', ucfirst(Growtype_Post_Admin_Methods_Meta_Share::THREADS), $response_url),
                'url' => $response_url
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
