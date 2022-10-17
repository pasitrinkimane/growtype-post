<?php

/**
 *
 */
class Growtype_Post_Shortcode
{
    function __construct()
    {
        if (!is_admin()) {
            add_shortcode('growtype_post', array ($this, 'growtype_post_shortcode'));
        }
    }

    /**
     * @param $atts
     * @return string
     * Posts shortcode
     */
    function growtype_post_shortcode($atts)
    {
        extract(shortcode_atts(array (
            'post_type' => 'post',
            'posts_per_page' => -1,
            'slider' => 'false',
            'preview_style' => 'basic', //blog
            'preview_style_custom' => '', //blog
            'slider_slides_amount_to_show' => '4',
            'post_link' => 'true',
            'category_name' => '', //use category slug
            'parent_class' => '',
            'pagination' => false,
            'post_status' => 'publish', //also active, expired
            'columns' => '3',
            'post__in' => [],
            'category__not_in' => [],
            'category__in' => [],
            'order' => 'asc',
            'orderby' => 'menu_order',
            'parent_id' => '',
            'intro_content_length' => '100'
        ), $atts));

        /**
         * Preview style
         */
        if ($preview_style === 'custom' && !empty($preview_style_custom)) {
            $preview_style = $preview_style_custom;
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

        if (empty($parent_id)) {
            $parent_id = 'growtype-post-' . $post_type;
        }

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

        if (!empty(get_query_var('paged'))) {
            $current_page = max(1, get_query_var('paged'));
            $offset = $current_page === 1 ? 0 : ($current_page - 1) * $posts_per_page;

            $args['offset'] = $offset;
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

            $posts = get_sites([
                'number' => $posts_per_page === -1 ? 100 : $posts_per_page,
                'site__not_in' => $site__not_in,
                'offset' => $offset
            ]);
        } else {

            if ($post_status === 'active' || $post_status === 'expired') {
                $args['meta_query'] = array (
                    'relation' => 'OR',
                    array (
                        'key' => 'event_start_date',
                        'value' => current_time('Ymd'),
                        'compare' => ($post_status === 'expired' ? '<' : '>')
                    ),
                );
            }

            $args = apply_filters('growtype_post_shortcode_extend_args', $atts, $args);

            $the_query = new WP_Query($args);

            $posts_amount = $the_query->post_count;

            $posts = $the_query->get_posts();

            /**
             * Total existing records for pagination
             */
            if ($pagination) {
                $total_existing_records = new WP_Query([
                    'posts_per_page' => -1,
                    'post_type' => $args['post_type'],
                    'meta_query' => $args['meta_query'] ?? [],
                ]);

                $total_pages = round($total_existing_records->post_count / $posts_per_page);
            }
        }

        $slider_id = md5(rand());

        /**
         * Show posts
         */
        $render = '';

        if (!empty($posts)) {
            $render = self::render_all(
                $posts,
                [
                    'preview_style' => $preview_style,
                    'columns' => $columns,
                    'post_link' => $post_link,
                    'parent_class' => $parent_class,
                    'slider' => $slider,
                    'parent_id' => $parent_id,
                    'pagination' => $pagination,
                    'intro_content_length' => $intro_content_length,
                    'slider_id' => $slider_id
                ]
            );
        }

        /**
         * Add js scripts
         * slick slider
         */
        if ($slider === 'true') {
            if ($posts_amount > $slider_slides_amount_to_show) {
                add_action('wp_footer', function ($arguments) use ($slider_id, $slider_slides_amount_to_show) { ?>
                    <script type="text/javascript">
                        jQuery(document).ready(function () {
                            let slidesToShow = <?php echo $slider_slides_amount_to_show ?>;
                            let sliderId = '<?php echo $slider_id ?>';

                            jQuery('.growtype-post-container[data-slider-id="' + sliderId + '"]').slick({
                                infinite: false,
                                slidesToShow: slidesToShow,
                                slidesToScroll: 1,
                                autoplay: false,
                                autoplaySpeed: 2000,
                                arrows: true,
                                dots: false,
                                responsive: [
                                    {
                                        breakpoint: 1000,
                                        settings: {
                                            slidesToShow: 4,
                                            slidesToScroll: 1
                                        }
                                    },
                                    {
                                        breakpoint: 900,
                                        settings: {
                                            slidesToShow: 3,
                                            slidesToScroll: 1
                                        }
                                    },
                                    {
                                        breakpoint: 700,
                                        settings: {
                                            slidesToShow: 2,
                                            slidesToScroll: 1
                                        }
                                    },
                                    {
                                        breakpoint: 570,
                                        settings: {
                                            slidesToShow: 1,
                                            slidesToScroll: 1
                                        }
                                    }
                                ]
                            });
                        });
                    </script>
                    <?php
                }, 100);
            }
        }

        return $render;
    }

    /**
     * @param $preview_style
     * @param $columns
     * @param $post_link
     * @param $parent_class
     * @param $slider
     * @return void
     * Render multiple posts
     * //$parameters -> $preview_style, $columns, $post_link = true, $parent_class = '', $slider = false, $parent_id = '', $pagination = null
     */
    public static function render_all(
        $posts,
        $parameters
    ) {
        $post_classes_list = ['b-post-single'];

        $post_type = isset($posts[0]) ? $posts[0]->post_type : null;

        array_push($post_classes_list, 'b-post-' . $parameters['preview_style']);

        if (!empty($post_type)) {
            array_push($post_classes_list, 'b-post-' . $post_type);
        }

        $post_classes = implode(' ', $post_classes_list);

        $template_path = 'preview.' . $parameters['preview_style'] . '.index';

        ob_start();

        if (!empty($posts)) : ?>
            <div <?php echo !empty($parameters['parent_id']) ? 'id="' . $parameters['parent_id'] . '"' : "" ?>
                class="growtype-post-container <?php echo $parameters['parent_class'] ?> <?php echo $parameters['slider'] === 'true' ? 'is-slider' : '' ?>"
                data-columns="<?php echo $parameters['columns'] ?>"
                data-slider-id="<?php echo $parameters['slider_id'] ?>"
            >
                <?php
                foreach ($posts as $post) {
                    echo self::render_single(
                        $template_path,
                        $post,
                        [
                            'post_link' => $parameters['post_link'],
                            'post_classes' => $post_classes,
                            'intro_content_length' => $parameters['intro_content_length']
                        ]
                    );
                }
                ?>
            </div>
        <?php endif;

        /**
         * Pagination
         */
        if ($parameters['pagination']) { ?>
            <div class="pagination">
                <?php echo self::pagination($posts, $total_pages ?? null); ?>
            </div>
            <?php
        }

        return ob_get_clean();
    }

    /**
     * @param $template_path
     * @param $post
     * @param $parameters
     * @return false|string|null
     */
    public static function render_single(
        $template_path,
        $post,
        $parameters
    ) {

        $variables = array_merge(['post' => $post], $parameters);

        return growtype_post_include_view($template_path, $variables);
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
            $current_page = max(1, get_query_var('paged'));

            echo paginate_links(array (
                'prev_text' => '<span class="dashicons dashicons-arrow-left-alt"></span>',
                'next_text' => '<span class="dashicons dashicons-arrow-right-alt"></span>',
                'type' => 'list',
                'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                'format' => '?page=%#%',
                'current' => $current_page,
                'total' => $total_pages,
            ));
        }
    }
}
