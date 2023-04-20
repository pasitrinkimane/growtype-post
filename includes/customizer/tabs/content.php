<?php

/**
 * Posts panel
 */
$wp_customize->add_panel(
    'posts',
    array (
        'priority' => 150,
        'capability' => '',
        'theme_supports' => '',
        'title' => __('Growtype - Post', 'growtype'),
    )
);

/**
 * Single posts page settings
 */
$wp_customize->add_section(
    'post_single_page',
    array (
        'title' => __('Post Content', 'growtype'),
        'priority' => 5,
        'panel' => 'posts',
    )
);

/**
 * Intro
 */
$wp_customize->add_setting('post_single_page_details',
    array (
        'default' => '',
        'transport' => 'postMessage'
    )
);

$wp_customize->add_control(new Skyrocket_Simple_Notice_Custom_control($wp_customize, 'post_single_page_details',
    array (
        'label' => __('Post Content'),
        'description' => __('Below you can change post single page settings.'),
        'section' => 'post_single_page'
    )
));

/**
 * Title
 */
$wp_customize->add_setting('growtype_post_single_page_title_enabled',
    array (
        'default' => 1,
        'transport' => 'refresh',
    )
);

$wp_customize->add_control(new Skyrocket_Toggle_Switch_Custom_control($wp_customize, 'growtype_post_single_page_title_enabled',
    array (
        'label' => esc_html__('Title'),
        'section' => 'post_single_page',
        'description' => __('Post title enabled.', 'growtype'),
    )
));

/**
 * Image
 */
$wp_customize->add_setting('growtype_post_single_page_featured_image_enabled',
    array (
        'default' => 1,
        'transport' => 'refresh',
    )
);

$wp_customize->add_control(new Skyrocket_Toggle_Switch_Custom_control($wp_customize, 'growtype_post_single_page_featured_image_enabled',
    array (
        'label' => esc_html__('Featured Image'),
        'section' => 'post_single_page',
        'description' => __('Featured image enabled.', 'growtype'),
    )
));

/**
 * Cta
 */
$wp_customize->add_setting('growtype_post_single_page_cta_enabled',
    array (
        'default' => 1,
        'transport' => 'refresh',
    )
);

$wp_customize->add_control(new Skyrocket_Toggle_Switch_Custom_control($wp_customize, 'growtype_post_single_page_cta_enabled',
    array (
        'label' => esc_html__('CTA'),
        'section' => 'post_single_page',
        'description' => __('CTA enabled.', 'growtype'),
    )
));

/**
 * Back
 */
$wp_customize->add_setting('growtype_post_back_btn_enabled',
    array (
        'default' => 1,
        'transport' => 'refresh',
    )
);

$wp_customize->add_control(new Skyrocket_Toggle_Switch_Custom_control($wp_customize, 'growtype_post_back_btn_enabled',
    array (
        'label' => esc_html__('Back btn'),
        'section' => 'post_single_page',
        'description' => __('Button to go back.', 'growtype'),
    )
));

/**
 * taxonomy
 */
$wp_customize->add_setting('growtype_post_taxonomy_enabled',
    array (
        'default' => 1,
        'transport' => 'refresh',
    )
);

$wp_customize->add_control(new Skyrocket_Toggle_Switch_Custom_control($wp_customize, 'growtype_post_taxonomy_enabled',
    array (
        'label' => esc_html__('Taxonomy'),
        'section' => 'post_single_page',
        'description' => __('Show post taxonomy.', 'growtype'),
    )
));

/**
 * Reading time
 */
$wp_customize->add_setting('growtype_post_reading_time_enabled',
    array (
        'default' => 1,
        'transport' => 'refresh',
    )
);

$wp_customize->add_control(new Skyrocket_Toggle_Switch_Custom_control($wp_customize, 'growtype_post_reading_time_enabled',
    array (
        'label' => esc_html__('Reading time'),
        'section' => 'post_single_page',
        'description' => __('Reading time enabled.', 'growtype'),
    )
));

/**
 * Section intro
 */
$wp_customize->add_setting('post_single_page_related_posts_details',
    array (
        'default' => '',
        'transport' => 'postMessage'
    )
);

$wp_customize->add_control(new Skyrocket_Simple_Notice_Custom_control($wp_customize, 'post_single_page_related_posts_details',
    array (
        'label' => __('Related Posts'),
        'description' => __('Below you can change related posts settings.'),
        'section' => 'post_single_page'
    )
));

/**
 * Related posts
 */
$wp_customize->add_setting('growtype_post_related_posts_enabled',
    array (
        'default' => 1,
        'transport' => 'refresh',
    )
);

$wp_customize->add_control(new Skyrocket_Toggle_Switch_Custom_control($wp_customize, 'growtype_post_related_posts_enabled',
    array (
        'label' => esc_html__('Enabled'),
        'section' => 'post_single_page',
        'description' => __('Related posts are enabled.', 'growtype'),
    )
));
