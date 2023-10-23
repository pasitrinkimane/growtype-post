<?php if (isset($post_is_a_link) && $post_is_a_link === false) { ?>
    <div class="<?php echo isset($post_classes) ? $post_classes : '' ?>">
        <?php echo growtype_post_include_view('preview.review.content', ['post' => $post]); ?>
    </div>
<?php } else { ?>
    <a href="<?php echo get_permalink($post->ID) ?>" class="<?php echo isset($post_classes) ? $post_classes : '' ?>">
        <?php echo growtype_post_include_view('preview.review.content', ['post' => $post]); ?>
    </a>
<?php } ?>
