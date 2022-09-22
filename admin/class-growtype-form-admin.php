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
     * Traits
     */
    use AdminSettingsLogin;
    use AdminSettingsSignup;
    use AdminSettingsWoocommercePlugin;
    use AdminSettingsPost;
    use AdminSettingsExamples;
    use AdminAppearanceMenu;

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
            add_action('admin_menu', array ($this, 'add_custom_options_page'));

            /**
             * AdminSignup
             */
            add_action('admin_init', array ($this, 'signup_content'));

            /**
             * AdminLogin
             */
            add_action('admin_init', array ($this, 'login_content'));

            /**
             * AdminPost
             */
            add_action('admin_init', array ($this, 'post_content'));

            /**
             * AdminWoocommercePlugin
             */
            add_action('admin_init', array ($this, 'woocommerce_content'));

            /**
             * AdminExamples
             */
            add_action('admin_init', array ($this, 'examples_content'));

            /**
             * Admin menu in appearance menus
             */
            add_action('load-nav-menus.php', array ($this, 'add_nav_menu_meta_box'));

            /**
             * Load methods
             */
            $this->load_methods();
        } else {
            /**
             * Growtype form menu links update
             */
            add_filter('walker_nav_menu_start_el', array ($this, 'update_Growtype_Post_frontend_menu_links'), 10, 4);
        }
    }

    /**
     * @param $output
     * @param $item
     * @param $depth
     * @param $args
     * @return array|string|string[]
     */
    function update_Growtype_Post_frontend_menu_links($output, $item, $depth, $args)
    {
        $output = str_replace('#Growtype_Post_logout_url#', wp_logout_url(home_url()), $output);

        return $output;
    }

    /**
     * Register the stylesheets for the admin area.
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

        wp_enqueue_style($this->Growtype_Post, plugin_dir_url(__FILE__) . 'css/growtype-post-admin.css', array (), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the admin area.
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

        wp_enqueue_script($this->Growtype_Post, plugin_dir_url(__FILE__) . 'js/growtype-post-admin.js', array ('jquery'), $this->version, false);

    }

    /**
     * Register the options page with the Wordpress menu.
     */
    function add_custom_options_page()
    {
        add_options_page(
            'Growtype - Form',
            'Growtype - Form',
            'manage_options',
            'growtype-post-settings',
            array ($this, 'Growtype_Post_settings_form'),
            1
        );
    }

    /**
     * @param $current
     * @return void
     */
    function Growtype_Post_settings_tabs($current = 'login')
    {
        $tabs['login'] = 'Login';
        $tabs['signup'] = 'Signup';
        $tabs['post'] = 'Post';

        if (class_exists('woocommerce')) {
            $tabs['woocommerce'] = 'Woocommerce';
        }

        $tabs['examples'] = 'Examples';

        echo '<div id="icon-themes" class="icon32"><br></div>';
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($tabs as $tab => $name) {
            $class = ($tab == $current) ? ' nav-tab-active' : '';
            echo "<a class='nav-tab$class' href='?page=growtype-post-settings&tab=$tab'>$name</a>";

        }
        echo '</h2>';
    }

    /**
     * @return void
     */
    function Growtype_Post_settings_form()
    {
        if (isset($_GET['page']) && $_GET['page'] == 'growtype-post-settings') {
            ?>

            <div class="wrap">

                <h1>Growtype - Form settings</h1>

                <?php
                if (isset($_GET['updated']) && 'true' == esc_attr($_GET['updated'])) {
                    echo '<div class="updated" ><p>Theme Settings updated.</p></div>';
                }

                if (isset ($_GET['tab'])) {
                    $this->Growtype_Post_settings_tabs($_GET['tab']);
                } else {
                    $this->Growtype_Post_settings_tabs();
                }
                ?>

                <p><b>Json beautifier:</b> <a href="https://jsonbeautify.com/" target="_blank">https://jsonbeautify.com/</a></p>

                <form id="Growtype_Post_main_settings_form" method="post" action="options.php">
                    <?php

                    if (isset ($_GET['tab'])) {
                        $tab = $_GET['tab'];
                    } else {
                        $tab = 'login';
                    }

                    switch ($tab) {
                        case 'login':
                            settings_fields('Growtype_Post_settings_login');

                            echo '<table class="form-table">';
                            do_settings_fields('growtype-post-settings', 'Growtype_Post_settings_login');
                            echo '</table>';

                            break;
                        case 'signup':
                            settings_fields('Growtype_Post_settings_signup');

                            echo '<table class="form-table">';
                            do_settings_fields('growtype-post-settings', 'Growtype_Post_settings_signup');
                            echo '</table>';

                            break;
                        case 'woocommerce' :
                            settings_fields('Growtype_Post_settings_woocommerce');

                            echo '<table class="form-table">';
                            do_settings_fields('growtype-post-settings', 'Growtype_Post_settings_woocommerce');
                            echo '</table>';

                            break;
                        case 'post' :
                            settings_fields('Growtype_Post_settings_post');

                            echo '<table class="form-table">';
                            do_settings_fields('growtype-post-settings', 'Growtype_Post_settings_post');
                            echo '</table>';

                            break;
                        case 'examples' :
                            settings_fields('Growtype_Post_settings_examples');

                            echo '</br></br>';
                            echo '<b>Shortcode:</b> [Growtype_Post name="signup"] ' . "</br>";
                            echo '<b>Allowed input types:</b> ' . implode(',', Growtype_Post_Render::Growtype_Post_ALLOWED_FIELD_TYPES);

                            echo '<table class="form-table">';
                            do_settings_fields('growtype-post-settings', 'Growtype_Post_settings_examples');
                            echo '</table>';

                            break;
                    }

                    if ($tab !== 'examples') {
                        submit_button();
                    }

                    ?>
                </form>

                <script src="<?= plugin_dir_url(__FILE__) ?>plugins/jquery-ace/ace/ace.js"></script>
                <script src="<?= plugin_dir_url(__FILE__) ?>plugins/jquery-ace/ace/theme-twilight.js"></script>
                <script src="<?= plugin_dir_url(__FILE__) ?>plugins/jquery-ace/ace/mode-ruby.js"></script>
                <script src="<?= plugin_dir_url(__FILE__) ?>plugins/jquery-ace/jquery-ace.min.js"></script>

                <script>
                    $ = jQuery;
                    let forms = $('#Growtype_Post_main_settings_form').find('.Growtype_Post_json_content');

                    forms.map(function (index, value) {
                        if ($(value).length > 0) {
                            let editor = $(value).ace({
                                theme: 'twilight',
                                lang: 'ruby'
                            })

                            let Growtype_Post_json_content = $(value).data('ace');
                            if (Growtype_Post_json_content.length > 0) {
                                Growtype_Post_json_content.editor.ace.setValue(JSON.stringify(JSON.parse($(value).text()), null, '\t'));
                            }
                        }
                    });

                    if ($('body').hasClass('settings_page_growtype-post-settings')) {
                        $('#Growtype_Post_main_settings_form input[type="submit"]').click(function () {
                            let forms = $(this).closest('form').find('.Growtype_Post_json_content');
                            forms.map(function (index, value) {
                                if ($(value).data('ace').editor.ace.getValue().length > 0) {
                                    try {
                                        JSON.parse($(value).data('ace').editor.ace.getValue())
                                    } catch (e) {
                                        alert("Caught: " + e.message)
                                        event.preventDefault();
                                    }
                                }
                            });
                        });
                    }
                </script>
            </div>

            <?php
        }
    }

    /**
     * Load the required methods for this plugin.
     *
     */
    private function load_methods()
    {
        /**
         * Load members
         */
        if (!class_exists('WP_List_Table')) {
            require(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
        }
        require_once(ABSPATH . 'wp-admin/includes/class-wp-users-list-table.php');

        require_once GROWTYPE_POST_PATH . 'admin/methods/users/class-growtype-post-signup-details.php';
        require_once GROWTYPE_POST_PATH . 'admin/methods/users/class-growtype-post-signups-list-table.php';

        $this->loader = new Growtype_Post_Signup_Details();
    }
}
