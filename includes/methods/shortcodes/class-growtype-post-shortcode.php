<?php

/**
 *
 */
class Growtype_Post_Shortcode
{
    function __construct()
    {
        if (!is_admin() && !wp_is_json_request()) {
            add_shortcode('growtype_post', array ($this, 'growtype_post_shortcode'));
        }
    }

    /**
     * @param $attr
     * @return string
     * Posts shortcode
     */
    function growtype_post_shortcode($attr)
    {
        $args = [
            'post_type' => isset($attr['post_type']) ? $attr['post_type'] : 'post',
            'content_source' => isset($attr['content_source']) ? $attr['content_source'] : 'internal',
            'content_url' => isset($attr['content_url']) ? $attr['content_url'] : '',
            'posts_per_page' => isset($attr['posts_per_page']) ? $attr['posts_per_page'] : -1,
            'preview_style' => isset($attr['preview_style']) ? $attr['preview_style'] : 'basic', //blog
            'preview_style_custom' => isset($attr['preview_style_custom']) ? $attr['preview_style_custom'] : '', //blog
            'category_name' => isset($attr['category_name']) ? $attr['category_name'] : '', //use category slug
            'parent_class' => isset($attr['parent_class']) ? $attr['parent_class'] : '',
            'post_status' => isset($attr['post_status']) ? $attr['post_status'] : 'publish', //also active, expired
            'columns' => isset($attr['columns']) ? $attr['columns'] : '3',
            'post__in' => isset($attr['post__in']) ? $attr['post__in'] : [],
            'category__not_in' => isset($attr['category__not_in']) ? $attr['category__not_in'] : [],
            'category__in' => isset($attr['category__in']) ? $attr['category__in'] : [],
            'order' => isset($attr['order']) ? $attr['order'] : 'asc',
            'orderby' => isset($attr['orderby']) ? $attr['orderby'] : 'menu_order',
            'meta_key' => isset($attr['meta_key']) ? $attr['meta_key'] : '',
            'parent_id' => isset($attr['parent_id']) ? $attr['parent_id'] : '',
            'intro_content_length' => isset($attr['intro_content_length']) ? $attr['intro_content_length'] : '100',
            'loading_type' => isset($attr['loading_type']) ? $attr['loading_type'] : 'initial',
            'post_is_a_link' => isset($attr['post_is_a_link']) && $attr['post_is_a_link'] ? true : false,
            'post_in_modal' => isset($attr['post_in_modal']) && $attr['post_in_modal'] ? true : false,
            'pagination' => isset($attr['pagination']) && $attr['pagination'] ? true : false,
            'load_all_posts' => isset($attr['load_all_posts']) && $attr['load_all_posts'] ? true : false,
            'meta_query' => isset($attr['meta_query']) && !empty($attr['meta_query']) ? $attr['meta_query'] : null,
            'tax_query' => isset($attr['tax_query']) && !empty($attr['tax_query']) ? $attr['tax_query'] : null,
            'sticky_post' => isset($attr['sticky_post']) && !empty($attr['sticky_post']) ? $attr['sticky_post'] : 'none',
            'content_url_cache' => isset($attr['content_url_cache']) && $attr['content_url_cache'] ? true : false,
            'terms_navigation' => isset($attr['terms_navigation']) && $attr['terms_navigation'] ? true : false,
            'terms_navigation_taxonomy' => isset($attr['terms_navigation_taxonomy']) ? explode(',', $attr['terms_navigation_taxonomy']) : ['category'],
        ];

        /**
         * Show all posts
         */
        if ($args['load_all_posts'] && $args['loading_type'] !== 'limited') {
            $args['posts_per_page'] = -1;
        }

        /**
         * Preview style
         */
        if ($args['preview_style'] === 'custom' && !empty($args['preview_style_custom'])) {
            $args['preview_style'] = $args['preview_style_custom'];
        } elseif ($args['preview_style'] === 'product' && !class_exists('woocommerce')) {
            return __('Please enable WooCommerce plugin to use this preview style!', 'growtype-post');
        }

        $query_args = array (
            'post_type' => $args['post_type'],
            'posts_per_page' => $args['posts_per_page'],
            'orderby' => $args['orderby'],
            'order' => $args['order']
        );

        if (!empty($args['meta_key'])) {
            $query_args['meta_key'] = $args['meta_key'];
        }

        if (!empty($args['category__not_in'])) {
            $query_args['category__not_in'] = explode(',', $args['category__not_in']);
        }

        if (!empty($args['category__in'])) {
            $query_args['category__in'] = explode(',', $args['category__in']);
        }

        if (!empty($args['post__in'])) {
            $query_args['post__in'] = explode(',', $args['post__in']);
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
                $query_args['post__in'] = !empty(get_option('sticky_posts')) ? get_option('sticky_posts') : ['0'];
            } elseif ($args['sticky_post'] === 'hidden' && !empty(get_option('sticky_posts'))) {
                $query_args['post__not_in'] = get_option('sticky_posts');
            }
        }

