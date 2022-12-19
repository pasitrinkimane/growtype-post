<div class="growtype-post-single-header">
    <div class="growtype-post-single-img" style="background: url(<?php echo growtype_post_get_featured_image_url($post) ?>);background-position: center;background-size: cover;background-repeat: no-repeat;"></div>
    <p class="e-title"><?php echo $post->post_title ?></p>
</div>
<div class="growtype-post-single-content">
    <p class="e-details"><?php echo $post->post_excerpt ?></p>
    <div class="growtype-post-single-footer">
        <button class="btn btn-basic"><?php echo __('Read more', 'growtype') ?></button>
        <span class="e-arrow"></span>
    </div>
</div>
