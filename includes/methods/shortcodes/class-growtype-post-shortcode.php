<?php

/**
 *
 */
class Growtype_Post_Shortcode
{
    function __construct()
    {
        if (!is_admin() && !wp_is_json_request()) {
            add_shortcode('growtype_post', array ($this, 'growtype_post_shortcode_callback'));
        }

        add_filter('wp_insert_post_data', [$this, 'wp_insert_post_data_callback'], 10, 2);
    }

    function wp_insert_post_data_callback($data, $post_arr)
    {
        foreach (['post_content', 'post_excerpt'] as $field) {
            if (!empty($data[$field]) && strpos($data[$field], '[growtype_post') !== false) {
                $data[$field] = preg_replace_callback(
                    '/\[growtype_post\b((?:\'[^\']*\'|"[^"]*"|[^\[\]]|\\\.)*)\]/',
                    function ($m) {
                        $attrs = $m[1];
                        $attrs = str_replace(
                            ['[', ']'],
                            ['&#91;', '&#93;'],
                            $attrs
                        );
                        return '[growtype_post' . $attrs . ']';
                    },
                    $data[$field]
                );
            }
        }

        return $data;
    }

    function growtype_post_shortcode_callback($attr)
    {
        if (is_customize_preview()) {
            return '';
        }

        $init = self::init($attr);

        return $init['render'];
    }

    /**
     * @param $attr
     * @return string
     * Posts shortcode
     */
    public static function init($attr)
    {
        $args = self::format_args($attr);

        if ($args['preview_style'] === 'product' && !class_exists('woocommerce')) {
            return [
                'render' => __('Please enable WooCommerce plugin to use this preview style!', 'growtype-post'),
                'args' => $args
            ];
        }

        if ($args['ajax_load_content']) {
            $args['ajax_load_content'] = false;

            return [
                'render' => sprintf("<div class='growtype-post-ajax-load-content' data-args='%s'><div class='spinner-wrapper'><span class='spinner-border'></span></div></div>", json_encode($args)),
                'args' => $args
            ];
        }

        $wp_query_response = self::query_posts($args);

        $args = array_merge($args, $wp_query_response['args'] ?? []);
        $wp_query = $wp_query_response['wp_query'] ?? [];

        /**
         * Show posts
         */
        $show_if_no_posts = $args['show_if_no_posts'] ? true : $wp_query->have_posts();

        if ($show_if_no_posts) {
            $render = self::render_all(
                $wp_query,
                $args
            );
        } elseif ($args['show_no_posts_text']) {
            $render = '<p class="growtype-post-no-posts-text">' . $args['no_posts_text'] . '</p>';
        }

        return [
            'render' => $render ?? '',
            'args' => $args,
            'wp_query' => $wp_query
        ];
    }

