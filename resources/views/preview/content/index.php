<div class="growtype-post-single growtype-post-content"
    <?php echo isset($post_terms_html) ? $post_terms_html : '' ?>
>
    <?php echo apply_filters('the_content', $post->post_content) ?>
</div>
