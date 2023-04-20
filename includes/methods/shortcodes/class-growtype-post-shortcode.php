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
        extract(shortcode_atts(array (
            'post_type' => 'post',
            'posts_per_page' => -1,
            'preview_style' => 'basic', //blog
            'preview_style_custom' => '', //blog
            'category_name' => '', //use category slug
            'parent_class' => '',
            'post_status' => 'publish', //also active, expired
            'columns' => '3',
            'post__in' => [],
            'category__not_in' => [],
            'category__in' => [],
            'order' => 'asc',
            'orderby' => 'menu_order',
            'parent_id' => '',
            'intro_content_length' => '100',
        ), $attr));

        $post_link = isset($attr['post_link']) && $attr['post_link'] ? true : false;
        $post_in_modal = isset($attr['post_in_modal']) && $attr['post_in_modal'] ? true : false;
        $pagination = isset($attr['pagination']) && $attr['pagination'] ? true : false;
        $show_all_posts = isset($attr['show_all_posts']) && $attr['show_all_posts'] ? true : false;
        $meta_query = isset($attr['meta_query']) && !empty($attr['meta_query']) ? $attr['meta_query'] : null;
        $tax_query = isset($attr['tax_query']) && !empty($attr['tax_query']) ? $attr['tax_query'] : null;
        $sticky_post = isset($attr['sticky_post']) && !empty($attr['sticky_post']) ? $attr['sticky_post'] : 'none';

        /**
         * Show all posts
         */
        if ($show_all_posts) {
            $posts_per_page = -1;
        }

        /**
         * Preview style
         */
        if ($preview_style === 'custom' && !empty($preview_style_custom)) {
            $preview_style = $preview_style_custom;
        }

        if ($preview_style === 'product' && !class_exists('woocommerce')) {
            return 'Please enable WooCommerce plugin to use this preview style!';
        }

        $args = array (
            'post_type' => $post_type,
            'posts_per_page' => $posts_per_page,
            'orderby' => $orderby,
            'order' => $order
        );

        if (!empty($category__not_in)) {
            $args['category__not_in'] = explode(',', $category__not_in);
        }

        if (!empty($category__in)) {
            $args['category__in'] = explode(',', $category__in);
        }

        if (!empty($post__in)) {
            $args['post__in'] = explode(',', $post__in);
        }

        /**
         * Display sticky posts
         */
        if ($sticky_post !== 'none') {
            if ($sticky_post === 'visible') {
                $parent_class = explode(',', $parent_class);
                $parent_class = array_filter($parent_class);
                array_push($parent_class, 'has-sticky-post');
                $parent_class = implode(' ', $parent_class);
                $args['post__in'] = !empty(get_option('sticky_posts')) ? get_option('sticky_posts') : ['0'];
            } elseif ($sticky_post === 'hidden' && !empty(get_option('sticky_posts'))) {
                $args['post__not_in'] = get_option('sticky_posts');
            }
        }

        /**
         * Set default parent id
         */
//        if (empty($parent_id)) {
//            $parent_id = 'growtype-post-' . $post_type;
//        }

        if (!empty($category_name)) {
            if (in_array($post_type, ['product'])) {
                $args['tax_query'] = array (
                    array (
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' => [$category_name],
                        'operator' => 'IN',
                    )
                );
            } else {
                $args['category_name'] = $category_name;
            }
        }

        if ($pagination && !empty(get_query_var('paged'))) {
            $args['offset'] = growtype_post_get_pagination_offset($posts_per_page);
        }

        /**
         * For multisite sites
         */
        if ($post_type === 'multisite_sites') {
            $site__not_in = [get_main_site_id()];

            if ($post_status === 'active' || $post_status === 'expired') {
                $all_pages = get_sites([
                    'number' => 1000,
                    'site__not_in' => $site__not_in,
                ]);

                foreach ($all_pages as $post) {
                    $field_details = Growtype_Site::get_settings_field_details($post->blog_id, 'event_start_date');
                    if ($post_status === 'active' && $field_details->option_value < date('Y-m-d')
                        || $post_status === 'expired' && $field_details->option_value > date('Y-m-d')
                    ) {
                        array_push($site__not_in, $post->blog_id);
                    }
                }
            }

            /**
             * Total existing records for pagination
             */
            if ($pagination) {
                $total_existing_records = get_sites([
                    'number' => 1000,
                    'site__not_in' => $site__not_in,
                ]);

                $total_pages = round(count($total_existing_records) / $posts_per_page);
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
            if (!empty($meta_query)) {
                $args['meta_query'] = str_replace("'", '"', json_decode(urldecode($meta_query), true));
            }

            /**
             * Include custom tax query
             */
            if (!empty($tax_query)) {
                $args['tax_query'] = str_replace("'", '"', json_decode(urldecode($tax_query), true));
            }

            $args = apply_filters('growtype_post_shortcode_extend_args', $args, $attr);

            $wp_query = new WP_Query($args);

            /**
             * Total existing records for pagination
             */
            if ($pagination) {
                $total_pages = round(wp_count_posts($args['post_type'])->publish / $posts_per_page);
            }
        }

        /**
         * Show posts
         */
        $render = '';

        if ($wp_query->have_posts()) {
            $render = self::render_all(
                $wp_query,
                [
                    'preview_style' => $preview_style,
                    'columns' => $columns,
                    'post_link' => $post_link,
                    'parent_class' => $parent_class,
                    'parent_id' => $parent_id,
                    'pagination' => $pagination,
                    'intro_content_length' => $intro_content_length,
                    'total_pages' => $total_pages ?? null,
                    'post_in_modal' => $post_in_modal,
                    'post_type' => isset($args['post_type']) ? $args['post_type'] : null,
                ]
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
                class="growtype-post-container <?php echo $args['parent_class'] ?? '' ?>"
                data-columns="<?php echo $args['columns'] ?? '1' ?>"
            >
                <?php
                while ($wp_query->have_posts()) : $wp_query->the_post();
                    echo self::render_single(
                        $template_path,
                        get_post(),
                        $args
                    );
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
