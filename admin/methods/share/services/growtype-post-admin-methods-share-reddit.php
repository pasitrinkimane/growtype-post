<?php

class Growtype_Post_Admin_Methods_Share_Reddit
{
    public static function share($accounts_details, $post_details)
    {
        $send_details = self::prepare_send_details($post_details);

        $responses = [];
        foreach ($accounts_details as $auth_group_key => $accounts) {
            $credentials = Growtype_Auth::credentials(Growtype_Post_Admin_Methods_Meta_Share::REDDIT)[$auth_group_key] ?? [];
            foreach ($accounts as $account_channels) {
                foreach ($account_channels as $account_channel) {
                    $already_shared = Growtype_Post_Admin_Methods_Share::check_if_post_is_already_shared_on_platform($post_details['id'], Growtype_Post_Admin_Methods_Meta_Share::REDDIT, $auth_group_key, $account_channel);

                    $response = [];
                    if (!$already_shared) {
                        $response = self::post($credentials, $account_channel, $send_details);

                        $platform_share_details = Growtype_Post_Admin_Methods_Share::format_already_shared_on_platforms_details(Growtype_Post_Admin_Methods_Meta_Share::REDDIT, $auth_group_key, $account_channel, $response);

                        if ($response['success']) {
                            $response['auth_group_key'] = $auth_group_key;
                            $response['account_channel'] = $account_channel;

                            Growtype_Post_Admin_Methods_Share::update_already_shared_on_platforms_details($post_details['id'], $platform_share_details);
                        }
                    } else {
                        $response['success'] = false;
                        $response['message'] = sprintf('Already shared on %s - %s - %s', ucfirst(Growtype_Post_Admin_Methods_Meta_Share::REDDIT), $auth_group_key, $account_channel);
                    }

                    $responses[] = $response;
                }
            }
        }

        return $responses;
    }

