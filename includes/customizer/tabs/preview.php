<?php

/**
 * Posts preview settings
 */
$wp_customize->add_section(
    'post_preview',
    array (
        'title' => __('Post Preview', 'growtype'),
        'panel' => 'posts',
    )
);

/**
 * Intro
 */
$wp_customize->add_setting('post_preview_details',
    array (
        'default' => '',
        'transport' => 'postMessage'
    )
);

$wp_customize->add_control(new Skyrocket_Simple_Notice_Custom_control($wp_customize, 'post_preview_details',
    array (
        'label' => __('Post Preview'),
        'description' => __('Below you can change post preview settings.'),
        'section' => 'post_preview'
    )
));

/**
 * Featured image
 */
$wp_customize->add_setting('growtype_post_preview_featured_image_enabled',
    array (
        'default' => 1,
        'transport' => 'refresh',
    )
);

$wp_customize->add_control(new Skyrocket_Toggle_Switch_Custom_control($wp_customize, 'growtype_post_preview_featured_image_enabled',
    array (
        'label' => esc_html__('Featured Image'),
        'section' => 'post_preview',
        'description' => __('Featured image enabled.', 'growtype'),
    )
));

/**
 * Categories
 */
$wp_customize->add_setting('growtype_post_preview_categories_enabled',
    array (
        'default' => 1,
        'transport' => 'refresh',
    )
);

$wp_customize->add_control(new Skyrocket_Toggle_Switch_Custom_control($wp_customize, 'growtype_post_preview_categories_enabled',
    array (
        'label' => esc_html__('Categories'),
        'section' => 'post_preview',
        'description' => __('Categories enabled.', 'growtype'),
    )
));

/**
 * Date
 */
$wp_customize->add_setting('growtype_post_preview_date_enabled',
    array (
        'default' => 1,
        'transport' => 'refresh',
    )
);

$wp_customize->add_control(new Skyrocket_Toggle_Switch_Custom_control($wp_customize, 'growtype_post_preview_date_enabled',
    array (
        'label' => esc_html__('Date'),
        'section' => 'post_preview',
        'description' => __('Date enabled.', 'growtype'),
    )
));

/**
 * Continue Actions
 */
$wp_customize->add_setting('growtype_post_preview_social_actions_enabled',
    array (
        'default' => 1,
        'transport' => 'refresh',
    )
);

$wp_customize->add_control(new Skyrocket_Toggle_Switch_Custom_control($wp_customize, 'growtype_post_preview_social_actions_enabled',
    array (
        'label' => esc_html__('Social Actions'),
        'section' => 'post_preview',
        'description' => __('Social actions enabled.', 'growtype'),
    )
));

/**
 * Continue Actions
 */
$wp_customize->add_setting('growtype_post_preview_continue_actions_enabled',
    array (
        'default' => 1,
        'transport' => 'refresh',
    )
);

$wp_customize->add_control(new Skyrocket_Toggle_Switch_Custom_control($wp_customize, 'growtype_post_preview_continue_actions_enabled',
    array (
        'label' => esc_html__('Continue Actions'),
        'section' => 'post_preview',
        'description' => __('CTA enabled.', 'growtype'),
    )
));
