<?php

add_action('growtype_single_post_related_posts', 'growtype_post_growtype_single_post_related_posts');

function growtype_post_growtype_single_post_related_posts()
{
    if (!get_theme_mod('post_single_page_related_posts_disabled')) {
        echo growtype_post_include_view('section.related-posts');
    }
}
