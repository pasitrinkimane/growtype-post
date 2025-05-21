<?php

/**
 *
 */
class Growtype_Post_Block
{
    const ATTRIBUTES_FORMATTED_IN_SHORTCODE = [
        'meta_query' => [
            'skip' => false,
            'get_value' => 'meta_query',
            'formatting' => [
                'decode' => false,
            ],
        ],
        'tax_query' => [
            'skip' => false,
            'get_value' => 'tax_query',
            'formatting' => [
                'decode' => false,
            ],
        ]
    ];

    function __construct()
    {
        add_action('init', array ($this, 'create_block_growtype_post_block_init'));

        add_action('enqueue_block_editor_assets', array ($this, 'localize_settings'));
    }

    function create_block_growtype_post_block_init()
    {
        register_block_type_from_metadata(GROWTYPE_POST_PATH . 'build', [
            'render_callback' => array ($this, 'render_callback_growtype_post'),
        ]);
    }

    function localize_settings() {
        $show_meta_boxes = get_option('growtype_post_admin_edit_post_show_meta_boxes');

        wp_localize_script('growtype-post-editor-script', 'growtypePostAdminSettings', array(
            'show_meta_boxes' => $show_meta_boxes,
        ));
    }

    function render_callback_growtype_post($block_attributes, $content)
    {
        $shortcode = $this->format_shortcode($block_attributes);

        $shortcode_content = preg_replace('~\[(.+?)\]~', $shortcode, $content);

        return do_shortcode($shortcode_content);
    }

    /**
     * @param $block_attributes
     * @return string
     */
    function format_shortcode($block_attributes)
    {
        $shortcode = '[growtype_post';
        foreach ($block_attributes as $key => $value) {

            if (isset(self::ATTRIBUTES_FORMATTED_IN_SHORTCODE[$key]['skip']) && self::ATTRIBUTES_FORMATTED_IN_SHORTCODE[$key]['skip'] === true) {
                continue;
            }

            if (isset(self::ATTRIBUTES_FORMATTED_IN_SHORTCODE[$key]['get_value']) && isset($value[self::ATTRIBUTES_FORMATTED_IN_SHORTCODE[$key]['get_value']])) {
                $value = $value[self::ATTRIBUTES_FORMATTED_IN_SHORTCODE[$key]['get_value']];
            }

            if (isset(self::ATTRIBUTES_FORMATTED_IN_SHORTCODE[$key]['formatting']) && !empty($value)) {
                $value = urlencode(json_encode(json_decode($value, true)));
            }

            $shortcode .= ' ' . $key . '="' . $value . '"';
        }
        $shortcode .= ']';

        return $shortcode;
    }
}
