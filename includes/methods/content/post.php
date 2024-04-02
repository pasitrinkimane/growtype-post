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
        echo '<div class="container section-date">' . get_the_date() . '</div>';
    }
}

add_action('growtype_single_post_featured_image', 'growtype_post_growtype_single_post_featured_image');
function growtype_post_growtype_single_post_featured_image()
{
    if (get_theme_mod('growtype_post_single_page_featured_image_enabled', true)) {
        $caption = get_the_post_thumbnail_caption(get_the_ID());
        $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
        if (!empty($thumbnail_url)) {
            echo sprintf('<div class="container section-featuredimg" style="background: url(%s);background-size:cover;background-position:center;">%s</div>', get_the_post_thumbnail_url(get_the_ID(), 'full'), '<span class="section-featuredimg-caption e-caption">' . $caption . '</span>');
        }
    }
}

add_action('growtype_single_post_cta', 'growtype_post_growtype_single_post_cta');
function growtype_post_growtype_single_post_cta()
{
    if (get_theme_mod('growtype_post_single_page_cta_enabled', true)) {
        echo growtype_post_render_cta();
    }
}

function growtype_post_render_cta()
{
    $likes = growtype_post_get_post_likes(get_the_ID());

    return '<div class="cta-wrapper"><div class="growtype-post-btn-like ' . (in_array(growtype_post_get_ip_key(), $likes) ? 'is-active' : '') . '" data-type="post" data-id="' . get_the_ID() . '">' . (!empty(count($likes)) ? '<span class="e-amount">' . count($likes) . '</span>' : '') . '<span class="e-text">' . __('Love', 'growtype-post') . '</span></div><div class="growtype-post-btn-share" data-type="post" data-id="' . get_the_ID() . '">' . __('Share', 'growtype-post') . '</div></div>';
}

add_action('growtype_single_post_related_posts', 'growtype_post_growtype_single_post_related_posts');
function growtype_post_growtype_single_post_related_posts()
{
    $enabled = get_theme_mod('growtype_post_related_posts_enabled', true);

    if ($enabled) {
        $first_tag = !empty(wp_get_post_tags(get_the_id())) ? wp_get_post_tags(get_the_id())[0]->term_id : '';

        $args = array (
            'post_type' => get_post_type(),
            'post__not_in' => array (get_the_id()),
            'posts_per_page' => 3,
            'orderby' => 'menu_order',
            'order' => 'DESC',
        );

        if (!empty($first_tag)) {
            $args['tag__in'] = array ($first_tag);
        }

        $posts = new WP_Query($args);

        $posts_args = [
            'posts' => $posts,
            'section_title' => __('Read more', 'growtype'),
            'params' => [
                'columns' => 3,
            ]
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

add_action('growtype_single_post_back', 'growtype_post_growtype_single_post_back');
function growtype_post_growtype_single_post_back()
{
    if (get_theme_mod('growtype_post_back_btn_enabled', true)) {
        echo '<div class="container section-back"><a href="' . get_home_url() . '" class="btn-back"></a></div>';
    }
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

        echo '<div class="container section-taxonomy">' . $category . '</div>';
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
