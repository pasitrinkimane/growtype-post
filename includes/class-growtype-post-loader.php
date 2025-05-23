<?php

/**
 * Register all actions and filters for the plugin
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Growtype_Post
 * @subpackage Growtype_Post/includes
 */

use function App\sage;
use Roots\Sage\Template\Blade;
use Roots\Sage\Template\BladeProvider;

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Growtype_Post
 * @subpackage Growtype_Post/includes
 * @author     Your Name <email@example.com>
 */
class Growtype_Post_Loader
{

    /**
     * The array of actions registered with WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array $actions The actions registered with WordPress to fire when the plugin loads.
     */
    protected $actions;

    /**
     * The array of filters registered with WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array $filters The filters registered with WordPress to fire when the plugin loads.
     */
    protected $filters;

    /**
     * Initialize the collections used to maintain the actions and filters.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        $this->actions = array ();
        $this->filters = array ();

        $this->load_methods();
        $this->load_templates();
    }

    /**
     * Add a new action to the collection to be registered with WordPress.
     *
     * @param string $hook The name of the WordPress action that is being registered.
     * @param object $component A reference to the instance of the object on which the action is defined.
     * @param string $callback The name of the function definition on the $component.
     * @param int $priority Optional. The priority at which the function should be fired. Default is 10.
     * @param int $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.
     * @since    1.0.0
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1)
    {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Add a new filter to the collection to be registered with WordPress.
     *
     * @param string $hook The name of the WordPress filter that is being registered.
     * @param object $component A reference to the instance of the object on which the filter is defined.
     * @param string $callback The name of the function definition on the $component.
     * @param int $priority Optional. The priority at which the function should be fired. Default is 10.
     * @param int $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1
     * @since    1.0.0
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1)
    {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * A utility function that is used to register the actions and hooks into a single
     * collection.
     *
     * @param array $hooks The collection of hooks that is being registered (that is, actions or filters).
     * @param string $hook The name of the WordPress filter that is being registered.
     * @param object $component A reference to the instance of the object on which the filter is defined.
     * @param string $callback The name of the function definition on the $component.
     * @param int $priority The priority at which the function should be fired.
     * @param int $accepted_args The number of arguments that should be passed to the $callback.
     * @return   array                                  The collection of actions and filters registered with WordPress.
     * @since    1.0.0
     * @access   private
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args)
    {

        $hooks[] = array (
            'hook' => $hook,
            'component' => $component,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args
        );

        return $hooks;

    }

    /**
     * Register the filters and actions with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], array ($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }

        foreach ($this->actions as $hook) {
            add_action($hook['hook'], array ($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }
    }

    /**
     * Load the required methods for this plugin.
     *
     */
    private function load_methods()
    {
        /**
         * Autoload vendor
         */
        require_once GROWTYPE_POST_PATH . '/vendor/autoload.php';

        /**
         * Ajax
         */
        require_once GROWTYPE_POST_PATH . 'includes/methods/ajax/class-growtype-post-ajax.php';
        new Growtype_Post_Ajax();

        /**
         * Api
         */
        require_once GROWTYPE_POST_PATH . 'includes/methods/api/class-growtype-post-api.php';
        new Growtype_Post_Api();

        /**
         * Service
         */
        require_once GROWTYPE_POST_PATH . 'includes/service/class-growtype-post-service.php';
        new Growtype_Post_Service();

        /**
         * Content
         */
        require_once GROWTYPE_POST_PATH . 'includes/methods/content/archive.php';
        require_once GROWTYPE_POST_PATH . 'includes/methods/content/post.php';

        /**
         * Customizer
         */
        require_once GROWTYPE_POST_PATH . 'includes/customizer/Growtype_Post_Customizer.php';
        new Growtype_Post_Customizer();
    }

    private function load_templates()
    {
        add_filter('theme_post_templates', [$this, 'register_dynamic_templates']);
        add_filter('template_include', [$this, 'load_dynamic_template']);
    }

    function register_dynamic_templates($templates)
    {
        $template_directory = GROWTYPE_POST_PATH . 'resources/views/';

        $template_files = glob($template_directory . 'template-*.php');

        foreach ($template_files as $file) {
            if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $file_contents = file_get_contents($file);
                if (preg_match('/Template Name:\s*(.+)/', $file_contents, $matches)) {
                    $template_name = trim($matches[1]);
                    $template_filename = basename($file);

                    $templates[$template_filename] = $template_name;
                }
            }
        }

        return $templates;
    }

    function load_dynamic_template($template)
    {
        $template_slug = get_page_template_slug();
        $custom_template = GROWTYPE_POST_PATH . 'resources/views/' . $template_slug;

        if ($template_slug && pathinfo($custom_template, PATHINFO_EXTENSION) === 'php' && file_exists($custom_template)) {
            return $custom_template;
        }

        return $template;
    }
}