        if (!empty($args['category_name'])) {
            if (in_array($args['post_type'], ['product'])) {
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

        if ($args['pagination'] && !empty(get_query_var('paged'))) {
            $query_args['offset'] = growtype_post_get_pagination_offset($args['posts_per_page']);
        }

        /**
         * Adjust query according to source
         */
        if ($args['content_source'] === 'internal') {

            /**
             * For multisite sites
             */
            if ($args['post_type'] === 'multisite_sites') {
                $site__not_in = [get_main_site_id()];

                if ($args['post_status'] === 'active' || $args['post_status'] === 'expired') {
                    $all_pages = get_sites([
                        'number' => 1000,
                        'site__not_in' => $site__not_in,
                    ]);

                    foreach ($all_pages as $post) {
                        $field_details = Growtype_Site::get_settings_field_details($post->blog_id, 'event_start_date');
                        if ($args['post_status'] === 'active' && $field_details->option_value < date('Y-m-d')
                            || $args['post_status'] === 'expired' && $field_details->option_value > date('Y-m-d')
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

                    $total_pages = round(count($total_existing_records) / $args['posts_per_page']);
                }

                $wp_query = new WP_Site_Query([
                    'number' => $args['posts_per_page'] === -1 ? 100 : $args['posts_per_page'],
                    'site__not_in' => $site__not_in,
                    'offset' => growtype_post_get_pagination_offset($args['posts_per_page'])
                ]);
            } else {
                /**
                 * Include custom meta query
                 */
                if (!empty($args['meta_query'])) {
                    $query_args['meta_query'] = json_decode(urldecode($args['meta_query']), true);
                }

                /**
                 * Include custom tax query
                 */
                if (!empty($args['tax_query'])) {
                    $query_args['tax_query'] = json_decode(urldecode($args['tax_query']), true);
                }

                $query_args = apply_filters('growtype_post_shortcode_extend_args', $query_args, $args);

                $wp_query = new WP_Query($query_args);

                /**
                 * Total existing records for pagination
                 */
                if ($args['pagination']) {
                    $args['total_pages'] = round(wp_count_posts($query_args['post_type'])->publish / $args['posts_per_page']);
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

            if (has_filter('growtype_post_content_sourced')) {
                $posts = apply_filters('growtype_post_content_sourced', $args);
            } else {
                $posts = get_transient($transient_name);

                if (!$args['content_url_cache']) {
                    if (!empty($posts)) {
                        delete_transient($transient_name);
                    }

                    $posts = [];
                }
            }

            if (empty($posts) && !$posts) {
                $content_url = apply_filters('growtype_post_shortcode_extend_content_url', $content_url, $args);

                //https://developer.wordpress.org/rest-api/reference/posts/#arguments
                $response = wp_remote_get($content_url, [
                    'timeout' => 10,
                    'sslverify' => false
                ]);

                $posts = is_array($response) && !is_wp_error($response) ? json_decode($response['body'], true) : [];

                if ($args['content_url_cache']) {
                    set_transient($transient_name, $posts, MONTH_IN_SECONDS);
                }
            }

            $formatted_posts = [];
            if (!empty($posts)) {
                foreach ($posts as $post) {
                    $formatted_post = new WP_Post((object)[
                        'ID' => $post['id'],
                        'post_author' => isset($post['author']) ? $post['author'] : '',
                        'post_date' => isset($post['date']) ? $post['date'] : '',
                        'post_date_gmt' => isset($post['date_gmt']) ? $post['date_gmt'] : '',
                        'post_content' => isset($post['content']['rendered']) ? $post['content']['rendered'] : '',
                        'post_title' => isset($post['title']['rendered']) ? $post['title']['rendered'] : '',
                        'post_excerpt' => isset($post['excerpt']['rendered']) ? $post['excerpt']['rendered'] : '',
                        'post_status' => isset($post['status']) ? $post['status'] : '',
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
                        'post_type' => isset($post['type']) ? $post['type'] : '',
                        'post_mime_type' => isset($post['post_mime_type']) ? $post['post_mime_type'] : '',
                        'comment_count' => isset($post['comment_count']) ? $post['comment_count'] : '0',
                        'filter' => isset($post['filter']) ? $post['filter'] : 'raw',
                    ]);

                    foreach ($post as $key => $post_value) {
                        if (!in_array($key, ['id', 'author', 'date', 'date_gmt', 'content', 'title', 'excerpt', 'status', 'slug', 'modified', 'modified_gmt', 'parent', 'guid', 'menu_order', 'type'])) {
                            $formatted_post->$key = $post_value;
                        }
                    }

                    array_push($formatted_posts, $formatted_post);
                }
            }

            $wp_query = new WP_Query;
            $wp_query->query = null;
            $wp_query->posts = $formatted_posts;
            $wp_query->request = $formatted_posts;
            $wp_query->post_count = count($formatted_posts);
        }

        /**
         * Show posts
         */
        $render = '';

        if ($wp_query->have_posts()) {
            $render = self::render_all(
                $wp_query,
                $args
            );
        }

        return $render;
    }

    /**
     * @param $wp_query
     * @param $args
     * @return false|string
     */
    public static function render_all(
        $wp_query = null,
        $args = null
    ) {
        /**
         * Prepare default wp_query if empty wp_query is passed
         */
        if (empty($wp_query)) {
            $args['pagination'] = isset($args['pagination']) ? $args['pagination'] : true;
            $args['posts_per_page'] = isset($args['posts_per_page']) ? $args['posts_per_page'] : 12;
            $args['post_type'] = isset($args['post_type']) ? $args['post_type'] : get_post_type();
            $args['offset'] = isset($args['offset']) ? $args['offset'] : growtype_post_get_pagination_offset($args['posts_per_page']);

            $queried_object = get_queried_object();

            if (!empty($queried_object)) {
                $queried_object_slug = $queried_object->slug;
                if (!empty($queried_object_slug)) {
                    $args['category_name'] = $queried_object_slug;
                }
            }

            $wp_query = new WP_Query($args);

            $args['columns'] = isset($args['columns']) ? $args['columns'] : 3;

            $pages_amount = 0;

            if (isset($args['post_type']) && $args['post_type']) {
                $pages_amount = round(wp_count_posts($args['post_type'])->publish / $args['posts_per_page']);
            }

            $args['total_pages'] = isset($args['total_pages']) ? $args['total_pages'] : $pages_amount;
        }

        $post_classes_list = ['growtype-post-single'];

        $post_type = isset($args['post_type']) && !empty($args['post_type']) ? $args['post_type'] : null;

        $preview_style = isset($args['preview_style']) ? $args['preview_style'] : 'basic';

        $args['preview_file_path'] = 'preview.' . $preview_style . '.content';

        array_push($post_classes_list, 'growtype-post-' . $preview_style);

        if (!empty($post_type)) {
            array_push($post_classes_list, 'growtype-post-' . $post_type);
        }

        $args['post_classes'] = implode(' ', $post_classes_list);

        $template_path = 'preview.' . $preview_style . '.index';

        $terms_navigation_taxonomies = isset($args['terms_navigation_taxonomy']) ? $args['terms_navigation_taxonomy'] : ['category'];

        foreach ($terms_navigation_taxonomies as $terms_navigation_taxonomy) {
            $terms = get_terms(array (
                'taxonomy' => $terms_navigation_taxonomy,
                'hide_empty' => true,
                'exclude' => 1
            ));

            $terms = [
                $terms_navigation_taxonomy => $terms
            ];
        }

        $existing_terms_groups = apply_filters('growtype_post_shortcode_extend_terms', $terms, $wp_query, $args);

        ob_start();

        if ($wp_query->have_posts()) : ?>

            <?php if (isset($args['terms_navigation']) && $args['terms_navigation'] && !empty($existing_terms_groups)) { ?>
                <div class="growtype-post-terms-filters">
                    <?php foreach ($existing_terms_groups as $key => $existing_terms) { ?>
                        <div class="growtype-post-terms-filter">
                            <?php
                            $counter = 0;
                            foreach ($existing_terms as $existing_term) { ?>
                                <div class="growtype-post-terms-filter-btn btn btn-secondary <?php echo $counter === 0 ? 'is-active' : '' ?>" data-cat-<?php echo $key ?>="<?php echo $existing_term->slug ?>"><?php echo $existing_term->name ?></div>
                                <?php $counter++; ?>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <div <?php echo !empty($args['parent_id']) ? 'id="' . $args['parent_id'] . '"' : "" ?>
                class="growtype-post-container <?php echo isset($args['parent_class']) ? $args['parent_class'] : '' ?>"
                data-columns="<?php echo isset($args['columns']) ? $args['columns'] : '1' ?>"
                data-visible-posts="<?php echo isset($args['posts_per_page']) ? $args['posts_per_page'] : '' ?>"
                data-loading-type="<?php echo isset($args['loading_type']) ? $args['loading_type'] : 'initial' ?>"
            >
                <?php
                $counter = 0;
                while ($wp_query->have_posts()) : $wp_query->the_post();
                    $existing_args = $args;

                    if (!empty($counter) && isset($existing_args['posts_per_page']) && $existing_args['posts_per_page'] > 0 && $counter >= $existing_args['posts_per_page']) {
                        $existing_classes = explode(' ', $existing_args['post_classes']);
                        array_push($existing_classes, 'is-hidden');
                        $existing_args['post_classes'] = implode(' ', $existing_classes);
                    }

                    $current_post = get_post();

                    $current_post = apply_filters('growtype_post_shortcode_extend_post', $current_post, $args);

                    if (isset($current_post->post_is_a_link)) {
                        $existing_args['post_is_a_link'] = $current_post->post_is_a_link;
                    }

                    if (isset($current_post->post_permalink)) {
                        $existing_args['post_permalink'] = $current_post->post_permalink;
                    } else {
                        $existing_args['post_permalink'] = get_permalink();
                    }

                    if (isset($current_post->post_terms)) {
                        $existing_args['post_terms'] = $current_post->post_terms;
                    } else {
                        foreach ($terms_navigation_taxonomies as $terms_navigation_taxonomy) {
                            $existing_args['post_terms'][$terms_navigation_taxonomy] = wp_get_post_terms(get_the_ID(), $terms_navigation_taxonomy);
                            $existing_args['post_terms'][$terms_navigation_taxonomy] = is_wp_error($existing_args['post_terms']) ? [] : $existing_args['post_terms'];
                        }
                    }

                    $existing_args['post_terms_html'] = self::format_post_terms_html($existing_args['post_terms']);

                    echo self::render_single(
                        $template_path,
                        $current_post,
                        $existing_args
                    );

                    $counter++;
                endwhile;
                ?>
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

        return ob_get_clean();
    }

    public static function format_post_terms_html($terms)
    {
        $html = '';
        foreach ($terms as $key => $term) {
            $html .= 'data-cat-' . $key . '="' . implode(',', array_pluck($term, 'slug')) . '" ';
        }

        return $html;
    }

    /**
     * @param $template_path
     * @param $post
     * @param $args
     * @return false|string|null
     */
    public static function render_single(
        $template_path,
        $post,
        $args
    ) {
        $variables = array_merge(['post' => $post], $args);
        return growtype_post_include_view($template_path, $variables);
    }

    /**
     * @param null $custom_query
     */
    public static function pagination($wp_query = null, $total_pages = null)
    {
        if (empty($custom_query)) {
            global $wp_query;
            $custom_query = $wp_query;
        }

        $total_pages = !empty($total_pages) ? $total_pages : (is_object($custom_query) ? $custom_query->max_num_pages : count($custom_query));
        $big = 999999999;

        if ($total_pages > 1) {
            $current_page = max(1, get_query_var('paged'));
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
