<?php

add_action('growtype_archive', 'growtype_post_growtype_archive');

function growtype_post_growtype_archive()
{
    echo growtype_post_render_all();
}
