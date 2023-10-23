<div class="growtype-post-single growtype-post-content" data-cat="<?php echo !empty($terms) ? implode(',', array_pluck($terms, 'slug')) : '' ?>">
    <?php echo apply_filters('the_content', $post->post_content) ?>
</div>
