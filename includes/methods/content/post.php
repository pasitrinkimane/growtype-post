<?php

add_action('growtype_single_post_title', 'growtype_post_growtype_single_post_title');
function growtype_post_growtype_single_post_title()
{
    if (get_theme_mod('growtype_post_single_page_title_enabled', true)) {
        echo '<div class="container section-title"><h1>' . get_the_title() . '</h1></div>';
    }
}

add_action('growtype_single_post_date', 'growtype_post_growtype_single_post_date');
function growtype_post_growtype_single_post_date()
{
    if (get_theme_mod('growtype_post_single_page_date_enabled', true)) {
        echo '<div class="container section-date"><span class="e-date">' . get_the_date() . '</span></div>';
    }
}

add_action('growtype_single_post_taxonomy', 'growtype_post_growtype_single_post_info_wrapper_open');
function growtype_post_growtype_single_post_info_wrapper_open()
{
    echo '<div class="section-info">';
}

add_action('growtype_single_post_date', 'growtype_post_growtype_single_post_info_wrapper_close');
function growtype_post_growtype_single_post_info_wrapper_close()
{
    echo '</div>';
}

/**
 * Render the post content
 */
add_action('growtype_single_post_featured_image', 'growtype_post_growtype_single_post_featured_image');
function growtype_post_growtype_single_post_featured_image($params = [])
{
    if (!get_theme_mod('growtype_post_single_page_featured_image_enabled', true)) {
        return;
    }

    $post_id = get_the_ID();
    $caption = get_the_post_thumbnail_caption($post_id);
    $image_url = get_the_post_thumbnail_url($post_id, 'full');

    if (empty($image_url) && isset($params['default_image'])) {
        $image_url = $params['default_image'];
    }

    if (empty($image_url)) {
        return;
    }

    $background_size = $params['background_size'] ?? 'cover';
    $background_position = $params['background_position'] ?? 'center';
    $background_repeat = $params['background_repeat'] ?? 'no-repeat';

    $style = sprintf(
        'background-image: url(%s); background-size: %s; background-position: %s; background-repeat: %s;',
        esc_url($image_url),
        esc_attr($background_size),
        esc_attr($background_position),
        esc_attr($background_repeat)
    );

    $caption_html = !empty($caption) ? sprintf('<span class="section-featuredimg-caption e-caption">%s</span>', esc_html($caption)) : '';

    ?>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "ImageObject",
            "contentUrl": "<?php echo $image_url ?>",
            "name": "<?php echo get_the_title() ?>",
            "description": "<?php echo get_the_excerpt() ?>"
        }

    </script>
    <?php

    printf(
        '<div class="container section-featuredimg" style="%s">%s</div>',
        $style,
        $caption_html
    );
}

/**
 * Render the post content
 */
add_action('growtype_single_post_cta', 'growtype_post_growtype_single_post_cta');
function growtype_post_growtype_single_post_cta($params = [])
{
    if (get_theme_mod('growtype_post_single_page_social_cta_enabled', true)) {
        echo growtype_post_render_social_cta($params['post_id'] ?? null, $params['cta_buttons'] ?? null);
    }
}

/**
 * @param $post_id
 * @param $show_buttons
 * @return string
 */
function growtype_post_render_social_cta($post_id = null, $cta_buttons = null)
{
    /**
     * Check if the social actions are enabled
     */
    if (!get_theme_mod('growtype_post_preview_social_actions_enabled', true)) {
        return '';
    }

    $post_id = $post_id ?? get_the_ID();

    $default_buttons = [
        'like' => ['label' => __('Love', 'growtype-post')],
        'share' => ['label' => __('Share', 'growtype-post')]
    ];

    $cta_buttons = $cta_buttons ?? $default_buttons;
    $cta_buttons = apply_filters('growtype_post_render_show_buttons', $cta_buttons, $post_id);

    $cta_html = array_reduce(array_keys($cta_buttons), function ($html, $cta_key) use ($cta_buttons, $post_id) {
        switch ($cta_key) {
            case 'like':
                $html .= growtype_post_render_like_button($post_id, $cta_buttons[$cta_key]);
                break;
            case 'share':
                $html .= growtype_post_render_share_button($post_id, $cta_buttons[$cta_key]);
                break;
            default:
                $html .= sprintf('<a href="%s" class="growtype-post-cta">%s%s</a>', esc_url($cta_buttons[$cta_key]['url']), $cta_buttons[$cta_key]['icon'] ?? '', esc_html($cta_buttons[$cta_key]['label']));
                break;
        }
        return $html;
    }, '');

    return $cta_html ? "<div class=\"growtype-post-social-cta-wrapper\">{$cta_html}</div>" : '';
}

