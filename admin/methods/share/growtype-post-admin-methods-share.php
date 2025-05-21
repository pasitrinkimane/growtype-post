<?php

class Growtype_Post_Admin_Methods_Share
{
    const READ_MORE_SENTENCES = [
        'Read more 🔗:',
        'Continue reading 📖:',
        'Check out the full article 📝:'
    ];

    const FIND_OUT_MORE_SENTENCES = [
        'Learn more 🔍:',
        'Explore further 🌐:',
        'Discover more 🔍:',
        'Find out more 🔍:',
        'Click here for more info 🖱️:',
    ];

    const INTRO_TITLE = [
        '"%s"', // Keeps it neutral for flexibility
        "💡 New Article Alert! \n\"%s\"",
        "📖 Just Published: \n\"%s\"",
        "🔥 Hot Off the Press: \n\"%s\"",
        "✨ Check Out Our Latest: \n\"%s\"",
        "🔔 Don't Miss This: \n\"%s\"",
        "🌟 Featured Article: \n\"%s\"",
        "📰 Fresh Reads: \n\"%s\"",
        "📖 Explore Now: \n\"%s\""
    ];

    const INTRO_SENTENCES = [
        'New article:',
        'We published a new article:',
        'Check out our latest article:',
        'Our latest article:',
        'Discover our latest publication:',
        'Presenting our new article:',
        'Announcing our latest article:',
    ];

    public function __construct()
    {
        $this->load_methods();

        add_filter('growtype_auth_admin_settings_credentials_available_services', [$this, 'growtype_auth_admin_settings_credentials_available_services_extend']);

        add_filter('growtype_post_admin_methods_share_response', [$this, 'growtype_post_admin_methods_share_response_extend'], 0, 4);
    }

    function growtype_auth_admin_settings_credentials_available_services_extend($services)
    {
        $services[Growtype_Auth::SERVICE_REDDIT]['fields'] = array_merge($services[Growtype_Auth::SERVICE_REDDIT]['fields'], [
            [
                'name' => 'username',
                'label' => 'Username',
                'placeholder' => 'Username',
                'type' => 'text',
                'default' => ''
            ],
            [
                'name' => 'password',
                'label' => 'Password',
                'placeholder' => 'Password',
                'type' => 'text',
                'default' => ''
            ],
            [
                'name' => 'subreddits',
                'label' => 'Subreddits (separated by comma)',
                'placeholder' => 'Subreddits',
                'type' => 'text',
                'default' => ''
            ],
        ]);

        $services[Growtype_Auth::SERVICE_GOOGLE]['fields'] = array_merge($services[Growtype_Auth::SERVICE_GOOGLE]['fields'], [
            [
                'name' => 'blogger_blogs_ids',
                'label' => 'Blogger Blogs ids (separated by comma)',
                'placeholder' => 'Blogs ids',
                'type' => 'text',
                'default' => ''
            ],
        ]);

        $services[Growtype_Auth::SERVICE_TUMBLR]['fields'] = array_merge($services[Growtype_Auth::SERVICE_TUMBLR]['fields'], [
            [
                'name' => 'blog_name',
                'label' => 'Blog name',
                'placeholder' => 'Blog name',
                'type' => 'text',
                'default' => ''
            ],
        ]);

        $services[Growtype_Auth::SERVICE_THREADS]['fields'] = array_merge($services[Growtype_Auth::SERVICE_THREADS]['fields'], [
            [
                'name' => 'available_users',
                'label' => 'Available users',
                'placeholder' => 'Usernames (separated by comma)',
                'type' => 'text',
                'default' => ''
            ],
        ]);

        return $services;
    }

    public function load_methods()
    {
        require_once 'services/growtype-post-admin-methods-share-blogger.php';
        new Growtype_Post_Admin_Methods_Share_Blogger();

        require_once 'services/growtype-post-admin-methods-share-medium.php';
        new Growtype_Post_Admin_Methods_Share_Medium();

        require_once 'services/growtype-post-admin-methods-share-pinterest.php';
        new Growtype_Post_Admin_Methods_Share_Pinterest();

        require_once 'services/growtype-post-admin-methods-share-reddit.php';
        new Growtype_Post_Admin_Methods_Share_Reddit();

        require_once 'services/growtype-post-admin-methods-share-threads.php';
        new Growtype_Post_Admin_Methods_Share_Threads();

        require_once 'services/growtype-post-admin-methods-share-twitter.php';
        new Growtype_Post_Admin_Methods_Share_Twitter();

        require_once 'services/growtype-post-admin-methods-share-tumblr.php';
        new Growtype_Post_Admin_Methods_Share_Tumblr();
    }

    function growtype_post_admin_methods_share_response_extend($share_details, $account_details, $post_details, $platform_class)
    {
        if (class_exists($platform_class)) {
            $share_details = $platform_class::share($account_details, $post_details);
        }

        return $share_details;
    }

