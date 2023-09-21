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
            'posts_per_page' => isset($attr['posts_per_page']) ? $attr['posts_per_page'] : -1,
            'visible_posts' => isset($attr['posts_per_page']) ? $attr['posts_per_page'] : -1,
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
            'post_link' => isset($attr['post_link']) && $attr['post_link'] ? true : false,
            'post_in_modal' => isset($attr['post_in_modal']) && $attr['post_in_modal'] ? true : false,
            'pagination' => isset($attr['pagination']) && $attr['pagination'] ? true : false,
            'load_all_posts' => isset($attr['load_all_posts']) && $attr['load_all_posts'] ? true : false,
            'meta_query' => isset($attr['meta_query']) && !empty($attr['meta_query']) ? $attr['meta_query'] : null,
            'tax_query' => isset($attr['tax_query']) && !empty($attr['tax_query']) ? $attr['tax_query'] : null,
            'sticky_post' => isset($attr['sticky_post']) && !empty($attr['sticky_post']) ? $attr['sticky_post'] : 'none',
        ];

        /**
         * Show all posts
         */
        if ($args['load_all_posts']) {
            $args['posts_per_page'] = -1;
        }

        /**
         * Preview style
         */
        if ($args['preview_style'] === 'custom' && !empty($args['preview_style_custom'])) {
            $args['preview_style'] = $args['preview_style_custom'];
        } elseif ($args['preview_style'] === 'product' && !class_exists('woocommerce')) {
            return 'Please enable WooCommerce plugin to use this preview style!';
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
     * @param $preview_style
     * @param $columns
     * @param $post_link
     * @param $parent_class
     * @return void
     * Render multiple posts
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

        array_push($post_classes_list, 'growtype-post-' . $preview_style);

        if (!empty($post_type)) {
            array_push($post_classes_list, 'growtype-post-' . $post_type);
        }

        $args['post_classes'] = implode(' ', $post_classes_list);

        $template_path = 'preview.' . $preview_style . '.index';

        ob_start();

        if ($wp_query->have_posts()) : ?>
            <div <?php echo !empty($args['parent_id']) ? 'id="' . $args['parent_id'] . '"' : "" ?>
                class="growtype-post-container <?php echo isset($args['parent_class']) ? $args['parent_class'] : '' ?>"
                data-columns="<?php echo isset($args['columns']) ? $args['columns'] : '1' ?>"
                data-visible-posts="<?php echo isset($args['visible_posts']) ? $args['visible_posts'] : '' ?>"
                data-loading-type="<?php echo isset($args['loading_type']) ? $args['loading_type'] : 'initial' ?>"
            >
                <?php
                $counter = 0;
                while ($wp_query->have_posts()) : $wp_query->the_post();
                    if ($counter >= $args['visible_posts']) {
                        $existing_classes = explode(' ', $args['post_classes']);
                        array_push($existing_classes, 'is-hidden');
                        $args['post_classes'] = implode(' ', $existing_classes);
                    }
                    echo self::render_single(
                        $template_path,
                        get_post(),
                        $args
                    );
                    $counter++;
                endwhile;
                ?>
            </div>

            <?php if (isset($args['post_in_modal']) && $args['post_in_modal']) { ?>
                <?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
                    <div class="modal modal-growtype-post fade" id="<?php echo 'growtypePostModal-' . get_the_ID() . '"' ?>" tabindex="-1" aria-hidden="true">
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