    public static function set_args($attr = [])
    {
        $args = [
            'post_type' => isset($attr['post_type']) ? $attr['post_type'] : get_post_type(),
            'content_source' => isset($attr['content_source']) ? $attr['content_source'] : 'internal',
            'content_url' => isset($attr['content_url']) ? $attr['content_url'] : '',
            'posts_per_page' => isset($attr['posts_per_page']) ? $attr['posts_per_page'] : get_option('posts_per_page'),
            'preview_style' => isset($attr['preview_style']) ? $attr['preview_style'] : 'basic', //blog
            'preview_style_custom' => isset($attr['preview_style_custom']) ? $attr['preview_style_custom'] : '', //blog
            'category_name' => isset($attr['category_name']) ? $attr['category_name'] : '', //use category slug
            'parent_class' => isset($attr['parent_class']) ? $attr['parent_class'] : '',
            'post_status' => isset($attr['post_status']) ? $attr['post_status'] : 'publish', //also active, expired
            'columns' => isset($attr['columns']) ? $attr['columns'] : '3',
            'post__in' => isset($attr['post__in']) ? $attr['post__in'] : [],
            'post__not_in' => isset($attr['post__not_in']) ? $attr['post__not_in'] : [],
            'category__not_in' => isset($attr['category__not_in']) ? $attr['category__not_in'] : [],
            'category__in' => isset($attr['category__in']) ? $attr['category__in'] : [],
            'order' => isset($attr['order']) ? $attr['order'] : 'asc',
            'shuffle' => isset($attr['shuffle']) ? filter_var($attr['shuffle'], FILTER_VALIDATE_BOOLEAN) : false,
            'orderby' => isset($attr['orderby']) ? $attr['orderby'] : 'menu_order',
            'meta_key' => isset($attr['meta_key']) ? $attr['meta_key'] : '',
            'parent_id' => isset($attr['parent_id']) && !empty($attr['parent_id']) ? $attr['parent_id'] : 'gpw-container',
            'intro_content_length' => isset($attr['intro_content_length']) ? $attr['intro_content_length'] : '100',
            'load_more_posts_loading_type' => isset($attr['load_more_posts_loading_type']) ? $attr['load_more_posts_loading_type'] : 'initial',
            'meta_query' => isset($attr['meta_query']) && !empty($attr['meta_query']) ? $attr['meta_query'] : null,
            'tax_query' => isset($attr['tax_query']) && !empty($attr['tax_query']) ? $attr['tax_query'] : null,
            'custom_args' => isset($attr['custom_args']) && !empty($attr['custom_args']) ? $attr['custom_args'] : null,
            'sticky_post' => isset($attr['sticky_post']) && !empty($attr['sticky_post']) ? $attr['sticky_post'] : 'none',
            'terms_navigation_taxonomy' => isset($attr['terms_navigation_taxonomy']) ? (is_array($attr['terms_navigation_taxonomy']) ? $attr['terms_navigation_taxonomy'] : explode(',', $attr['terms_navigation_taxonomy'])) : ['category'],
            'post_is_a_link' => isset($attr['post_is_a_link']) ? filter_var($attr['post_is_a_link'], FILTER_VALIDATE_BOOLEAN) : false,
            'post_in_modal' => isset($attr['post_in_modal']) ? filter_var($attr['post_in_modal'], FILTER_VALIDATE_BOOLEAN) : false,
            'pagination' => isset($attr['pagination']) ? filter_var($attr['pagination'], FILTER_VALIDATE_BOOLEAN) : false,
            'load_all_posts' => isset($attr['load_all_posts']) ? filter_var($attr['load_all_posts'], FILTER_VALIDATE_BOOLEAN) : false,
            'content_url_cache' => isset($attr['content_url_cache']) ? filter_var($attr['content_url_cache'], FILTER_VALIDATE_BOOLEAN) : false,
            'terms_navigation' => isset($attr['terms_navigation']) ? filter_var($attr['terms_navigation'], FILTER_VALIDATE_BOOLEAN) : false,
            'ajax_load_content' => isset($attr['ajax_load_content']) ? filter_var($attr['ajax_load_content'], FILTER_VALIDATE_BOOLEAN) : false,
            'terms_navigation_show_all_option_visible' => isset($attr['terms_navigation_show_all_option_visible']) ? filter_var($attr['terms_navigation_show_all_option_visible'], FILTER_VALIDATE_BOOLEAN) : false,
            'terms_navigation_selections_included_in_url' => isset($attr['terms_navigation_selections_included_in_url']) ? filter_var($attr['terms_navigation_selections_included_in_url'], FILTER_VALIDATE_BOOLEAN) : true,
            'terms_navigation_default_term_selected' => isset($attr['terms_navigation_default_term_selected']) ? $attr['terms_navigation_default_term_selected'] : '',
            'terms_navigation_select_allow_multiple_options' => isset($attr['terms_navigation_select_allow_multiple_options']) && filter_var($attr['terms_navigation_select_allow_multiple_options'], FILTER_VALIDATE_BOOLEAN) ? true : false,
            'terms_navigation_select_allow_single_deselect' => isset($attr['terms_navigation_select_allow_single_deselect']) && filter_var($attr['terms_navigation_select_allow_single_deselect'], FILTER_VALIDATE_BOOLEAN) ? true : false,
            'show_load_more_posts_btn' => isset($attr['show_load_more_posts_btn']) ? filter_var($attr['show_load_more_posts_btn'], FILTER_VALIDATE_BOOLEAN) : false,
            'custom_filters' => isset($attr['custom_filters']) ? filter_var($attr['custom_filters'], FILTER_VALIDATE_BOOLEAN) : false,
            'custom_filters_search_input_active' => isset($attr['custom_filters_search_input_active']) ? filter_var($attr['custom_filters_search_input_active'], FILTER_VALIDATE_BOOLEAN) : false,
            'custom_filters_orderby_select_active' => isset($attr['custom_filters_orderby_select_active']) ? filter_var($attr['custom_filters_orderby_select_active'], FILTER_VALIDATE_BOOLEAN) : false,
            'custom_filters_show_labels' => isset($attr['custom_filters_show_labels']) ? filter_var($attr['custom_filters_show_labels'], FILTER_VALIDATE_BOOLEAN) : false,
            'custom_filters_included' => isset($attr['custom_filters_included']) && !empty($attr['custom_filters_included']) ? $attr['custom_filters_included'] : [],
            'show_if_no_posts' => isset($attr['show_if_no_posts']) && !empty($attr['show_if_no_posts']) && $attr['show_if_no_posts'] ? true : false,
            'selected_terms_navigation_values' => isset($attr['selected_terms_navigation_values']) && !empty($attr['selected_terms_navigation_values']) && $attr['selected_terms_navigation_values'] ? $attr['selected_terms_navigation_values'] : [],
            'terms_navigation_default_term_trigger_type' => isset($attr['terms_navigation_default_term_trigger_type']) ? $attr['terms_navigation_default_term_trigger_type'] : 'click',
            'terms_navigation_style' => isset($attr['terms_navigation_style']) ? $attr['terms_navigation_style'] : 'buttons',
            'terms_navigation_style_mobile' => isset($attr['terms_navigation_style_mobile']) ? $attr['terms_navigation_style_mobile'] : 'select',
            'infinite_load_posts' => isset($attr['infinite_load_posts']) ? $attr['infinite_load_posts'] : false,
            'show_custom_tax_posts' => isset($attr['show_custom_tax_posts']) ? $attr['show_custom_tax_posts'] : false,
            'custom_tax' => isset($attr['custom_tax']) ? $attr['custom_tax'] : null,
            'custom_tax_slug' => isset($attr['custom_tax_slug']) ? $attr['custom_tax_slug'] : null,
            'custom_tax_level' => isset($attr['custom_tax_level']) ? $attr['custom_tax_level'] : null,
            'cta_label' => isset($attr['cta_label']) ? $attr['cta_label'] : apply_filters('growtype_post_cta_label', __('Continue reading', 'growtype-post')),
            'show_filters_visibility_trigger' => isset($attr['show_filters_visibility_trigger']) && filter_var($attr['show_filters_visibility_trigger'], FILTER_VALIDATE_BOOLEAN) ? true : false,
            'filters_visible_by_default' => isset($attr['filters_visible_by_default']) && $attr['filters_visible_by_default'] ? '1' : '0',
            'show_no_posts_text' => isset($attr['show_no_posts_text']) && $attr['show_no_posts_text'] ? true : false,
            'no_posts_text' => isset($attr['no_posts_text']) ? $attr['no_posts_text'] : '',
        ];

        // Apply custom args
        if (!empty($attr['custom_args'])) {
            $custom_args = [];
            if (is_array($attr['custom_args'])) {
                $custom_args = $attr['custom_args'];
            } else {
                $decoded = json_decode(urldecode($attr['custom_args']), true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $custom_args = $decoded;
                }
            }

            foreach ($custom_args as $key => $custom_arg) {
                $args[$key] = $custom_arg;
            }
        }

        $args['posts_per_page_mobile'] = isset($attr['posts_per_page_mobile']) ? $attr['posts_per_page_mobile'] : $args['posts_per_page'];

        $args['offset'] = isset($args['offset']) ? $args['offset'] : growtype_post_get_pagination_offset($args['posts_per_page']);

        if (empty($args['tax_query'])) {
            $queried_object = get_queried_object();

            if (!empty($queried_object)) {
                $queried_object_slug = $queried_object->slug;
                if (!empty($queried_object_slug)) {
                    $tax_query_args = [
                        'taxonomy' => $queried_object->taxonomy,
                        'field' => 'slug',
                        'terms' => $queried_object_slug,
                    ];

                    if ($queried_object->parent == 0) {
                        $tax_query_args['include_children'] = false;
                    } else {
                        $tax_query_args['include_children'] = true;
                    }

                    $args['tax_query'][] = $tax_query_args;
                }
            }
        }

        $args['post_type'] = !is_array($args['post_type']) ? explode(',', $args['post_type']) : $args['post_type'];

        /**
         * Preview style
         */
        if ($args['preview_style'] === 'custom' && !empty($args['preview_style_custom'])) {
            $args['preview_style'] = $args['preview_style_custom'];
        }

        /**
         * Custom filters included
         */
        if (is_string($args['custom_filters_included'])) {
            $args['custom_filters_included'] = explode(',', $args['custom_filters_included']);
        }

        $args['template_slug'] = isset($attr['template_slug']) ? $attr['template_slug'] : 'preview.' . $args['preview_style'] . '.index';

        $post_classes_list = ['growtype-post-single'];

        array_push($post_classes_list, 'growtype-post-' . $args['preview_style']);

        if (!empty($post_type)) {
            array_push($post_classes_list, 'growtype-post-' . implode('-', $args['post_type']));
        }

        $args['post_classes'] = isset($attr['post_classes']) && !empty($attr['post_classes']) ? implode(' ', $post_classes_list) . ' ' . $attr['post_classes'] : implode(' ', $post_classes_list);
        $args['post_classes'] = explode(' ', $args['post_classes']);
        $args['post_classes'] = array_unique($args['post_classes']);
        $args['post_classes'] = implode(' ', $args['post_classes']);

        if (isset($args['meta_query']) && !empty($args['meta_query']) && is_string($args['meta_query'])) {
            $args['meta_query'] = html_entity_decode($args['meta_query'], ENT_QUOTES);
        }

        return $args;
    }