    public static function submit($platform, $account_details, $post_id)
    {
        $post = get_post($post_id);

        $post_content = $post->post_content;
        $post_featured_img_url = get_the_post_thumbnail_url($post_id, 'full');

        if (empty($post_content)) {
            return [
                'success' => false,
                'message' => 'Post <b>content</b> is empty'
            ];
        }

        if (empty($post_featured_img_url)) {
            return [
                'success' => false,
                'message' => 'Post <b>featured image</b> is empty'
            ];
        }

        $post_details = [
            'id' => $post_id,
            'content' => $post_content,
            'title' => $post->post_title,
            'excerpt' => $post->post_excerpt,
            'featured_img_url' => $post_featured_img_url,
            'default_meta_title' => get_post_meta(get_option('page_on_front'), '_yoast_wpseo_title', true),
            'website_domain' => parse_url(get_home_url())['host'] ?? '',
            'cta_url' => $_POST['share_data']['cta_url'] ?? '',
            'hashtags' => Growtype_Post_Admin_Methods_Meta_Content::extract_hashtags_from_string($post_content),
        ];

        $platform_class = sprintf('Growtype_Post_Admin_Methods_Share_%s', ucfirst($platform));

        $share_response = apply_filters('growtype_post_admin_methods_share_response', [], $account_details, $post_details, $platform_class);

        return $share_response;
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

    public static function update_already_shared_on_platforms_details($post_id, $shared_details, $reset = false)
    {
        $already_shared_on_platforms = $shared_details;

        if (!$reset) {
            $already_shared_on_platforms = get_post_meta($post_id, 'growtype_post_is_already_shared_on_platforms', true);
            $already_shared_on_platforms = !empty($already_shared_on_platforms) ? $already_shared_on_platforms : [];
            $already_shared_on_platforms = growtype_post_merge_arrays_recursively($already_shared_on_platforms, $shared_details);
        }

        update_post_meta($post_id, 'growtype_post_is_already_shared_on_platforms', $already_shared_on_platforms);
    }

    public static function format_already_shared_on_platforms_details($platform, $auth_group_key, $account_channel, $response)
    {
        return [
            $platform => [
                $auth_group_key => [
                    $account_channel => [
                        'url' => $response['url'] ?? ''
                    ]
                ]
            ]
        ];
    }

    public static function check_if_post_is_already_shared_on_platform($post_id, $platform, $auth_group_key, $account_channel)
    {
        $already_shared_on_platforms = Growtype_Post_Admin_Methods_Meta_Share::already_shared_on_platforms($post_id);

        if (empty($account_channel)) {
            $account_channel = $auth_group_key;
        }

        if (isset($already_shared_on_platforms[$platform][$auth_group_key]) && in_array($account_channel, $already_shared_on_platforms[$platform][$auth_group_key])) {
            return true;
        }

        return isset($already_shared_on_platforms[$platform][$auth_group_key][$account_channel]) ? true : false;
    }

    public static function get_shared_post_external_url_for_platform($post_id, $platform, $auth_group_key, $account_channel)
    {
        $already_shared_on_platforms = Growtype_Post_Admin_Methods_Meta_Share::already_shared_on_platforms($post_id);

        if (empty($account_channel)) {
            $account_channel = $auth_group_key;
        }

        if (!isset($already_shared_on_platforms[$platform][$auth_group_key][$account_channel]['url'])) {
            return $already_shared_on_platforms[$platform][$auth_group_key]['url'] ?? '';
        }

        return $already_shared_on_platforms[$platform][$auth_group_key][$account_channel]['url'] ?? '';
    }

    public static function get_params_from_post_content($post_content)
    {
        // Extract Caption
        preg_match('/Caption:\s*(.+?)(?:\n|$)/', $post_content, $captionMatch);
        $caption = isset($captionMatch[1]) ? trim($captionMatch[1]) : '';
        $caption = strip_tags($caption);

        preg_match('/Tags:\s*(.+?)(?:\n|$)/', $post_content, $tagsMatch);
        $tags = isset($tagsMatch[1]) ? trim($tagsMatch[1]) : '';
        $tags = strip_tags($tags);

        if (empty($tags)) {
            preg_match_all('/#\w+/', $post_content, $matches);
            $tags = $matches[0] ?? [];
        }

        preg_match('/CTA url:\s*(.+?)(?:\n|$)/', $post_content, $ctaMatch);
        $cta_url = isset($ctaMatch[1]) ? trim($ctaMatch[1]) : '';
        $cta_url = strip_tags($cta_url);

        return [
            'caption' => $caption,
            'tags' => $tags,
            'cta_url' => $cta_url,
        ];
    }
}
