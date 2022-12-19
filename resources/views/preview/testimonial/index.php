<div class="b-testimonial b-testimonial-picture-bottom">
    <div class="b-testimonial-content">
        <div class="e-quate">"</div>
        <?php echo $post->post_content ?>
    </div>
    <div class="b-testimonial-avatar">
        <div class="b-testimonial-avatar-image" style="background: url(<?php echo growtype_post_get_featured_image_url($post) ?>);background-position: center;background-size: cover;background-repeat: no-repeat;"></div>
        <div class="b-testimonial-avatar-description">
            <p class="b-testimonial-avatar-title"><?php echo $post->post_title ?></p>
            <?php if (!empty($tax)) { ?>
                <p class="b-testimonial-avatar-position"><?php echo $tax->name ?></p>
            <?php } ?>
        </div>
    </div>
</div>
