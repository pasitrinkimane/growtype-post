<?php

class Growtype_Post_Admin_Methods_Share
{
    public function __construct()
    {
        $this->load_methods();
    }

    public function load_methods()
    {
        require_once 'services/growtype-post-admin-methods-share-medium.php';
        require_once 'services/growtype-post-admin-methods-share-twitter.php';
        require_once 'services/growtype-post-admin-methods-share-treads.php';
        require_once 'services/growtype-post-admin-methods-share-pinterest.php';
    }

    public static function submit($platform, $post_id)
    {
        $featured_img_url = get_the_post_thumbnail_url($post_id, 'full');
        $post_title = get_the_title($post_id);
        $post_excerpt = get_the_excerpt($post_id);
        $post_content = apply_filters('the_content', get_post_field('post_content', $post_id));
        $main_meta_title = get_post_meta(get_option('page_on_front'), '_yoast_wpseo_title', true);
        $website_domain = parse_url(get_home_url())['host'] ?? '';

        $content_formats = [
            'full' => 'full',
            'medium' => 'medium',
            'short' => 'short',
            'link' => 'link'
        ];

        $read_more_sentences = [
            'Read more 🔗:',
            'Learn more 🔍:',
            'Explore further 🌐:',
            'Discover more 🔍:',
            'Find out more 📚:',
            'Continue reading 📖:',
            'Check out the full article 📝:',
            'Click here to read more 🖱️:',
        ];

        $intro_sentences = [
            'New article on:',
            'We published a new article on:',
            'Check out our latest article:',
            'Our latest article:',
            'Discover our latest publication:',
            'Presenting our new article:',
            'Announcing our latest article:',
        ];

        $intro_title = [
            '%s',
            'Article - %s',
            'A new article - %s',
            'Our latest article - %s',
            'Latest article - %s',
            'Latest publication - %s',
        ];

        if ($platform == Growtype_Post_Admin_Methods_Meta_Share::REDDIT) {
            $subreddits = explode(',', get_option('growtype_post_reddit_default_subreddits'));
            $source_url = get_permalink($post_id);
            $post_content = self::format_post_content($post_content);

            $response = [];
            foreach ($subreddits as $subreddit) {
                $content = '';
                $content_format = array_rand($content_formats, 1);

                $read_more_sentence = sprintf('%s — %s', $post_title, $website_domain ?? '');
                $read_more_sentence = $read_more_sentences[array_rand($read_more_sentences)] . " " . "[$read_more_sentence]($source_url)";

                switch ($content_format) {
                    case 'full':
                        $content = $read_more_sentence . "\n\n" . $post_content;

                        break;
                    case 'long':
                        $content = self::get_first_content_elements($post_content, rand(7, 12));
                        $content .= "\n\n" . $read_more_sentence . "\n\n";

                        break;
                    case 'medium':
                        $content = self::get_first_content_elements($post_content, rand(3, 6));
                        $content .= "\n\n" . $read_more_sentence . "\n\n";

                        break;
                    case 'short':
                        $content = self::get_first_content_elements($post_content, rand(1, 2));
                        $content .= "\n\n" . $read_more_sentence . "\n\n";

                        break;
                    case 'link':
                        $content = $intro_sentences[array_rand($intro_sentences)] . " " . "[$source_url]($source_url)";

                        break;
                }

                if (!empty($featured_img_url)) {
                    $content = $content . "\n\n " . "![Image]( {$featured_img_url} ) \n\n ";
                }

                if (empty($content)) {
                    return [
                        'success' => false,
                        'message' => 'Content is empty'
                    ];
                }

                $reddit_post_title = sprintf($intro_title[array_rand($intro_title)], $post_title);

                $details = [
                    'title' => $reddit_post_title,
                    'content' => $content,
                    'subreddit' => $subreddit,
                    'post_id' => $post_id,
                    'image' => $featured_img_url,
                    'url' => get_permalink($post_id)
                ];

                $credentials = [
                    'client_id' => get_option('growtype_post_reddit_client_id'),
                    'client_secret' => get_option('growtype_post_reddit_client_secret'),
                    'username' => get_option('growtype_post_reddit_username'),
                    'password' => get_option('growtype_post_reddit_password'),
                ];

                $response = self::post_to_reddit($credentials, $details);
            }

            /**
             * Update post meta
             */
            if (isset($response['success']) && $response['success']) {
                self::update_shared_on_platforms($post_id, Growtype_Post_Admin_Methods_Meta_Share::REDDIT);
            }

            return $response;
        } elseif ($platform === Growtype_Post_Admin_Methods_Meta_Share::BLOGGER) {
            /**
             * Add intro text
             */
            if (!empty($main_meta_title) && !empty($website_domain)) {
                $intro_text = sprintf('%s — %s', $main_meta_title, $website_domain);
                $source_url = get_permalink($post_id);
                $intro_text = sprintf("Source: 🔗 <a href='%s' target='_blank'>%s</a>", $source_url, $intro_text);
                $post_content = $intro_text . "\n\n" . $post_content;
            }

            if (!empty($featured_img_url)) {
                $post_content = $post_content . "\n\n " . "<img src='" . $featured_img_url . "'> \n\n ";
            }

            $access_token = get_user_meta(get_current_user_id(), 'growtype_post_google_auth_access_token', true);

            if (empty($access_token)) {
                $access_token = self::setup_blogger();

                if (isset($access_token['success']) && $access_token['success'] === false) {
                    return $access_token;
                }
            }

            $blogs_ids = explode(',', get_option('growtype_post_google_blogger_default_blogs_ids'));

            foreach ($blogs_ids as $blog_id) {
                $post_data = [
                    'kind' => 'blogger#post',
                    'title' => $post_title,
                    'content' => $post_content,
                    'labels' => ['connect', 'virtual characters', 'conversations', 'chat', 'learn', 'interact', 'lifelike personalities', 'experience', 'engaging conversations'],
                ];

                $response = self::post_to_blogger($access_token, $blog_id, $post_data);

                if (isset($response['error'])) {
                    $access_token = self::setup_blogger();

                    if (isset($access_token['success']) && $access_token['success'] === false) {
                        return $access_token;
                    }

                    return [
                        'success' => false,
                        'message' => 'Token updated. Please try again.',
                    ];
                } else {
                    self::update_shared_on_platforms($post_id, Growtype_Post_Admin_Methods_Meta_Share::BLOGGER);

                    $blog_url = $response['url'] ?? '';
                }
            }

            return [
                'success' => true,
                'message' => 'Blogger post created successfully',
                'url' => $blog_url ?? ''
            ];
        } elseif ($platform === Growtype_Post_Admin_Methods_Meta_Share::MEDIUM) {

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

            $post_details = [
                'title' => $post_title,
                'body' => $post_content,
                'subtitle' => $post_excerpt,
                'canonicalUrl' => get_permalink(get_page_by_path('blog')),
                'tags' => ['ai', 'chat', 'soulmates', 'assistant', 'chatbot'],
            ];

            $response = Growtype_Post_Admin_Methods_Share_Medium::submit($post_details);

            if (!isset($response['data'])) {
                return [
                    'success' => false,
                    'message' => 'Medium post - something went wrong. Please try again.',
                ];
            }

            self::update_shared_on_platforms($post_id, Growtype_Post_Admin_Methods_Meta_Share::MEDIUM);

            return [
                'success' => true,
                'message' => 'Medium post created successfully'
            ];
        } elseif ($platform === Growtype_Post_Admin_Methods_Meta_Share::TWITTER) {
            $body = sprintf($intro_title[array_rand($intro_title)], $post_title);
            $body .= "\n";
            $body .= html_entity_decode(growtype_post_get_limited_content($post_content, 100), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $body .= "\n";
            $body .= $read_more_sentences[array_rand($read_more_sentences)] . " " . get_permalink($post_id);

            $post_details = [
                'body' => $body,
                'image' => base64_encode(file_get_contents($featured_img_url)),
            ];

            $response = Growtype_Post_Admin_Methods_Share_Twitter::submit($post_details);

            if (isset($response['error']) && !empty($response['error'])) {
                return [
                    'success' => false,
                    'message' => $response['error'],
                ];
            }

            self::update_shared_on_platforms($post_id, Growtype_Post_Admin_Methods_Meta_Share::TWITTER);

            return [
                'success' => true,
                'message' => 'Twitter post created successfully'
            ];
        } elseif ($platform === Growtype_Post_Admin_Methods_Meta_Share::TREADS) {
            $body = sprintf($intro_title[array_rand($intro_title)], $post_title);
            $body .= "\n";
            $body .= html_entity_decode(growtype_post_get_limited_content($post_content, 100), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $body .= "\n";
            $body .= $read_more_sentences[array_rand($read_more_sentences)] . " " . get_permalink($post_id);

            $post_details = [
                'body' => $body,
                'image' => !empty($featured_img_url) ? base64_encode(file_get_contents($featured_img_url)) : '',
            ];

            $response = Growtype_Post_Admin_Methods_Share_Treads::submit($post_details);

            if (isset($response['error']) && !empty($response['error'])) {
                return [
                    'success' => false,
                    'message' => $response['error'],
                ];
            }

            self::update_shared_on_platforms($post_id, Growtype_Post_Admin_Methods_Meta_Share::TWITTER);

            return [
                'success' => true,
                'message' => 'Treads post created successfully'
            ];
        } elseif ($platform === Growtype_Post_Admin_Methods_Meta_Share::PINTEREST) {
            $access_token = get_user_meta(get_current_user_id(), 'growtype_post_pinterest_auth_access_token', true);

            if (empty($access_token)) {
                $access_token = Growtype_Post_Admin_Methods_Share_Pinterest::setup_pinterest();

                if (isset($access_token['success']) && $access_token['success'] === false) {
                    return $access_token;
                }
            }

            $boards_ids = ['945544952955159815'];

            foreach ($boards_ids as $board_id) {
                $pin_data = array (
                    'title' => 'My Pin',
                    'description' => 'Pin Description',
                    'alt_text' => 'Pin Description',
                    'board_id' => $board_id,
                    'link' => home_url(),
                    'media_source' => array (
                        'source_type' => 'image_url',
                        'url' => 'https://i.pinimg.com/564x/28/75/e9/2875e94f8055227e72d514b837adb271.jpg'
                    )
                );

                $response = Growtype_Post_Admin_Methods_Share_Pinterest::post_to_pinterest($access_token, $pin_data);

                if (isset($response['code']) && in_array($response['code'], [29, 2])) {
                    return [
                        'success' => false,
                        'message' => $response['message'],
                    ];
                }

                if (isset($response['error'])) {
                    $access_token = Growtype_Post_Admin_Methods_Share_Pinterest::setup_pinterest();

                    if (isset($access_token['success']) && $access_token['success'] === false) {
                        return $access_token;
                    }

                    return [
                        'success' => false,
                        'message' => 'Token updated. Please try again.',
                    ];
                } else {
                    self::update_shared_on_platforms($post_id, Growtype_Post_Admin_Methods_Meta_Share::PINTEREST);
                }
            }

            return [
                'success' => true,
                'message' => 'Pinterest post created successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Invalid platform'
        ];
    }

    public static function get_first_content_elements($html, $rowCount = 3)
    {
        // Load HTML string into a DOMDocument
        $dom = new DOMDocument();
        libxml_use_internal_errors(true); // Disable warnings for HTML5 elements
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        // Initialize XPath to query the DOM
        $xpath = new DOMXPath($dom);

        // Find all paragraphs (p elements) and headings (h2 elements)
        $nodes = $xpath->query('//p|//h2');

        // Extract first $rowCount elements
        $extractedElements = '';
        $elementCounter = 0;
        foreach ($nodes as $node) {
            // Get outerHTML to preserve tags
            $outerHTML = self::saveHTMLWithoutDoctype($node);

            // Append extracted element to result
            $extractedElements .= $outerHTML . "\n";

            if ($elementCounter - 1 !== $rowCount) {
                $extractedElements .= "\n" . "\n";
            }

            $elementCounter++;
            if ($elementCounter >= $rowCount) {
                break;
            }
        }

        return $extractedElements;
    }

    // Function to save HTML without the doctype, html, and body tags
    private static function saveHTMLWithoutDoctype($node)
    {
        $doc = new DOMDocument();
        $doc->appendChild($doc->importNode($node, true));
        return trim($doc->saveHTML());
    }

    public static function format_post_content($html_content)
    {
        // Remove DOCTYPE
        $html_content = preg_replace('/<!DOCTYPE[^>]*>/i', '', $html_content);

        // Remove <html> and </html> tags
        $html_content = preg_replace('/<\/?html[^>]*>/i', '', $html_content);

        // Remove <body> and </body> tags
        $html_content = preg_replace('/<\/?body[^>]*>/i', '', $html_content);

        // Remove classes from all HTML elements
        $html_content = preg_replace('/ class="[^"]*"/', '', $html_content);

        return $html_content;
    }

    public static function update_shared_on_platforms($post_id, $platform)
    {
        $shared_on_platforms = get_post_meta($post_id, 'growtype_post_is_shared_on_platforms', true);
        $shared_on_platforms = !empty($shared_on_platforms) ? $shared_on_platforms : [];
        array_push($shared_on_platforms, $platform);
        update_post_meta($post_id, 'growtype_post_is_shared_on_platforms', $shared_on_platforms);
    }

    public static function post_to_reddit($credentials, $post_details)
    {
        try {
            $agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3';
            $title = $post_details['title'];
            $subreddit = $post_details['subreddit'];
            $url = $post_details['url'] ?? '';
            $content = $post_details['content'];
            $image = $post_details['image'] ?? '';

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
            $post_data = array (
                'kind' => 'link', // Change to 'self' if it's a text post
                'title' => $title,
                'text' => $content,
                'url' => $url,
                'sr' => $subreddit,
                'resubmit' => 'true',
            );

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
                return [
                    'success' => true,
                    'message' => 'Reddit post created successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Reddit Error: ' . ($response['errors'][0][0] ?? 'Undefined error')
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Reddit Error: ' . $e->getMessage()
            ];
        }
    }

    public static function setup_blogger()
    {
        $auth_code = get_user_meta(get_current_user_id(), 'growtype_post_google_auth_code', true);

        $client_id = get_option('growtype_post_google_client_id');
        $client_secret = get_option('growtype_post_google_client_secret');
        $redirect_uri = home_url() . '/' . Growtype_Post_Admin::google_auth_redirect_path();
        $scope = 'https://www.googleapis.com/auth/blogger';

        $authorizationURL = 'https://accounts.google.com/o/oauth2/auth';

        $params = [
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => $scope,
        ];

        $redirect_url = $authorizationURL . '?' . http_build_query($params);

        if (empty($auth_code)) {
            return [
                'success' => false,
                'message' => 'Redirecting to Google for authorization',
                'redirectURL' => $redirect_url
            ];
        } else {
            $tokenURL = 'https://accounts.google.com/o/oauth2/token';

            $params = [
                'code' => $auth_code,
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri' => $redirect_uri,
                'grant_type' => 'authorization_code',
            ];

            $curl = curl_init($tokenURL);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
            curl_close($curl);
            $responseData = json_decode($response, true);

            if (isset($responseData['error'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid grant',
                    'redirectURL' => $redirect_url
                ];
            }

            $access_token = $responseData['access_token'] ?? '';

            if (!empty($access_token)) {
                update_user_meta(get_current_user_id(), 'growtype_post_google_auth_access_token', $access_token);

                return $access_token;
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to get access token',
                    'redirectURL' => $redirect_url
                ];
            }
        }
    }

    public static function post_to_blogger($access_token, $blog_id, $post_data)
    {
        $apiEndpoint = 'https://www.googleapis.com/blogger/v3/blogs/' . $blog_id . '/posts';

        $post_data['blog']['id'] = $blog_id;

        $options = [
            CURLOPT_URL => $apiEndpoint,
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

        return json_decode($response, true);
    }
}
