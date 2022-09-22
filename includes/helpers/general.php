<?php

/**
 * @return bool
 */
function growtype_post_is_front_post()
{
    return get_option('page_on_front') == growtype_post_get_post_id(get_post());
}

/**
 * @param $post
 * @return int|null
 */
function growtype_post_get_post_id($post)
{
    $post_id = $post->ID ?? null;

    if (empty($post_id)) {
        $post_name = $post->post_name ?? null;
        $post = get_page_by_path($post_name);
        $post_id = $post->ID ?? null;
    }

    return $post_id;
}
