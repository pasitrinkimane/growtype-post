<?php

class Growtype_Post_Admin_Settings
{
    public function __construct()
    {
        $this->load_tabs();

        add_action('admin_menu', array ($this, 'admin_menu_pages'));

        add_action('init', array ($this, 'process_posted_data'));
    }

    /**
     * Register the options page with the Wordpress menu.
     */
    function admin_menu_pages()
    {
        add_options_page(
            __('Growtype - Post', 'growtype-post'),
            __('Growtype - Post', 'growtype-post'),
            'manage_options',
            Growtype_Post_Admin::SETTINGS_PAGE_SLUG,
            array ($this, 'options_page_content'),
            1
        );
    }

    function options_page_content()
    {
        if (isset($_GET['page']) && $_GET['page'] === Growtype_Post_Admin::SETTINGS_PAGE_SLUG) { ?>

            <div class="wrap">

                <h1>Growtype Post - Settings</h1>

                <?php
                if (isset($_GET['updated']) && 'true' == esc_attr($_GET['updated'])) {
                    echo '<div class="updated" ><p>Settings updated.</p></div>';
                }

                if (isset ($_GET['tab'])) {
                    $this->render_settings_tab_render($_GET['tab']);
                } else {
                    $this->render_settings_tab_render();
                }
                ?>

                <form id="growtype_post_settings_form" method="post" action="options.php">
                    <?php

                    if (isset ($_GET['tab'])) {
                        $tab = $_GET['tab'];
                    } else {
                        $tab = Growtype_Post_Admin::SETTINGS_DEFAULT_TAB;
                    }

                    switch ($tab) {
                        case 'general':
                            settings_fields('growtype_post_settings_general');

                            echo '<table class="form-table">';
                            do_settings_fields(Growtype_Post_Admin::SETTINGS_PAGE_SLUG, 'growtype_post_settings_general_render');
                            echo '</table>';

                            break;
                        case 'social':
                            settings_fields('growtype_post_settings_social');

                            echo '<table class="form-table">';
                            do_settings_sections('growtype_form_settings_social_reddit_section');
                            do_settings_sections('growtype_form_settings_social_google_section');
                            do_settings_sections('growtype_form_settings_social_medium_section');
                            echo '</table>';

                            break;
                        case 'content':
                            settings_fields('growtype_post_settings_content');

                            echo '<table class="form-table">';
                            do_settings_sections('growtype_post_settings_content_openai_section');
                            echo '</table>';

                            break;
                    }

                    submit_button();

                    ?>
                </form>
            </div>

            <?php
        }
    }

    function process_posted_data()
    {

    }

    function settings_tabs()
    {
        return apply_filters('growtype_post_admin_settings_tabs', []);
    }

    function render_settings_tab_render($current = Growtype_Post_Admin::SETTINGS_DEFAULT_TAB)
    {
        $tabs = $this->settings_tabs();

        echo '<div id="icon-themes" class="icon32"><br></div>';
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($tabs as $tab => $name) {
            $class = ($tab == $current) ? ' nav-tab-active' : '';
            echo "<a class='nav-tab$class' href='?page=" . Growtype_Post_Admin::SETTINGS_PAGE_SLUG . "&tab=$tab'>$name</a>";

        }
        echo '</h2>';
    }

    public function load_tabs()
    {
        /**
         * General
         */
        include_once GROWTYPE_POST_PATH . 'admin/pages/settings/tabs/growtype-post-admin-settings-general.php';
        new Growtype_Post_Admin_Settings_General();

        /**
         * Social
         */
        include_once GROWTYPE_POST_PATH . 'admin/pages/settings/tabs/growtype-post-admin-settings-social.php';
        new Growtype_Post_Admin_Settings_Social();

        /**
         * Social
         */
        include_once GROWTYPE_POST_PATH . 'admin/pages/settings/tabs/growtype-post-admin-settings-content.php';
        new Growtype_Post_Admin_Settings_Content();
    }
}