    public static function format_args($attr = [])
    {
        $args = self::set_args($attr);

        return apply_filters('growtype_post_shortcode_format_args', $args, $attr);
    }

    public static function query_posts($args)
    {
        $posts_per_page = (int)$args['posts_per_page'];

        /**
         * Show all posts
         */
        if ($args['load_all_posts'] && in_array($args['load_more_posts_loading_type'], ['initial', 'limited'])) {
            $posts_per_page = -1;
        }

        $query_args = array (
            'post_type' => $args['post_type'],
            'posts_per_page' => $posts_per_page,
            'orderby' => $args['orderby'],
            'order' => $args['order']
        );

        if (!empty($args['meta_key'])) {
            $query_args['meta_key'] = $args['meta_key'];
        }

        if (!empty($args['child_of'])) {
            $query_args['child_of'] = $args['child_of'];
        }

        if (!empty($args['post_parent'])) {
            $query_args['post_parent'] = $args['post_parent'];
        }

        if (!empty($args['category__not_in'])) {
            $query_args['category__not_in'] = explode(',', $args['category__not_in']);
        }

        if (!empty($args['category__in'])) {
            $query_args['category__in'] = explode(',', $args['category__in']);
        }

        if (!empty($args['post__in'])) {
            if (is_string($args['post__in'])) {
                $post_in = explode(',', $args['post__in']);
            } else {
                $post_in = array_filter($args['post__in'], function ($value) {
                    return ($value !== null && $value !== false && $value !== '' && !empty($value));
                });
            }

            if (!empty($post_in)) {
                $query_args['post__in'] = $post_in;
            }
        }

        if (!empty($args['post__not_in'])) {
            $query_args['post__not_in'] = is_string($args['post__not_in']) ? explode(',', $args['post__not_in']) : $args['post__not_in'];
        }

        /**
         * Display sticky posts
         */
        if ($args['sticky_post'] !== 'none') {
            if ($args['sticky_post'] === 'visible') {
                $parent_class = explode(',', $args['parent_class']);
                $parent_class = array_filter($parent_class);
                array_push($parent_class, 'has-sticky-post');
                $parent_class = implode(' ', $parent_class);
                $sticky_posts_ids = !empty(get_option('sticky_posts')) ? get_option('sticky_posts') : ['0'];
                $sticky_posts_ids = apply_filters('growtype_post_shortcode_sticky_posts_ids', $sticky_posts_ids, $args);
                $query_args['post__in'] = $sticky_posts_ids;
            } elseif ($args['sticky_post'] === 'hidden' && !empty(get_option('sticky_posts'))) {
                $post__not_in = !empty($query_args['post__not_in']) ? $query_args['post__not_in'] : [];
                $query_args['post__not_in'] = array_merge($post__not_in, get_option('sticky_posts'));
            }
        }

        if (!empty($args['category_name'])) {
            if (in_array('product', $args['post_type'])) {
                $query_args['tax_query'] = array (
                    array (
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' => [$args['category_name']],
                        'operator' => 'IN',
                    )
                );
            } else {
                $query_args['category_name'] = $args['category_name'];
            }
        }

        if ($args['pagination'] && growtype_post_get_pagination_page_nr() > 1) {
            $query_args['offset'] = growtype_post_get_pagination_offset($posts_per_page);
        }

        /**
         * Adjust query according to source
         */
        if ($args['content_source'] === 'internal') {

            /**
             * For multisite sites
             */
            if (in_array('multisite_sites', $args['post_type'])) {
                $site__not_in = [get_main_site_id()];

                if ($args['post_status'] === 'active' || $args['post_status'] === 'expired') {
                    $all_pages = get_sites([
                        'number' => 1000,
                        'site__not_in' => $site__not_in,
                    ]);

                    foreach ($all_pages as $post) {
                        $field_details = Growtype_Site::get_settings_field_details($post->blog_id, 'event_start_date');
                        if ($args['post_status'] === 'active' && $field_details->option_value < wp_date('Y-m-d')
                            || $args['post_status'] === 'expired' && $field_details->option_value > wp_date('Y-m-d')
                        ) {
                            array_push($site__not_in, $post->blog_id);
                        }
                    }
                }

                /**
                 * Total existing records for pagination
                 */
                if ($args['pagination']) {
                    $total_existing_records = get_sites([
                        'number' => 1000,
                        'site__not_in' => $site__not_in,
                    ]);

                    $args['total_pages'] = round(count($total_existing_records) / $posts_per_page);
                }

                $wp_query = new WP_Site_Query([
                    'number' => $posts_per_page === -1 ? 100 : $posts_per_page,
                    'site__not_in' => $site__not_in,
                    'offset' => growtype_post_get_pagination_offset($posts_per_page)
                ]);
            } else {
                /**
                 * Include custom meta query
                 */
                if (!empty($args['meta_query'])) {
                    $query_args['meta_query'] = is_array($args['meta_query']) ? $args['meta_query'] : json_decode(urldecode($args['meta_query']), true);

                    if (!empty($query_args['meta_query'])) {
                        foreach ($query_args['meta_query'] as $key => $meta_query) {
                            if (isset($meta_query['value']) && $meta_query['value'] === '$current_date') {
                                $query_args['meta_query'][$key]['value'] = date('Ymd');
                            }
                        }
                    }
                }

                /**
                 * Include custom tax query
                 */
                if (!empty($args['tax_query'])) {
                    $query_args['tax_query'] = is_array($args['tax_query']) ? $args['tax_query'] : json_decode(urldecode($args['tax_query']), true);
                }

                /**
                 * Terms navigation
                 */
                if (isset($args['selected_terms_navigation_values']) && !empty($args['selected_terms_navigation_values'])) {
                    foreach ($args['selected_terms_navigation_values'] as $tax => $term) {
                        if (in_array('all', $term) || !taxonomy_exists($tax)) {
                            continue;
                        }

                        $query_args['tax_query'][] = [
                            'taxonomy' => $tax,
                            'field' => 'slug',
                            'terms' => $term
                        ];
                    }
                }

                if (isset($args['show_custom_tax_posts']) && $args['show_custom_tax_posts']) {
                    $tax_value = $args['custom_tax_slug'] ?? '';
                    $custom_tax = $args['custom_tax'] ?? '';

                    if (!empty($tax_value) || empty($custom_tax)) {
                        $term = get_term_by('slug', $tax_value, $custom_tax);

                        $tax_level = $args['custom_tax_level'] ?? 'any';

                        if ($tax_level === 'parent') {
                            $parent_term_id = $term->term_id;

                            $child_terms = get_terms(array (
                                'taxonomy' => $custom_tax,
                                'parent' => $parent_term_id,
                                'hide_empty' => false,
                            ));

                            $child_term_ids = wp_list_pluck($child_terms, 'term_id');

                            $query_args['tax_query'][] = [
                                'taxonomy' => $custom_tax,
                                'field' => 'term_id',
                                'terms' => $child_term_ids,
                                'include_children' => false
                            ];
                        }
                    }
                }

                $query_args = apply_filters('growtype_post_shortcode_extend_args', $query_args, $args);

                $wp_query = new WP_Query($query_args);

                /**
                 * Total existing records for pagination
                 */
                if ($args['pagination']) {
                    $total_pages = 0;
                    foreach ($query_args['post_type'] as $post_type) {
                        $total_pages += round(wp_count_posts($post_type)->publish / $posts_per_page);
                    }
                    $args['total_pages'] = $total_pages;
                }
            }
        } else {
            $content_url = $args['content_url'];

            $params = ['orderby', 'order'];

            foreach ($params as $key => $param) {
                $separator = $key === 0 ? '?' : '&';
                $content_url .= $separator . $param . '=' . $args[$param];
            }

            $transient_name = sprintf('growtype_post_shortcode_%s_%s', $args['content_source'], md5($content_url));
            $transient_name = apply_filters('growtype_post_shortcode_transient_name', $transient_name, $args);
            $transient_name = substr($transient_name, 0, 150);

            /**
             * Check if custom source exists
             */
            if (has_filter('growtype_post_content_sourced')) {
                $posts = apply_filters('growtype_post_content_sourced', [], $args);
            } else {
                $posts = get_transient($transient_name);

                if (!$args['content_url_cache']) {
                    if (!empty($posts)) {
                        delete_transient($transient_name);
                    }

                    $posts = [];
                }

                if (empty($posts) || !$posts) {
                    $content_url = apply_filters('growtype_post_shortcode_extend_content_url', $content_url, $args);

                    $response = wp_remote_get($content_url, [
                        'timeout' => 30,
                        'sslverify' => false
                    ]);

                    $posts = is_array($response) && !is_wp_error($response) ? json_decode($response['body'], true) : [];

                    $posts = apply_filters('growtype_post_shortcode_modify_remote_response_body', $posts, $args);

                    if ($args['content_url_cache']) {
                        set_transient($transient_name, $posts, MONTH_IN_SECONDS);
                    }
                }
            }

            /**
             * Exclude posts
             */
            if (isset($args['post__not_in']) && !empty($args['post__not_in'])) {
                foreach ($posts as $key => $post) {
                    if (isset($post['id']) && in_array($post['id'], $args['post__not_in'])) {
                        unset($posts[$key]);
                    }
                }
            }

            if (!empty($posts) && (!$args['load_all_posts'] || ($args['load_all_posts'] && $args['load_more_posts_loading_type'] === 'ajax'))) {
                $posts = array_slice($posts, growtype_post_get_pagination_offset($posts_per_page), $posts_per_page);
            }

            $formatted_posts = self::format_posts_as_wp_posts($posts);

            $formatted_posts = array_map(function ($post) {
                return $post instanceof WP_Post ? $post : new WP_Post((object)$post);
            }, $formatted_posts);

            $wp_query = new WP_Query([
                'post_type' => 'any',
                'posts_per_page' => -1,
                'fields' => 'all',
            ]);

            $wp_query->posts = $formatted_posts;
            $wp_query->post_count = count($formatted_posts);
            $wp_query->found_posts = count($formatted_posts);
            $wp_query->max_num_pages = 1;
            $wp_query->is_posts_page = true;
            $wp_query->is_main_query = false;

            foreach ($formatted_posts as $post) {
                if ($post instanceof WP_Post) {
                    wp_cache_set($post->ID, $post, 'posts');
                }
            }
        }

        return [
            'wp_query' => $wp_query,
            'args' => $args
        ];
    }

