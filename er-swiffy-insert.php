<?php
/*
Plugin Name: ER Swiffy Insert
Plugin URI: http://itekblog.com/wordpress-plugins/er-swiffy-insert/
Description: <strong>ER Swiffy Insert</strong> allows you to use a shortcode to insert animations generated with Google Swiffy. Swiffy converts Flash SWF files to HTML5, allowing you to reuse Flash content on devices without Flash support.
Version: 1.0.0
Author: ER (ET & RaveMaker)
Author URI: http://itekblog.com
License: GPL3
Copyright 2013 ER
*/

/**
 * Main class for the plugin.
 *
 * @since 1.0.0
 *
 * @author  ET & RaveMaker
 */
class ER_Swiffy_Insert {

    static $add_script;
    static $version;

    public function __construct() {

        // define consts.
        define('SWIFFY_INSERT_PLUGIN_MENU_SLUG', 'swiffy-insert');

        // Load our custom assets for admin.
        add_action('admin_enqueue_scripts', array($this, 'admin_assets'));
        add_action('wp_enqueue_scripts', array($this, 'assets'));

        // Create settings page.
        add_action('admin_menu', array($this, 'create_admin_menu'));

        // Add shortcode.
        add_shortcode('swiffy', array($this, 'register_shortcode'));

        // Add head script.
        add_action('wp_head', array($this, 'enqueue_head'));

        // Add footer script
        add_action('wp_footer', array($this, 'enqueue_footer'));
    }

    public function register_shortcode($atts) {
        self::$add_script = true;
        extract(shortcode_atts(array(
                    'n' => '1',
                    'w' => '450',
                    'h' => '300',
                    'v' => '5.0',            
                        ), $atts));
        self::$version = $v;
        return "<div id='swiffycontainer_{$n}' style='width: {$w}px; height: {$h}px;'></div>";
    }

    public function assets() {
        //wp_deregister_script('html5_swiffy_insert_script');
        //wp_register_script('swiffy', 'https://www.gstatic.com/swiffy/v5.0/runtime.js');        
        //wp_enqueue_script('swiffy');
    }

    public function admin_assets() {

        // name of settings page
        $settings = 'settings_page_' . SWIFFY_INSERT_PLUGIN_MENU_SLUG;
        // Bail out early if we are not on a page add/edit screen.
        // First part (before &&) is checks if it is a page. second part to check if this is the settings page.
        if ((!( 'post' == get_current_screen()->base && 'page' == get_current_screen()->id ) ) && (!( $settings == get_current_screen()->base && $settings == get_current_screen()->id ) ))
            return;
    }

    public function enqueue_head() {
        global $post;
        $detenir_bucle = 0;
        $contador = 1;
        do {
            $swiffy_variable = 'swiffy_' . $contador;
            if (get_post_meta($post->ID, $swiffy_variable, true)) {
                echo '<script>swiffyobject_' . $contador . ' = ' . get_post_meta($post->ID, $swiffy_variable, true) . '</script>';
                $contador++;
            } else {
                $detenir_bucle = 1;
            }
        } while ($detenir_bucle == 0);
    }

    public function enqueue_footer() {

        if (!self::$add_script)
            return;

        wp_register_script('swiffy', 'https://www.gstatic.com/swiffy/v'.self::$version.'/runtime.js');
        wp_print_scripts('swiffy');
        global $post;
        $detenir_bucle = 0;
        $contador = 1;
        do {
            $swiffy_variable = 'swiffy_' . $contador;
            $swiffy_container = 'swiffycontainer_' . $contador;
            $swiffy_object = 'swiffyobject_' . $contador;
            if (get_post_meta($post->ID, $swiffy_variable, true)) {
                ?>
                <script>
                    var stage = new swiffy.Stage(document.getElementById('<?php echo $swiffy_container; ?>'), <?php echo $swiffy_object; ?>);
                    stage.start();
                </script>
                <?php
                $contador++;
            } else {
                $detenir_bucle = 1;
            }
        } while ($detenir_bucle == 0);
    }

    public function create_admin_menu() {
        $page_title = 'ER Swiffy Insert';
        $menu_title = $page_title;
        $capability = 'manage_options';
        $menu_slug = SWIFFY_INSERT_PLUGIN_MENU_SLUG;
        $function = array($this, 'create_setting_page');
        add_options_page($page_title, $menu_title, $capability, $menu_slug, $function);
    }

    public function create_setting_page() {
        include("howto.php");
    }

}

// Instantiate the class.
$er_swiffy_insert = new ER_Swiffy_Insert();