<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Growtype_Post
 * @subpackage Growtype_Post/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Growtype_Post
 * @subpackage Growtype_Post/admin
 * @author     Your Name <email@example.com>
 */
class Growtype_Post_Admin
{
    use AdminSettingsGeneralTrait;

    const GROWTYPE_WC_SETTINGS_DEFAULT_TAB = 'general';
    const SETTINGS_PAGE_SLUG = 'growtype-post-settings';

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $Growtype_Post The ID of this plugin.
     */
    private $Growtype_Post;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $Growtype_Post The name of this plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($Growtype_Post, $version)
    {
        $this->Growtype_Post = $Growtype_Post;
        $this->version = $version;

        if (is_admin()) {
            $this->load_methods();
        }
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->Growtype_Post, GROWTYPE_POST_URL_PUBLIC . 'styles/growtype-post-admin.css', array (), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->Growtype_Post, GROWTYPE_POST_URL_PUBLIC . 'scripts/growtype-post-admin.js', array ('jquery'), $this->version, false);
    }

    /**
     * Load the required methods for this plugin.
     *
     */
    private function load_methods()
    {
        /**
         * Plugin settings
         */
        require GROWTYPE_POST_PATH . '/admin/pages/growtype-post-admin-pages.php';
        new Growtype_Post_Admin_Pages();
    }
}