function growtype_post_render_like_button($post_id, $button_config)
{
    $is_liked_by_user = growtype_post_is_liked_by_user($post_id);
    $likes_amount = growtype_post_likes_amount($post_id);

    $button_classes = ['growtype-post-cta', 'growtype-post-btn-like'];
    if ($is_liked_by_user) {
        $button_classes[] = 'is-active';
    }

    $button_text = $likes_amount ? "<span class=\"e-amount\">" . esc_html($likes_amount) . "</span>" : '';
    $button_text .= "<span class=\"e-text\">{$button_config['label']}</span>";

    return sprintf(
        '<div class="%s" data-type="post" data-id="%s">%s</div>',
        esc_attr(implode(' ', $button_classes)),
        esc_attr($post_id),
        $button_text
    );
}

function growtype_post_render_share_button($post_id, $button_config)
{
    return sprintf(
        '<div class="growtype-post-cta growtype-post-btn-share" data-type="post" data-id="%s">%s</div>',
        esc_attr($post_id),
        esc_html($button_config['label'])
    );
}

add_action('growtype_single_post_related_posts', 'growtype_post_growtype_single_post_related_posts');
function growtype_post_growtype_single_post_related_posts()
{
    $enabled = get_theme_mod('growtype_post_related_posts_enabled', true);

    if ($enabled) {
        $first_tag = !empty(wp_get_post_tags(get_the_id())) ? wp_get_post_tags(get_the_id())[0]->term_id : '';

        $posts_per_page = apply_filters('growtype_post_related_posts_per_page', 3);

        $args = array (
            'post_type' => get_post_type(),
            'post__not_in' => array (get_the_id()),
            'posts_per_page' => $posts_per_page,
            'orderby' => 'menu_order',
            'order' => 'ASC',
        );

        if (!empty($first_tag)) {
            $args['tag__in'] = array ($first_tag);
        }

        $posts = new WP_Query($args);

        $columns = apply_filters('growtype_post_related_posts_columns', 3);

        $post_params = apply_filters('rowtype_post_related_posts_params', [
            'columns' => $columns,
            'post_is_a_link' => true,
        ]);

        $posts_args = [
            'posts' => $posts,
            'section_title' => apply_filters('growtype_post_related_posts_section_title', __('Related Posts', 'growtype-post')),
            'params' => $post_params
        ];

        $posts_args = apply_filters('growtype_post_related_posts_args', $posts_args);

        if (isset($posts_args['posts']) && !empty($posts_args['posts']->posts)) {
            echo growtype_post_include_view('section.related-posts', $posts_args);
        }

        wp_reset_query();
    }
}

add_action('growtype_single_post_reading_time', 'growtype_post_growtype_single_post_reading_time');
function growtype_post_growtype_single_post_reading_time()
{
    if (get_theme_mod('growtype_post_reading_time_enabled', true)) {
        echo '<div class="container section-readingtime">' . growtype_post_reading_time(get_the_ID()) . '</div>';
    }
}

