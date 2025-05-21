<?php
$parent_classes = ['growtype-post-terms-filters'];

if (isset($parent_class) && !empty($parent_class)) {
    array_push($parent_classes, $parent_class);
}

?>

<div
    class="<?php echo implode(' ', $parent_classes) ?>"
    data-selections-included-in-url="<?php echo $args['terms_navigation_selections_included_in_url'] ?>"
    data-nav-style-desktop="<?php echo $args['terms_navigation_style'] ?>"
    data-nav-style-mobile="<?php echo $args['terms_navigation_style_mobile'] ?>"
>

    <?php do_action('growtype_post_terms_filters_after_open', $terms, $terms_navigation_taxonomies); ?>

    <?php foreach ($terms as $key => $existing_terms) {
        $js_init_cat = isset($existing_terms['settings']['js_init_cat']) ? $existing_terms['settings']['js_init_cat'] : '';

        if (isset($args['terms_navigation_default_term_selected']) && !empty($args['terms_navigation_default_term_selected'])) {
            $js_init_cat = $args['terms_navigation_default_term_selected'];
        }

        ?>
        <div class="growtype-post-terms-filters-single"
             data-type="<?php echo $key ?>"
        >
            <?php do_action('growtype_post_terms_filters_single_after_open', $existing_terms, $key); ?>

            <div
                class="growtype-post-terms-filter <?php echo isset($existing_terms['settings']['is_closed_by_default']) && $existing_terms['settings']['is_closed_by_default'] ? 'is-closed' : '' ?>"
                data-type="<?php echo $key ?>"
                data-init-cat="<?php echo $js_init_cat ?>"
            >
                <?php if (isset($existing_terms['values'])) {
                    $counter = 0;
                    $toggle_trigger_exists = false;
                    foreach ($existing_terms['values'] as $existing_term) {
                        $existing_term = is_array($existing_term) ? (object)$existing_term : $existing_term;

                        if (!$args['terms_navigation_show_all_option_visible'] && isset($existing_term->slug) && $existing_term->slug === 'all') {
                            continue;
                        }

                        $trigger_type = isset($existing_terms['settings']['trigger_type']) ? $existing_terms['settings']['trigger_type'] : $args['terms_navigation_default_term_trigger_type'];
                        $multiple_select = isset($existing_terms['settings']['multiple_select']) && $existing_terms['settings']['multiple_select'] === true ? 'true' : 'false';

                        $is_selected = false;
                        if (isset($existing_terms['settings']['default_value'])) {
                            if ($existing_terms['settings']['default_value'] === $existing_term->slug) {
                                $is_selected = true;
                            }
                        } elseif (empty($js_init_cat) && $counter === 0 && $args['terms_navigation_show_all_option_visible']) {
                            $is_selected = true;
                        }
                        ?>
                        <div class="growtype-post-terms-filter-btn btn btn-secondary <?php echo $is_selected ? 'is-active' : '' ?>"
                             data-cat-<?php echo $key ?>="<?php echo $existing_term->slug ?>"
                             data-trigger-type="<?php echo $trigger_type ?>"
                             data-multiple-select="<?php echo $multiple_select ?>"
                             data-disabled="<?php echo isset($existing_term->disabled) ? $existing_term->disabled : false ?>"
                        >
                            <?php echo $existing_term->name ?>
                        </div>
                        <?php $counter++; ?>
                    <?php }
                } ?>
            </div>

            <select
                <?php echo isset($existing_terms['settings']['is_multiple']) && $existing_terms['settings']['is_multiple'] ? 'multiple' : '' ?>
                name="<?php echo $key ?>"
                class="growtype-post-terms-filter"
                data-type="<?php echo $key ?>"
                data-init-cat="<?php echo $js_init_cat ?>"
                data-allow-single-deselect="<?php echo isset($existing_terms['settings']['allow_single_deselect']) && $existing_terms['settings']['allow_single_deselect'] ? 'true' : 'false' ?>"
                data-placeholder="<?php echo isset($existing_terms['settings']['placeholder']) ? $existing_terms['settings']['placeholder'] : 'Select ...' ?>"
            >
                <?php if (!isset($existing_terms['settings']['is_multiple']) || !$existing_terms['settings']['is_multiple']) { ?>
                    <option value="" class="" data-cat-<?php echo $key ?>="none"><?php echo isset($existing_terms['settings']['placeholder']) ? $existing_terms['settings']['placeholder'] : 'Select ...' ?></option>
                <?php } ?>

                <?php if (isset($existing_terms['values'])) {
                    foreach ($existing_terms['values'] as $existing_term) {

                        $existing_term = is_array($existing_term) ? (object)$existing_term : $existing_term;
                        $is_selected = false;

                        if (isset($existing_terms['settings']['default_value'])) {
                            if ($existing_terms['settings']['default_value'] === $existing_term->slug) {
                                $is_selected = true;
                            }
                        }
                        ?>
                        <option value="<?php echo $existing_term->name ?>" <?php echo $is_selected ? 'selected' : '' ?> class="" data-cat-<?php echo $key ?>="<?php echo $existing_term->slug ?>"><?php echo isset($existing_terms['settings']['select_value_not_none_prefix']) && !empty($existing_terms['settings']['select_value_not_none_prefix']) ? $existing_terms['settings']['select_value_not_none_prefix'] : '' ?><?php echo $existing_term->name ?></option>
                    <?php }
                } ?>
            </select>

            <?php do_action('growtype_post_terms_filters_single_before_close', $existing_terms, $key); ?>
        </div>
    <?php } ?>

    <?php do_action('growtype_post_terms_filters_before_close', $terms, $terms_navigation_taxonomies); ?>
</div>
