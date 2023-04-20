<?php
$post = isset($post) ? $post : get_post();
$terms = wp_get_post_terms($post->ID, get_post_type($post) . '_tax');
$terms = is_wp_error($terms) ? [] : $terms;

$file_path = 'preview.basic.content';

if (!empty($preview_style)) {
    $file_path = 'preview.' . $preview_style . '.content';
}

if (isset($post_link) && $post_link === false) { ?>
    <div class="<?php echo $post_classes ?>" <?php echo isset($post_in_modal) && $post_in_modal ? 'data-bs-toggle="modal" data-bs-target="#growtypePostModal-' . $post->ID . '"' : '' ?> data-cat="<?php echo isset($terms) && !empty($terms) ? implode(',', array_pluck($terms, 'slug')) : '' ?>">
        <?php echo growtype_post_include_view(
            $file_path,
            [
                'post' => $post,
                'intro_content_length' => $intro_content_length ?? ''
            ]
        ); ?>
    </div>
<?php } else { ?>
    <a href="<?php echo get_permalink($post->ID) ?>" class="<?php echo isset($post_classes) ? $post_classes : '' ?>" <?php echo isset($post_in_modal) && $post_in_modal ? 'data-bs-toggle="modal" data-bs-target="#growtypePostModal-' . $post->ID . '"' : '' ?> data-cat="<?php echo isset($terms) && !empty($terms) ? implode(',', array_pluck($terms, 'slug')) : '' ?>">
        <?php echo growtype_post_include_view(
            $file_path,
            [
                'post' => $post,
                'intro_content_length' => $intro_content_length ?? ''
            ]
        ); ?>
    </a>
<?php } ?>