add_action('growtype_single_post_breadcrumbs', 'growtype_post_growtype_single_post_breadcrumbs');
function growtype_post_growtype_single_post_breadcrumbs($params = [])
{
    if (get_theme_mod('growtype_post_breadcrumbs_enabled', true)) {

        if (empty($params)) {
            $params = [
                'btn_back' => [
                    'enabled' => true,
                    'url' => get_home_url(),
                    'label' => ''
                ],
                'breadcrumbs' => [
                    'enabled' => true
                ]
            ];
        }

        $params = apply_filters('growtype_post_growtype_single_post_breadcrumbs', $params);

        ?>
        <div class="container section-breadcrumbs">
            <?php
            if ($params['btn_back']['enabled']) {
                echo '<a href="' . esc_url($params['btn_back']['url']) . '" class="btn-back">' . ($params['btn_back']['label'] ?? '') . '</a>';
            }

            if ($params['breadcrumbs']['enabled']) {
                echo growtype_post_render_breadcrumbs();
            }
            ?>
        </div>
        <?php
    }
}

function growtype_post_render_breadcrumbs($breadcrumbs = [])
{
    $ancestors = get_post_ancestors(get_the_ID());
    $sep = ' <span class="dashicons dashicons-arrow-right-alt2"></span> ';

    echo '<div class="gp-breadcrumbs">';

    if (empty($breadcrumbs)) {
        $breadcrumbs[] = [
            'url' => home_url(),
            'label' => __('Home', 'growtype-post')
        ];

        if (!empty($ancestors)) {
            foreach (array_reverse($ancestors) as $ancestor) {
                $breadcrumbs[] = [
                    'url' => get_permalink($ancestor),
                    'label' => get_the_title($ancestor)
                ];
            }
        }

        $post_type = get_post_type();
        $taxonomies = get_object_taxonomies($post_type, 'objects');

        $taxonomies_to_show = apply_filters('growtype_post_breadcrumbs_taxonomies_to_show', '');

        if (!empty($taxonomies)) {
            foreach ($taxonomies as $key => $taxonomy) {

                if (!empty($taxonomies_to_show) && !in_array($taxonomy->name, $taxonomies_to_show)) {
                    continue;
                }

                $terms = get_the_terms(get_the_ID(), $taxonomy->name);
                if ($terms && !is_wp_error($terms)) {
                    $term = $terms[0];
                    $breadcrumbs[] = [
                        'url' => get_term_link($term->term_id),
                        'label' => $term->name
                    ];
                    break;
                }
            }
        }

        $breadcrumbs[] = [
            'url' => '',
            'label' => get_the_title()
        ];
    }

    $breadcrumbs = apply_filters('growtype_post_render_breadcrumbs', $breadcrumbs);

    foreach ($breadcrumbs as $key => $breadcrumb) {
        echo growtype_post_render_breadcrumbs_link($breadcrumb['url'], $breadcrumb['label']);

        if ($key !== array_key_last($breadcrumbs)) {
            echo $sep;
        }
    }

    echo '</div>';
}

function growtype_post_render_breadcrumbs_link($url, $label)
{
    if (empty($url)) {
        return '<span class="gp-breadcrumbs-single">' . $label . '</span>';
    }

    return '<span class="gp-breadcrumbs-single"><a href="' . $url . '">' . $label . '</a></span>';
}

add_action('growtype_single_post_taxonomy', 'growtype_post_growtype_single_post_taxonomy');
function growtype_post_growtype_single_post_taxonomy()
{
    if (get_theme_mod('growtype_post_taxonomy_enabled', true)) {
        $taxonomy = get_post_taxonomies(get_the_ID());

        $category = '';

        if (!empty($taxonomy)) {
            $taxonomy = $taxonomy[0];

            $terms = get_the_terms(get_the_ID(), $taxonomy);
            $category = join('/', wp_list_pluck($terms, 'name'));
        }

        echo '<div class="container section-taxonomy"><span class="e-tax">' . $category . '</span></div>';
    }
}

add_action('wp_loaded', function () {
    /**
     * Like post
     */
    if (isset($_GET['growtype-post-action']) && $_GET['growtype-post-action'] === 'like') {
        $post_id = isset($_GET['post_id']) && !empty($_GET['post_id']) ? $_GET['post_id'] : null;

        if (!empty($post_id)) {
            growtype_post_like_post($post_id, false);

            add_action('wp_footer', function () {
                echo '<script>window.history.pushState({}, document.title, "' . get_permalink($post_id) . '" );</script>';
            });
        }
    }
});
