<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Growtype_Post
 * @subpackage Growtype_Post/admin/partials
 */

trait AdminSettingsWoocommercePlugin
{
    public function woocommerce_content()
    {
        /**
         * WooCommerce Product Upload Json Content
         */
        register_setting(
            'Growtype_Post_settings_woocommerce', // settings group name
            'Growtype_Post_wc_product_json_content' // option name
        );

        add_settings_field(
            'Growtype_Post_wc_product_json_content',
            'Json Content',
            array ($this, 'Growtype_Post_wc_product_json_content_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_woocommerce'
        );

        /**
         * Redirect after product creation
         */
        register_setting(
            'Growtype_Post_settings_woocommerce', // settings group name
            'Growtype_Post_redirect_after_product_creation', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'Growtype_Post_redirect_after_product_creation',
            'Redirect Url After Upload Form Submit',
            array ($this, 'Growtype_Post_redirect_after_product_creation_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_woocommerce'
        );

        /**
         * Product upload page
         */
        register_setting(
            'Growtype_Post_settings_woocommerce', // settings group name
            'Growtype_Post_product_upload_page'
        );

        add_settings_field(
            'Growtype_Post_product_upload_page',
            'Product Upload Page',
            array ($this, 'Growtype_Post_product_upload_page_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_woocommerce'
        );

        /**
         * Default product status
         */
        register_setting(
            'Growtype_Post_settings_woocommerce', // settings group name
            'Growtype_Post_default_product_status', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'Growtype_Post_default_product_status',
            'Default Product Status',
            array ($this, 'Growtype_Post_default_product_status_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_woocommerce'
        );

        /**
         * Default catalog visibility
         */
        register_setting(
            'Growtype_Post_settings_woocommerce', // settings group name
            'Growtype_Post_default_product_catalog_visibility', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'Growtype_Post_default_product_catalog_visibility',
            'Default Product Catalog Visibility',
            array ($this, 'Growtype_Post_default_product_catalog_visibility_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_woocommerce'
        );

        /**
         * Default product type
         */
        register_setting(
            'Growtype_Post_settings_woocommerce', // settings group name
            'Growtype_Post_default_product_type', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'Growtype_Post_default_product_type',
            'Default Product Type',
            array ($this, 'Growtype_Post_default_product_type_callback'),
            'growtype-post-settings',
            'Growtype_Post_settings_woocommerce'
        );
    }

    /**
     * Wc upload product
     */
    function Growtype_Post_wc_product_json_content_callback()
    {
        ?>
        <textarea id="Growtype_Post_wc_product_json_content" class="Growtype_Post_json_content" name="Growtype_Post_wc_product_json_content" rows="40" cols="100" style="width: 100%;margin-bottom: 100px;"><?= get_option('Growtype_Post_wc_product_json_content') ?></textarea>
        <?php
    }

    /**
     * Wc upload product
     */
    function Growtype_Post_redirect_after_product_creation_callback()
    {
        $input_val = get_option('Growtype_Post_redirect_after_product_creation');
        ?>
        <input id="Growtype_Post_redirect_after_product_creation" class="input" name="Growtype_Post_redirect_after_product_creation" style="width: 100%;" value="<?= $input_val ?>">
        <?php
    }

    /**
     * Default product status
     */
    function Growtype_Post_default_product_status_callback()
    {
        $selected = Growtype_Post_default_product_status();
        $options = get_post_statuses();
        ?>
        <select name='Growtype_Post_default_product_status'>
            <?php
            foreach ($options as $value => $option) { ?>
                <option value='<?= $value ?>' <?php selected($selected, $value); ?>><?= $option ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Catalog visibility
     */
    function Growtype_Post_default_product_catalog_visibility_callback()
    {
        $selected = Growtype_Post_default_product_catalog_visibility();
        $options = [];

        if (class_exists('woocommerce')) {
            $options = wc_get_product_visibility_options();
        }
        ?>
        <select name='Growtype_Post_default_product_catalog_visibility'>
            <?php
            foreach ($options as $value => $option) { ?>
                <option value='<?= $value ?>' <?php selected($selected, $value); ?>><?= $option ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Catalog visibility
     */
    function Growtype_Post_default_product_type_callback()
    {
        $selected = Growtype_Post_default_product_type();
        $options = [];
        if (class_exists('woocommerce')) {
            $options = wc_get_product_types();
        }
        ?>
        <select name='Growtype_Post_default_product_type'>
            <?php
            foreach ($options as $value => $option) { ?>
                <option value='<?= $value ?>' <?php selected($selected, $value); ?>><?= $option ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Upload page
     */
    function Growtype_Post_product_upload_page_callback()
    {
        $selected = get_option('Growtype_Post_product_upload_page');
        $pages = get_pages();
        ?>
        <select name='Growtype_Post_product_upload_page'>
            <option value='none' <?php selected($selected, 'none'); ?>>none</option>
            <?php
            foreach ($pages as $page) { ?>
                <option value='<?= $page->ID ?>' <?php selected($selected, $page->ID); ?>><?= __($page->post_title, "growtype-post") ?></option>
            <?php } ?>
        </select>
        <?php
    }
}