    public static function post($credentials, $subreddit, $post_details)
    {
        try {
            $agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3';
            $title = $post_details['title'];
            $url = $post_details['url'] ?? '';
            $content = $post_details['content'];
            $post_kind = $post_details['post_kind'] ?? 'self';

            $content = htmlspecialchars_decode(strip_tags($content));

            $client_id = $credentials['client_id'];
            $client_secret = $credentials['client_secret'];
            $redditUsername = $credentials['username'];
            $redditPassword = $credentials['password'];

            // Step 1: Obtain access token
            $token_url = 'https://www.reddit.com/api/v1/access_token';

            $token_data = array (
                'grant_type' => 'password',
                'username' => $redditUsername,
                'password' => $redditPassword
            );

            $token_headers = array (
                'User-Agent: ' . $agent,
                'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret)
            );

            $ch = curl_init($token_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $token_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $token_headers);
            $token_response = curl_exec($ch);
            curl_close($ch);

            $token = json_decode($token_response, true);

            // Step 2: Create post
            $post_url = 'https://oauth.reddit.com/api/submit';

            $max_length = 300;
            if (mb_strlen($title) > $max_length) {
                $title = mb_substr($title, 0, $max_length - 3) . '...';
            }

            $post_data = array (
                'kind' => $post_kind, // Change to 'self' if it's a text post
                'title' => $title,
                'text' => $content,
                'sr' => $subreddit,
                'resubmit' => 'true',
            );

            if (!empty($url)) {
                $post_data['url'] = $url;
            }

            $post_headers = array (
                'User-Agent: ' . $agent,
                'Authorization: bearer ' . $token['access_token']
            );

            $ch = curl_init($post_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $post_headers);
            $response = curl_exec($ch);
            curl_close($ch);

            $response = json_decode($response, true);

            if (isset($response['success']) && $response['success'] == true) {
                $response_url = $response['jquery'][10][3][0] ?? '';
                $response_message = !empty($response_url) ? sprintf('%s post created successfully. View it <a href="%s" target="_blank">here</a>', ucfirst(Growtype_Post_Admin_Methods_Meta_Share::REDDIT), $response_url) : $response['message'];

                return [
                    'success' => true,
                    'message' => $response_message,
                    'url' => $response_url
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Reddit Error: ' . ($response['jquery'][14][3][0] ?? 'Undefined error')
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => sprintf('%s Error: %s', ucfirst(Growtype_Post_Admin_Methods_Meta_Share::REDDIT), $e->getMessage())
            ];
        }
    }

    public static function prepare_send_details($post_details)
    {
        $post_id = $post_details['id'];
        $post_title = $post_details['title'];
        $post_content = Growtype_Post_Admin_Methods_Share::format_post_content($post_details['content']);
        $featured_img_url = $post_details['featured_img_url'];
        $cta_url = $post_details['cta_url'];
        $caption = Growtype_Post_Admin_Methods_Share::get_params_from_post_content($post_content)['caption'] ?? '';

        $content_formats = [
            'full' => 'full',
            'medium' => 'medium',
            'short' => 'short',
            'link' => 'link'
        ];

        $intro_title = Growtype_Post_Admin_Methods_Share::INTRO_TITLE;
        $intro_sentences = Growtype_Post_Admin_Methods_Share::INTRO_SENTENCES;
        $read_more_sentences = Growtype_Post_Admin_Methods_Share::READ_MORE_SENTENCES;

        if (!empty($caption)) {
            $reddit_post_title = $caption;

            if (!empty($featured_img_url)) {
                $content = "![]( {$featured_img_url} ) \n\n ";
            }

            if (isset($post_details['cta_url']) && !empty($post_details['cta_url'])) {
                $content = sprintf('%s ![](%s) %s', Growtype_Post_Admin_Methods_Share::FIND_OUT_MORE_SENTENCES[array_rand(Growtype_Post_Admin_Methods_Share::FIND_OUT_MORE_SENTENCES)], $post_details['cta_url'], $content);
            }

            return [
                'title' => $reddit_post_title,
                'url' => $featured_img_url,
                'post_kind' => 'link',
                'content' => $content,
            ];
        } else {
            $reddit_post_title = sprintf($intro_title[array_rand($intro_title)], $post_title);

            $content = '';
            $content_format = array_rand($content_formats, 1);

            $read_more_sentence = sprintf('%s — %s', $post_title, $website_domain ?? '');
            $read_more_sentence = $read_more_sentences[array_rand($read_more_sentences)] . " " . "[$read_more_sentence]($cta_url)";

            switch ($content_format) {
                case 'full':
                    $content = $read_more_sentence . "\n\n" . $post_content;

                    break;
                case 'long':
                    $content = Growtype_Post_Admin_Methods_Share::get_first_content_elements($post_content, rand(7, 12));
                    $content .= "\n\n" . $read_more_sentence . "\n\n";

                    break;
                case 'medium':
                    $content = Growtype_Post_Admin_Methods_Share::get_first_content_elements($post_content, rand(3, 6));
                    $content .= "\n\n" . $read_more_sentence . "\n\n";

                    break;
                case 'short':
                    $content = Growtype_Post_Admin_Methods_Share::get_first_content_elements($post_content, rand(1, 2));
                    $content .= "\n\n" . $read_more_sentence . "\n\n";

                    break;
                case 'link':
                    $content = $intro_sentences[array_rand($intro_sentences)] . " " . "[$cta_url]($cta_url)";

                    break;
            }

            if (!empty($featured_img_url)) {
                $content = $content . "\n\n " . "![]( {$featured_img_url} ) \n\n ";
            }

            if (empty($content)) {
                return [
                    'success' => false,
                    'message' => 'Content is empty'
                ];
            }

            $send_details = [
                'post_kind' => 'link',
                'title' => $reddit_post_title,
                'post_id' => $post_id,
                'url' => $cta_url,
                'content' => $content,
            ];
        }

        return $send_details;
    }

    public static function post_image($credentials, $post_details)
    {
        try {
            // User Agent and Post Details
            $agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3';
            $title = $post_details['title'];
            $subreddit = $post_details['subreddit'];
            $image_url = $post_details['url'];
            $content = $post_details['content'] ?? ''; // Optional caption or text content

            // Credentials
            $client_id = $credentials['client_id'];
            $client_secret = $credentials['client_secret'];
            $redditUsername = $credentials['username'];
            $redditPassword = $credentials['password'];

            // Step 1: Obtain Access Token
            $token_url = 'https://www.reddit.com/api/v1/access_token';
            $token_data = [
                'grant_type' => 'password',
                'username' => $redditUsername,
                'password' => $redditPassword,
            ];
            $token_headers = [
                'User-Agent: ' . $agent,
                'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret),
            ];

            $ch = curl_init($token_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $token_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $token_headers);
            $token_response = curl_exec($ch);
            curl_close($ch);

            $token = json_decode($token_response, true);

            if (empty($token['access_token'])) {
                throw new Exception('Failed to obtain access token: ' . print_r($token, true));
            }

            $auth_headers = [
                'User-Agent: ' . $agent,
                'Authorization: bearer ' . $token['access_token'],
            ];

            // Step 2: Upload the Image
            $upload_response = self::upload_media_to_reddit($image_url, $auth_headers);

            var_dump($upload_response);

            $media_id = $upload_response['asset_id'];

            // Step 3: Submit the Post
            $post_url = 'https://oauth.reddit.com/api/submit';
            $post_data = [
                'kind' => 'image', // Specify the post type as an image
                'title' => $title,
                'sr' => $subreddit,
                'resubmit' => true,
                'sendreplies' => true,
//                'media_asset_id' => $media_id, // The uploaded media's ID,
//                'url' => "https://preview.redd.it/{$media_id}",
                'url' => "https://www.reddit.com/media?url=https%3A%2F%2Fpreview.redd.it%2Fmrj6bumxilbe1.jpeg%3Fauto%3Dwebp%26s%3D6d5398af98ddc8b24cffb55d5fe6e32552bd1c57",
            ];

            if (!empty($content)) {
                $post_data['text'] = $content; // Optional text content
            }

            var_dump($post_data);

            $ch = curl_init($post_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $auth_headers);
            $response = curl_exec($ch);
            curl_close($ch);

            $response = json_decode($response, true);

            var_dump($response);
            die();

            if (isset($response['success']) && $response['success'] === true) {
                return [
                    'success' => true,
                    'message' => 'Reddit image post created successfully.',
                ];
            } else {
                throw new Exception('Reddit Error: ' . print_r($response, true));
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Reddit Error: ' . $e->getMessage(),
            ];
        }
    }

    public static function upload_media_to_reddit($imageUrl, $authHeaders)
    {
        try {
            $uploadAssetUrl = 'https://oauth.reddit.com/api/media/asset.json';

            // Extract filename and MIME type
            $filename = basename(parse_url($imageUrl, PHP_URL_PATH));
            $mimetype = self::getMimeTypeFromUrl($imageUrl);

            // Request upload URL and fields
            $requestData = [
                'filepath' => $filename,
                'mimetype' => $mimetype,
            ];

            $ch = curl_init($uploadAssetUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $authHeaders);
            $uploadResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                throw new Exception("Reddit API returned HTTP code $httpCode: $uploadResponse");
            }

            $uploadData = json_decode($uploadResponse, true);
            if (empty($uploadData['args']['action']) || empty($uploadData['args']['fields']) || empty($uploadData['asset']['websocket_url'])) {
                throw new Exception('Invalid upload response from Reddit API: ' . print_r($uploadData, true));
            }

            $uploadUrl = 'https:' . $uploadData['args']['action'];
            $fields = $uploadData['args']['fields'];
            $assetId = $uploadData['asset']['asset_id'];
            $websocketUrl = $uploadData['asset']['websocket_url'];

            // Download the image
            $fileContent = file_get_contents($imageUrl);
            if ($fileContent === false) {
                throw new Exception('Failed to download image from URL: ' . $imageUrl);
            }

            // Prepare file upload
            $tempFile = tempnam(sys_get_temp_dir(), 'reddit_media');
            file_put_contents($tempFile, $fileContent);

            $formData = [];
            foreach ($fields as $field) {
                $formData[$field['name']] = $field['value'];
            }
            $formData['file'] = new CURLFile($tempFile, $mimetype, $filename);

            $ch = curl_init($uploadUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $fileUploadResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            unlink($tempFile);

            if ($httpCode !== 201) {
                throw new Exception("File upload failed with HTTP code $httpCode: $fileUploadResponse");
            }

            // Wait for the WebSocket confirmation
//            $assetId = self::confirmUploadViaWebSocket($websocketUrl);

            return [
                'asset_id' => $assetId,
                'websocket_url' => $websocketUrl,
            ];
        } catch (Exception $e) {
            throw new Exception('Media Upload Error: ' . $e->getMessage());
        }
    }

    /**
     * Confirm upload via WebSocket.
     */
    private static function confirmUploadViaWebSocket($websocketUrl)
    {
        $ws = stream_socket_client($websocketUrl, $errno, $errstr, 30, STREAM_CLIENT_CONNECT);

        if (!$ws) {
            throw new Exception("Failed to connect to WebSocket: $errstr ($errno)");
        }

        // Wait for WebSocket response
        $response = fread($ws, 1024);
        fclose($ws);

        $data = json_decode($response, true);

        if (empty($data['payload']['asset_id'])) {
            throw new Exception('WebSocket did not return asset_id: ' . $response);
        }

        return $data['payload']['asset_id'];
    }

    /**
     * Extract MIME type from image URL.
     */
    private static function getMimeTypeFromUrl($url)
    {
        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ];

        return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
    }
}