    public static function format_posts_as_wp_posts($posts)
    {
        $formatted_posts = [];
        if (!empty($posts)) {
            foreach ($posts as $post) {
                if (!empty($post) && is_array($post)) {
                    if (!isset($post['id'])) {
                        error_log(print_r(['error' => 'Post ID is missing', 'post_details' => $post[0] ?? ''], true));
                        continue;
                    }

                    $formatted_post = self::convert_to_wp_post($post);

                    foreach ($post as $key => $post_value) {
                        if (!in_array($key, ['id', 'author', 'date', 'date_gmt', 'content', 'title', 'excerpt', 'status', 'slug', 'modified', 'modified_gmt', 'parent', 'guid', 'menu_order', 'type'])) {
                            $formatted_post->$key = $post_value;
                        }
                    }

                    $formatted_post = apply_filters('growtype_post_format_posts_as_wp_posts_formatted_post', $formatted_post, $post);

                    array_push($formatted_posts, $formatted_post);
                }
            }
        }

        return $formatted_posts;
    }

    public static function convert_to_wp_post($post = [])
    {
        $data = [
            'ID' => isset($post['id']) ? $post['id'] : 0,
            'post_author' => isset($post['author']) ? $post['author'] : 'admin',
            'post_date' => isset($post['date']) ? $post['date'] : date(get_option('date_format')),
            'post_date_gmt' => isset($post['date_gmt']) ? $post['date_gmt'] : date(get_option('date_format')),
            'post_content' => isset($post['content']['rendered']) ? $post['content']['rendered'] : '',
            'post_title' => isset($post['title']['rendered']) ? $post['title']['rendered'] : '',
            'post_excerpt' => isset($post['excerpt']['rendered']) ? $post['excerpt']['rendered'] : '',
            'post_status' => isset($post['status']) ? $post['status'] : 'draft',
            'comment_status' => isset($post['comment_status']) ? $post['comment_status'] : 'closed',
            'ping_status' => isset($post['ping_status']) ? $post['ping_status'] : 'closed',
            'post_password' => isset($post['post_password']) ? $post['post_password'] : '',
            'post_name' => isset($post['slug']) ? $post['slug'] : '',
            'to_ping' => isset($post['to_ping']) ? $post['to_ping'] : '',
            'pinged' => isset($post['pinged']) ? $post['pinged'] : '',
            'post_modified' => isset($post['modified']) ? $post['modified'] : '',
            'post_modified_gmt' => isset($post['modified_gmt']) ? $post['modified_gmt'] : '',
            'post_content_filtered' => isset($post['post_content_filtered']) ? $post['post_content_filtered'] : '',
            'post_parent' => isset($post['parent']) ? $post['parent'] : '',
            'guid' => isset($post['guid']['rendered']) ? $post['guid']['rendered'] : '',
            'menu_order' => isset($post['menu_order']) ? $post['menu_order'] : '',
            'post_type' => isset($post['post_type']) ? $post['post_type'] : '',
            'post_mime_type' => isset($post['post_mime_type']) ? $post['post_mime_type'] : '',
            'comment_count' => isset($post['comment_count']) ? $post['comment_count'] : '0',
            'filter' => isset($post['filter']) ? $post['filter'] : 'raw',
        ];

        return new WP_Post((object)$data);
    }

