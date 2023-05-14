<?php
$variables = [];

$post = isset($post) ? $post : get_post();
$terms = wp_get_post_terms($post->ID, get_post_type($post) . '_tax');
$terms = is_wp_error($terms) ? [] : $terms;

$intro_content_length = isset($intro_content_length) && !empty($intro_content_length) ? $intro_content_length : null;

$extra_var = isset($extra_var) && !empty($extra_var) ? $extra_var : [];

$file_path = 'preview.basic.content';

if (!empty($preview_style)) {
    $file_path = 'preview.' . $preview_style . '.content';
}

$variables['post'] = $post;
$variables['terms'] = $terms;
$variables['intro_content_length'] = $intro_content_length;
$variables['extra_var'] = $extra_var;

if (isset($post_link) && $post_link === false) { ?>
    <div class="<?php echo $post_classes ?>" <?php echo isset($post_in_modal) && $post_in_modal ? 'data-bs-toggle="modal" data-bs-target="#growtypePostModal-' . $post->ID . '"' : '' ?> data-cat="<?php echo isset($terms) && !empty($terms) ? implode(',', array_pluck($terms, 'slug')) : '' ?>" <?php echo isset($post_attributes) ? $post_attributes : '' ?>>
        <?php echo growtype_post_include_view(
            $file_path,
            $variables
        ); ?>
    </div>
<?php } else { ?>
    <a href="<?php echo get_permalink($post->ID) ?>" class="<?php echo isset($post_classes) ? $post_classes : '' ?>" <?php echo isset($post_in_modal) && $post_in_modal ? 'data-bs-toggle="modal" data-bs-target="#growtypePostModal-' . $post->ID . '"' : '' ?> data-cat="<?php echo isset($terms) && !empty($terms) ? implode(',', array_pluck($terms, 'slug')) : '' ?>" <?php echo isset($post_attributes) ? $post_attributes : '' ?>>
        <?php echo growtype_post_include_view(
            $file_path,
            $variables
        ); ?>
    </a>
<?php } ?>
