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

    const SETTINGS_DEFAULT_TAB = 'general';

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

        add_filter('growtype_form_google_auth_request_is_excluded', array ($this, 'exclude_auth_request'), 0, 2);

        add_action('init', array ($this, 'validate_auth'));
    }

    public function validate_auth()
    {
        if (isset($_GET['code']) && current_user_can('manage_options')) {
            if (strpos($_SERVER['REQUEST_URI'], self::google_auth_redirect_path()) !== false) {
                update_user_meta(get_current_user_id(), 'growtype_post_google_auth_code', $_GET['code']);
                wp_redirect(admin_url('edit.php'));
                exit;
            } elseif (strpos($_SERVER['REQUEST_URI'], self::pinterest_auth_redirect_path()) !== false) {
                update_user_meta(get_current_user_id(), 'growtype_post_pinterest_auth_code', $_GET['code']);
                wp_redirect(admin_url('edit.php'));
                exit;
            }
        }
    }

    public function exclude_auth_request($excluded, $request_uri)
    {
        if (strpos($request_uri, self::google_auth_redirect_path()) !== false) {
            $excluded = true;
        } elseif (strpos($request_uri, self::pinterest_auth_redirect_path()) !== false) {
            $excluded = true;
        }

        return $excluded;
    }

    public static function google_auth_redirect_path()
    {
        return 'growtype-post-google-auth';
    }

    public static function pinterest_auth_redirect_path()
    {
        return 'growtype-post-pinterest-auth';
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
         * Share
         */
        require GROWTYPE_POST_PATH . '/admin/methods/growtype-post-admin-methods.php';
        new Growtype_Post_Admin_Methods();

        /**
         * Plugin settings
         */
        require GROWTYPE_POST_PATH . '/admin/pages/growtype-post-admin-pages.php';
        new Growtype_Post_Admin_Pages();
    }
}
