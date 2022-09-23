<div class="b-post-single-inner">
    <div class="e-img" style="background: url(<?php echo growtype_post_get_featured_image_url($post) ?>);background-position: center;background-size: cover;background-repeat: no-repeat;"></div>
    <div class="b-content">
        <?php if (!empty($post->post_title)) { ?>
            <h4><?php echo $post->post_title ?></h4>
        <?php } ?>
        <?php if (!empty($post->post_excerpt)) { ?>
            <p class="e-excerpt"><?php echo $post->post_excerpt ?></p>
        <?php } ?>
        <div class="e-intro">
            <?php echo growtype_post_get_limited_content($post->post_content, isset($content_length) ? $content_length : 200) ?>
        </div>
    </div>
    <div class="b-actions">
        <button class="btn btn-primary">
            <?php echo isset($cta_label) ? $cta_label : __('Continue reading', 'growtype'); ?>
        </button>
    </div>
</div>
