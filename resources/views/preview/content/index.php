<?php
$post = isset($post) ? $post : get_post();
$terms = wp_get_post_terms($post->ID, get_post_type($post) . '_tax');
?>

<div class="growtype-post-single growtype-post-content" data-cat="<?php echo !empty($terms) ? implode(',', array_pluck($terms, 'slug')) : '' ?>">
    <?php echo apply_filters('the_content', $post->post_content) ?>
</div>
