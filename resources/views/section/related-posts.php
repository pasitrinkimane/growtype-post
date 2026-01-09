<section class="s-posts s-posts-related">
    <div class="container">
        <div class="row">
            <div class="col">
                <?php if (isset($section_title) && !empty($section_title)) { ?>
                    <div class="block-title">
                        <h3 class="e-title-section"><?php echo $section_title ?></h3>
                    </div>
                <?php } ?>

                <?php if (function_exists('growtype_post_render_all')) {
                    echo growtype_post_render_all($posts, $params);
                } ?>
            </div>
        </div>
    </div>
</section>
