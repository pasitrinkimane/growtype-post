<?php
$post = isset($post) ? $post : get_post();
$terms = wp_get_post_terms($post->ID, get_post_type($post) . '_tax');

if (isset($post_link) && $post_link === false) { ?>
    <div class="<?php echo $post_classes ?>">
        <?php echo growtype_post_include_view(
            'preview.basic.content',
            [
                'post' => $post,
                'intro_content_length' => $intro_content_length ?? ''
            ]
        ); ?>
    </div>
<?php } else { ?>
    <a href="<?php echo get_permalink($post->ID) ?>" class="<?php echo isset($post_classes) ? $post_classes : '' ?>" data-cat="<?php echo isset($terms) && !empty($terms) ? implode(',', array_pluck($terms, 'slug')) : '' ?>">
        <?php echo growtype_post_include_view(
            'preview.basic.content',
            [
                'post' => $post,
                'intro_content_length' => $intro_content_length ?? ''
            ]
        ); ?>
    </a>
<?php } ?>
