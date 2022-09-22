<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    growtype_quiz
 * @subpackage growtype_quiz/admin/partials
 */

trait AdminSettingsGeneralTrait
{
    public function general_content()
    {
        /**
         *
         */
//        register_setting(
//            'growtype_quiz_settings_general', // settings group name
//            'growtype_quiz_custom_post_type_label_name' // option name
//        );
//
//        add_settings_field(
//            'growtype_quiz_custom_post_type_label_name',
//            'Label Name (default: Quizes)',
//            array ($this, 'growtype_quiz_custom_post_type_label_name_callback'),
//            'growtype-quiz-settings',
//            'growtype_quiz_settings_general'
//        );
    }

    /**
     *
     */
    function growtype_quiz_custom_post_type_label_name_callback()
    {
        $value = get_option('growtype_quiz_custom_post_type_label_name');
        ?>
        <input type="text" name="growtype_quiz_custom_post_type_label_name" value="<?php echo $value ?>"/>
        <?php
    }
}


