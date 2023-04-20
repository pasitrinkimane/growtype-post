<?php

add_action('growtype_single_post_title', 'growtype_post_growtype_single_post_title');
function growtype_post_growtype_single_post_title()
{
    if (get_theme_mod('growtype_post_single_page_title_enabled', true)) {
        echo '<div class="container section-title"><h1>' . get_the_title() . '</h1></div>';
    }
}

add_action('growtype_single_post_featured_image', 'growtype_post_growtype_single_post_featured_image');
function growtype_post_growtype_single_post_featured_image()
{
    if (get_theme_mod('growtype_post_single_page_featured_image_enabled', true)) {
        echo sprintf('<div class="container section-featuredimg" style="background: url(%s);background-size:cover;background-position:center;"></div>', get_the_post_thumbnail_url(get_the_ID(), 'full'));
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
    $likes = Growtype_Post_Ajax::growtype_post_likes_data(get_the_ID());

    return '<div class="cta-wrapper"><div class="btn-like ' . (!empty($likes) ? 'is-active' : '') . '" data-type="post" data-id="' . get_the_ID() . '">' . __('Love', 'growtype-post') . '</div><div class="btn-share" data-type="post" data-id="' . get_the_ID() . '">' . __('Share', 'growtype-post') . '</div></div>';
}

add_action('growtype_single_post_related_posts', 'growtype_post_growtype_single_post_related_posts');
function growtype_post_growtype_single_post_related_posts()
{
    if (get_theme_mod('growtype_post_related_posts_enabled', true)) {
        echo growtype_post_include_view('section.related-posts');
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
