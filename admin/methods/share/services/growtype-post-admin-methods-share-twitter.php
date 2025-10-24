<?php

class Growtype_Post_Admin_Methods_Share_Twitter
{
    public static function share($accounts_details, $post_details)
    {
        $send_details = self::prepare_send_details($post_details);

        $responses = [];
        foreach ($accounts_details as $auth_group_key => $accounts) {
            foreach ($accounts as $account_channels) {
                foreach ($account_channels as $account_channel) {
                    $already_shared = Growtype_Post_Admin_Methods_Share::check_if_post_is_already_shared_on_platform($post_details['id'], Growtype_Post_Admin_Methods_Meta_Share::TWITTER, $auth_group_key, $account_channel);

                    $response = [];
                    if (!$already_shared) {
                        $response = self::post($auth_group_key, $send_details);

                        $platform_share_details = Growtype_Post_Admin_Methods_Share::format_already_shared_on_platforms_details(Growtype_Post_Admin_Methods_Meta_Share::TWITTER, $auth_group_key, $account_channel, $response);

                        if ($response['success']) {
                            $response['auth_group_key'] = $auth_group_key;

                            Growtype_Post_Admin_Methods_Share::update_already_shared_on_platforms_details($post_details['id'], $platform_share_details);
                        }
                    } else {
                        $response['success'] = false;
                        $response['message'] = sprintf('Already shared on %s - %s - %s', ucfirst(Growtype_Post_Admin_Methods_Meta_Share::TWITTER), $auth_group_key, $account_channel);
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
        $credentials_groups = Growtype_Auth::credentials(Growtype_Auth::SERVICE_TWITTER);
        $post_content = Growtype_Post_Admin_Methods_Share::format_post_content($post_details['content']);
        $post_title = $post_details['title'];
        $featured_img_url = $post_details['featured_img_url'];
        $cta_url = $post_details['cta_url'];
        $hashtags = $post_details['hashtags'];

        $intro_title = Growtype_Post_Admin_Methods_Share::INTRO_TITLE;
        $intro_sentences = Growtype_Post_Admin_Methods_Share::INTRO_SENTENCES;

        $caption = Growtype_Post_Admin_Methods_Share::get_params_from_post_content($post_content)['caption'] ?? '';

        if (!empty($caption)) {
            $body = $caption;
            $body .= "\n";
            $body .= ' ' . Growtype_Post_Admin_Methods_Share::FIND_OUT_MORE_SENTENCES[array_rand(Growtype_Post_Admin_Methods_Share::FIND_OUT_MORE_SENTENCES)];
        } else {
            $body = sprintf($intro_title[array_rand($intro_title)], $post_title);
        }

//            $body .= "\n";
//            $body .= html_entity_decode(growtype_post_get_limited_content($post_content, 100), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (!empty($cta_url)) {
            $body .= "\n";
            $body .= Growtype_Post_Admin_Methods_Share::READ_MORE_SENTENCES[array_rand(Growtype_Post_Admin_Methods_Share::READ_MORE_SENTENCES)];
            $body .= " " . $cta_url;
        }

        $body .= "\n";
        $body .= implode(' ', $hashtags);

        $post_details = [
            'body' => $body,
            'image' => !empty($featured_img_url) ? base64_encode(file_get_contents($featured_img_url)) : '',
        ];

        return $post_details;
    }

    public static function post($auth_group_key, $send_details)
    {
        try {
            $growtype_auth_twitter = new Growtype_Auth_Twitter();
            $client = $growtype_auth_twitter
                ->init_client(Growtype_Auth::TYPE_AUTH, $auth_group_key);

            if (empty($client)) {
                return [
                    'success' => false,
                    'message' => sprintf('Twitter Empty client. Please enter credentials <a href="%s" target="_blank">here</a>.', admin_url('options-general.php?page=growtype-auth-settings'))
                ];
            }

            $post_text = $send_details['body'];

            $max_length = 280;
            if (mb_strlen($post_text) > $max_length) {
                $post_text = mb_substr($post_text, 0, $max_length - 3) . '...';
            }

            // Prepare the post data
            $post_data = [
                'text' => $post_text
            ];

            if (empty($post_data['text'])) {
                throw new Exception("Tweet body cannot be empty.");
            }

            // Handle image upload if provided
            if (!empty($send_details['image'])) {
                try {
                    $media_info = $client->uploadMedia()->upload($send_details['image']);
                } catch (\Exception $e) {
                    return [
                        'success' => false,
                        'message' => 'Failed to upload media to Twitter: ' . $e->getMessage()
                    ];
                }

                $post_data['media'] = [
                    "media_ids" => [
                        (string)$media_info["media_id"]
                    ]
                ];
            }

            // Post the tweet
            $response = $growtype_auth_twitter
                ->init_client(Growtype_Auth::TYPE_AUTH, 'growtype_post')
                ->tweet()
                ->create()
                ->performRequest($post_data);

            $response = json_decode(json_encode($response), true);

            if (!$response || !isset($response['data'])) {
                return [
                    'success' => false,
                    'message' => 'Tweet post failed.'
                ];
            }

            $user_details = $client->userMeLookup()->performRequest();
            $username = $user_details->data->username ?? '';

            $post_url = 'https://x.com/' . $username . '/status/' . $response['data']['id'];

            return [
                'success' => true,
                'message' => sprintf('%s post created successfully. View it <a href="%s" target="_blank">here</a>', ucfirst(Growtype_Post_Admin_Methods_Meta_Share::TWITTER), $post_url),
                'url' => $post_url
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => sprintf('%s error: %s', ucfirst(Growtype_Post_Admin_Methods_Meta_Share::TWITTER), $e->getMessage())
            ];
        }
    }
}
