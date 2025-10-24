<?php

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
     * @param $Growtype_Post
     * @param $version
     */
    public function __construct($Growtype_Post, $version)
    {
        $this->Growtype_Post = $Growtype_Post;
        $this->version = $version;
    }

    /**
     * @return void
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->Growtype_Post, GROWTYPE_POST_URL_PUBLIC . 'styles/growtype-post.css', array (), $this->version, 'all');
    }

    /**
     * @return void
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->Growtype_Post, GROWTYPE_POST_URL_PUBLIC . 'scripts/growtype-post.js', array ('jquery'), $this->version, true);

        wp_localize_script($this->Growtype_Post, 'growtype_post', array (
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('growtype_post_ajax_nonce'),
                'post_id' => get_the_ID(),
                'wrappers' => [],
            )
        );
    }

}
