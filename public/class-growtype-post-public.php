<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Growtype_Post
 * @subpackage Growtype_Post/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Growtype_Post
 * @subpackage Growtype_Post/public
 * @author     Your Name <email@example.com>
 */
class Growtype_Post_Public
{

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
     * @param string $Growtype_Post The name of the plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($Growtype_Post, $version)
    {
        $this->Growtype_Post = $Growtype_Post;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Growtype_Post_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Growtype_Post_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->Growtype_Post, GROWTYPE_POST_URL_PUBLIC . 'styles/growtype-post.css', array (), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Growtype_Post_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Growtype_Post_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script($this->Growtype_Post, GROWTYPE_POST_URL_PUBLIC . 'scripts/growtype-post.js', array ('jquery'), $this->version, true);

        wp_localize_script($this->Growtype_Post, 'growtype_post', array (
                'ajax_url' => admin_url('admin-ajax.php'),
                'post_id' => get_the_ID(),
                'wrappers' => [],
            )
        );
    }

}
