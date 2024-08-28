<?php

add_action('growtype_archive', 'growtype_post_archive');

function growtype_post_archive()
{
    $parameters = apply_filters('growtype_post_archive_parameters', [
        'columns' => 4,
        'pagination' => true
    ]);

    $query = apply_filters('growtype_post_archive_query', null);

    echo growtype_post_render_all($query, $parameters);
}
