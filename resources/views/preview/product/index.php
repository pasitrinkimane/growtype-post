<?php
$post = isset($post) ? $post : get_post();
$terms = wp_get_post_terms($post->ID, get_post_type($post) . '_tax');
$terms = is_wp_error($terms) ? [] : $terms;
?>

<div class="<?php echo $post_classes ?>" data-cat="<?php echo isset($terms) && !empty($terms) ? implode(',', array_pluck($terms, 'slug')) : '' ?>">
    <?php wc_get_template_part('content', 'product'); ?>
</div>