    /**
     * @param $wp_query
     * @param $args
     * @return false|string
     */
    public static function render_all(
        $wp_query = null,
        $args = []
    ) {

        $args = self::set_args($args);

        /**
         * Prepare default wp_query if empty wp_query is passed
         */
        if (empty($wp_query)) {
            $wp_query = new WP_Query($args);

            $args['columns'] = isset($args['columns']) ? $args['columns'] : 3;
        }

        $pages_amount = 0;
        $initially_retrieved_posts = $wp_query->post_count;

        if (isset($args['post_type']) && $args['post_type']) {
            $pages_amount = (int)ceil($wp_query->found_posts / $args['posts_per_page']);
        }

        $args['total_pages'] = isset($args['total_pages']) ? $args['total_pages'] : $pages_amount;

        $terms_navigation_taxonomies = isset($args['terms_navigation_taxonomy']) ? $args['terms_navigation_taxonomy'] : ['category'];

        $terms_groups = [];
        foreach ($terms_navigation_taxonomies as $terms_navigation_taxonomy) {
            $terms = get_terms(array (
                'taxonomy' => $terms_navigation_taxonomy,
                'hide_empty' => true,
                'exclude' => 1
            ));

            if (!is_wp_error($terms)) {
                $combined_values = $terms;

                if (isset($args['terms_navigation_show_all_option_visible']) && $args['terms_navigation_show_all_option_visible']) {
                    $combined_values = array_merge([
                        (object)[
                            'name' => 'All',
                            'slug' => 'all',
                        ]
                    ], $terms);
                }

                $terms = [
                    $terms_navigation_taxonomy => [
                        'values' => $combined_values,
                        'settings' => [
                            'placeholder' => apply_filters('growtype_post_terms_navigation_select_placeholder', 'Select ...'),
                            'is_multiple' => $args['terms_navigation_select_allow_multiple_options'] ?? false,
                            'allow_single_deselect' => $args['terms_navigation_select_allow_single_deselect'],
                        ]
                    ]
                ];
            }

            if (is_wp_error($terms)) {
                $terms = [];
            }

            $terms_groups = array_merge($terms_groups, $terms);
        }

        $terms = $terms_groups;

        $terms = apply_filters('growtype_post_shortcode_extend_terms', $terms, $wp_query, $args);

        $show_if_no_posts = $args['show_if_no_posts'] ? true : $wp_query->have_posts();

        ob_start();

        if ($show_if_no_posts) :
            $wrapper_container_classes = ['growtype-post-container-wrapper'];
            if (isset($args['parent_class']) && !empty($args['parent_class'])) {
                array_push($wrapper_container_classes, $args['parent_class']);
            }
            ?>
            <div
                id="<?php echo $args['parent_id'] ?>"
                class="<?php echo implode(' ', $wrapper_container_classes) ?>"
                data-post-type="<?php echo implode(',', $args['post_type'] ?? []) ?>"
                data-preview-style="<?php echo $args['preview_style'] ?>"
                data-content-source="<?php echo $args['content_source'] ?>"
                data-content-url-cache="<?php echo $args['content_url_cache'] ?>"
                data-args='<?php echo json_encode($args) ?>'
            >
                <?php if (isset($args['terms_navigation']) && $args['terms_navigation'] || isset($args['custom_filters']) && $args['custom_filters']) { ?>
                    <div class="growtype-post-filters-wrapper" data-filters-visible="<?php echo $args['filters_visible_by_default'] ?>">
                        <?php
                        do_action('growtype_post_filters_after_open', $terms, $terms_navigation_taxonomies, $args);

                        if (isset($args['show_filters_visibility_trigger']) && $args['show_filters_visibility_trigger']) {
                            ?>
                            <div class="growtype-post-filters-visibility-trigger">
                                <span class="growtype-post-filters-label"><?php echo __('Filters', 'growtype-post') ?></span>
                                <div class="growtype-post-filters-icon">
                                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3 17v2h6v-2H3zM3 5v2h10V5H3zm10 16v-2h8v-2h-8v-2h-2v6h2zM7 9v2H3v2h4v2h2V9H7zm14 4v-2H11v2h10zm-6-4h2V7h4V5h-4V3h-2v6z"></path>
                                    </svg>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <?php if (isset($args['terms_navigation']) && $args['terms_navigation']) { ?>
                            <?php echo growtype_post_include_view('filter.terms-navigation', [
                                'terms' => $terms,
                                'terms_navigation_taxonomies' => $terms_navigation_taxonomies,
                                'args' => $args
                            ]) ?>
                        <?php } ?>
                        <?php if (isset($args['custom_filters']) && $args['custom_filters']) { ?>
                            <div class="growtype-post-custom-filters">
                                <?php do_action('growtype_post_custom_filters_after_open', $terms, $terms_navigation_taxonomies, $args); ?>

                                <?php
                                $custom_filters = [
                                    'search' => [
                                        'active' => isset($args['custom_filters_search_input_active']) && $args['custom_filters_search_input_active'],
                                        'type' => 'text',
                                        'label' => 'Search',
                                        'name' => 'search',
                                    ],
                                    'orderby' => [
                                        'active' => isset($args['custom_filters_orderby_select_active']) && $args['custom_filters_orderby_select_active'],
                                        'type' => 'select',
                                        'label' => 'Orderby',
                                        'ajax' => true,
                                        'name' => 'orderby',
                                        'disable_search' => true,
                                        'options' => apply_filters('growtype_post_custom_filters_orderby_options', [
                                            'date' => 'Date',
                                            'name' => 'Name',
                                            'menu_order' => 'Menu Order',
                                        ]),
                                    ]
                                ];

                                $custom_filters = apply_filters('growtype_post_custom_filters', $custom_filters, $args);

                                foreach ($custom_filters as $custom_filter) {
                                    if (!$custom_filter['active']) {
                                        continue;
                                    }

                                    if ($custom_filter['type'] === 'select') { ?>
                                        <div class="growtype-post-custom-filters-single" data-name="<?php echo $custom_filter['name'] ?>" data-ajax="<?php echo filter_var($custom_filter['ajax'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false' ?>">
                                            <?php if (isset($args['custom_filters_show_labels']) && $args['custom_filters_show_labels']) { ?>
                                                <label for="<?php echo $custom_filter['name'] ?>"><?php echo $custom_filter['label'] ?></label>
                                            <?php } ?>
                                            <select class="growtype-post-custom-filter" name="<?php echo $custom_filter['name'] ?>" data-disable-search="<?php echo filter_var($custom_filter['disable_search'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false' ?>">
                                                <?php foreach ($custom_filter['options'] as $key => $value) { ?>
                                                    <option value="<?php echo $key ?>" <?= $key === $args['orderby'] ? 'selected' : '' ?>><?php echo $value ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    <?php } else { ?>
                                        <div class="growtype-post-custom-filters-single" data-name="<?php echo $custom_filter['name'] ?>">
                                            <?php if (isset($args['custom_filters_show_labels']) && $args['custom_filters_show_labels']) { ?>
                                                <label for="<?php echo $custom_filter['name'] ?>"><?php echo $custom_filter['label'] ?></label>
                                            <?php } ?>
                                            <input type="text" name="<?php echo $custom_filter['name'] ?>"/>
                                        </div>
                                    <?php }
                                }
                                ?>

                                <?php do_action('growtype_post_custom_filters_before_close', $terms, $terms_navigation_taxonomies, $args); ?>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>

                <?php

                $container_classes = ['growtype-post-container'];

                $load_more_posts_loading_type = isset($args['load_more_posts_loading_type']) ? $args['load_more_posts_loading_type'] : 'initial';
                $visible_posts = isset($args['posts_per_page']) ? $args['posts_per_page'] : '';
                $visible_posts_mobile = isset($args['posts_per_page_mobile']) ? $args['posts_per_page_mobile'] : $visible_posts;

                if ($load_more_posts_loading_type === 'initial' && $args['load_all_posts']) {
                    $visible_posts = '-1';
                }

                $posts_columns_amount = isset($args['columns']) ? $args['columns'] : 1;
                ?>

                <div class="<?php echo implode(' ', $container_classes) ?>"
                     data-columns="<?php echo $posts_columns_amount ?>"
                     data-visible-posts="<?php echo $visible_posts ?>"
                     data-visible-posts-mobile="<?php echo $visible_posts_mobile ?>"
                     data-initially-retrieved-posts="<?php echo $initially_retrieved_posts ?>"
                     data-load-more-posts-loading-type="<?php echo $load_more_posts_loading_type ?>"
                     style="--growtype-post-posts-grid-columns-count:<?php echo $posts_columns_amount ?>;"
                >
                    <?php echo self::render_posts($wp_query, $args, $terms) ?>
                </div>

                <?php if (isset($args['post_in_modal']) && $args['post_in_modal']) { ?>
                    <?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
                        <div class="modal modal-growtype-post fade" id="<?php echo 'growtype-post-modal-' . get_the_ID() . '"' ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <?php echo growtype_post_include_view(
                                        'modal.content',
                                        [
                                            'post' => get_post()
                                        ]
                                    ); ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php } ?>

                <?php if (isset($args['load_all_posts']) && $args['load_all_posts'] && isset($args['show_load_more_posts_btn']) && $args['show_load_more_posts_btn']) { ?>
                    <div class="gp-actions-wrapper">
                        <button class="btn btn-primary btn-loadmore"><?php echo apply_filters('growtype_post_load_more_posts_btn_label', __('Load more', 'growtype-post')) ?></button>
                    </div>
                <?php } ?>

            </div>
        <?php endif;

        /**
         * Pagination
         */
        if (isset($args['pagination']) && $args['pagination']) { ?>
            <div class="pagination">
                <?php echo self::pagination($wp_query, $args['total_pages']); ?>
            </div>
            <?php
        }

        wp_reset_postdata();

        $html = ob_get_clean();

        return $html;
    }

    public static function render_posts($wp_query, $args, $terms = [])
    {
        $args['growtype_post_preview_featured_image_enabled'] = get_theme_mod('growtype_post_preview_featured_image_enabled', true);
        $args['growtype_post_preview_categories_enabled'] = get_theme_mod('growtype_post_preview_categories_enabled', true);
        $args['growtype_post_preview_date_enabled'] = get_theme_mod('growtype_post_preview_date_enabled', true);
        $args['growtype_post_preview_continue_actions_enabled'] = get_theme_mod('growtype_post_preview_continue_actions_enabled', true);

        $wp_query = apply_filters('growtype_post_before_render_posts_wp_query', $wp_query, $args, $terms);

        ob_start();
        $counter = 0;
        while ($wp_query->have_posts()) {
            $wp_query->the_post();
            $post_id = get_the_ID();
            $current_post = get_post();
            $existing_args = apply_filters('growtype_post_shortcode_extend_post_args', $args, $current_post, $terms);

            if (!empty($counter) && isset($existing_args['posts_per_page']) && $existing_args['posts_per_page'] > 0 && $counter >= $existing_args['posts_per_page']) {
                if (!empty($args['load_more_posts_loading_type'])) {
                    if ($args['load_more_posts_loading_type'] === 'limited') {
                        $existing_args['post_classes'] = trim(($existing_args['post_classes'] ?? '') . ' is-hidden');
                    } elseif ($args['load_more_posts_loading_type'] === 'ajax') {
                        continue; // Skip rendering this post
                    }
                }
            }

            $current_post = apply_filters('growtype_post_shortcode_extend_post', $current_post, $args);

            $existing_args['post_is_a_link'] = $current_post->post_is_a_link ?? $args['post_is_a_link'] ?? false;
            $existing_args['post_permalink'] = $current_post->post_permalink ?? get_permalink();

            if (empty($existing_args['post_terms'])) {
                if (!empty($current_post->post_terms)) {
                    $existing_args['post_terms'] = $current_post->post_terms;
                } else {
                    // Fetch terms if not provided
                    if (empty($terms) && !empty($args['terms_navigation_taxonomy'])) {
                        // Ensure we have a string taxonomy name for get_the_terms()
                        $taxonomy_for_fetch = is_array($args['terms_navigation_taxonomy']) 
                            ? $args['terms_navigation_taxonomy'][0] 
                            : $args['terms_navigation_taxonomy'];
                        
                        $default_terms = get_the_terms($post_id, $taxonomy_for_fetch) ?: [];
                        if (!empty($default_terms) && !is_wp_error($default_terms)) {
                            foreach ($default_terms as $default_term) {
                                $terms[$default_term->taxonomy] = $default_terms;
                            }
                        }
                    }

                    // Assign terms to existing_args
                    foreach ($terms as $taxonomy => $term) {
                        $term_list = wp_get_post_terms($post_id, $taxonomy);
                        $existing_args['post_terms'][$taxonomy] = is_wp_error($term_list) ? [] : $term_list;
                    }
                }
            }

            $existing_args['counter'] = $counter;

            // Handle custom search filters
            if (!empty($existing_args['custom_filters']) && !empty($args['custom_filters_search_input_active'])) {
                $existing_args['post_terms']['search'] = [
                    [
                        'slug' => get_the_title($current_post) . '|' . get_the_excerpt($current_post),
                    ]
                ];
            }

            // Format post terms HTML
            $existing_args['post_terms_html'] = self::format_post_terms_html($existing_args['post_terms'] ?? []);

            // Render the post
            echo self::render_single_post($current_post, $existing_args);

            $counter++;
        }

        $html = ob_get_clean();

        return apply_filters('growtype_post_render_posts_html', $html, $args, $terms);
    }

    public static function format_post_terms_html($terms)
    {
        $html = '';
        foreach ($terms as $key => $term) {
            $term_slugs = array_pluck($term, 'slug');
            $term_parents = array_pluck($term, 'parent');

            foreach ($term_parents as $term_parent) {
                if (!empty($term_parent)) {
                    $term = get_term($term_parent, $term->taxonomy);
                    if (!empty($term)) {
                        array_push($term_slugs, $term->slug);
                    }
                }
            }

            $html .= 'data-cat-' . $key . '="' . implode(',', $term_slugs) . '" ';
        }

        return $html;
    }

    /**
     * @param $post
     * @param $args
     * @return false|string|null
     */
    public static function render_single_post($post, $args)
    {
        $args = array_merge(['post' => $post], $args);

        $preview_style = $args['preview_style'] ?? 'basic';

        if (!isset($args['template_slug']) || empty($args['template_slug'])) {
            $args['template_slug'] = 'preview.' . $preview_style . '.index';
        }

        return growtype_post_include_view($args['template_slug'], $args);
    }

    /**
     * @param null $custom_query
     */
    public static function pagination($custom_query = null, $total_pages = null)
    {
        if (empty($custom_query)) {
            global $wp_query;
            $custom_query = $wp_query;
        }

        $total_pages = !empty($total_pages) ? $total_pages : (is_object($custom_query) ? $custom_query->max_num_pages : count($custom_query));

        $big = 999999999;

        if ($total_pages > 1) {
            $current_page = growtype_post_get_pagination_page_nr();

            echo paginate_links(array (
                'prev_text' => '<span class="dashicons dashicons-arrow-left-alt2"></span>',
                'next_text' => '<span class="dashicons dashicons-arrow-right-alt2"></span>',
                'type' => 'list',
                'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                'format' => '?page=%#%',
                'current' => $current_page,
                'total' => $total_pages,
            ));
        }
    }
}
