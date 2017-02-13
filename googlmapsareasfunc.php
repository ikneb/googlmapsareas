<?php

/*
Plugin Name: Google Maps Areas
Plugin URI: http://
Description: Google Maps Areas plugin.
Version:  1.0
Author: Weeteam
Author URI: http://
*/


class GooglMapsAreasFunc
{

    function init()
    {
        // Actions and filters
        add_action('init', array($this, 'create_post_type'));
        add_action('edit_form_advanced', array($this, 'my_add_to_map'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('init', array($this, 'reset_editor'));
        add_shortcode('map', array($this, 'shortcode_func'));
        add_action('wp_ajax_get_all_marker', 'get_all_marker');
        add_action( 'wp_ajax_nopriv_get_all_marker', 'get_all_marker' );
        register_activation_hook( __FILE__, 'googlmapsareas_activate' );
        register_uninstall_hook( __FILE__, 'googlmapsareas_uninstall' );

    }

    function googlmapsareas_activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . "googlmapsareas";
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
              id_marker INT (9) NOT NULL AUTO_INCREMENT,
              id_map_post INT (9) NOT NULL,
              name VARCHAR NOT NULL,
              icon VARCHAR NOT NULL,
              label_text VARCHAR NOT NULL,
              window_text text NOT NULL,
              animate text NOT NULL,
              PRIMARY KEY  (id_marker)
        ) $charset_collate;";

        $wpdb->query($sql);

        /*require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );*/
    }

    function googlmapsareas_uninstall(){

        global $wpdb;
        $table_name = $wpdb->prefix . "googlmapsareas";
        $sql = "DROP TABLE IF EXISTS $table_name;";
        $wpdb->query($sql);
    }


    function admin_enqueue_scripts()
    {
        global $post;
        if (isset($post) && $post->post_type == 'wt_maps') {
            wp_enqueue_style('goglemapsareas-all', plugin_dir_url(__FILE__) . 'css/all.css');
            wp_enqueue_script('goglemapsareas-plugins', plugin_dir_url(__FILE__) . 'js/plugin.js');
        }

        wp_enqueue_script('goglemapsareas-init-js', plugin_dir_url(__FILE__) . 'js/googlMap.js');
        wp_localize_script( 'goglemapsareas-init-js', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );

    }


    function create_post_type()
    {
        register_post_type('wt_maps',
            array(
                'labels' => array(
                    'name' => __('Google Maps'),
                    'singular_name' => __('Map')
                ),
                'public' => true,
                'has_archive' => true,
                'menu_position' => 100
            )
        );
    }


    function my_add_to_map()
    {
        global $post; //    var_dump($post);exit;
        echo '<p class="map__shortcode">Use shrtcode for render map <strong>[map id=' . $post->ID . ']</strong></p>';
        if ($post->post_type == 'wt_maps') {
            ?>
            <div class="map__wrapper" data-postid = '. $post->ID . '>
                <script
                    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCByLB65AuBykaj_lFmuiZWrIRYXvLLvJI&callback=initMap"
                    async defer>
                </script>
                <div id="map"></div>
            </div>
            <div class="marker__wrapper">
                <!--                <a class="marker__add">+ Add marker</a>-->
                <ul id="marker__list">

                </ul>
            </div>
            <?php
        }
    }


    function reset_editor()
    {
        global $post;
        global $_wp_post_type_features;

        $post_type = 'wt_maps';
        $feature = 'editor';
        if (!isset($_wp_post_type_features[$post_type])) {

        } elseif (isset($_wp_post_type_features[$post_type][$feature]))
            unset($_wp_post_type_features[$post_type][$feature]);

    }

    function shortcode_func($attr)
    {

        $result =
            '<div class="map__wrapper">
                <script
                    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCByLB65AuBykaj_lFmuiZWrIRYXvLLvJI&callback=initMap"
                    async defer>
                </script>
                <div id="map" style="width:100%;height:400px;"></div>
                <script>
                    function initMap() {
                        // Create a map object and specify the DOM element for display.
                        var map = new google.maps.Map(document.getElementById("map"), {
                            center: {lat: 49.994783, lng: 36.1430755},
                            scrollwheel: false,
                            zoom: 10
                        });
                    }
                </script>
            </div>';

        return $result;
    }

    function get_all_marker()
    {
        $all_ikon = json_encode(scandir(plugin_dir_path(__FILE__) . 'img/marker-icon'));
       echo 'test';
        wp_die();
    }
}

global $google_maps_areas;
$google_maps_areas = new GooglMapsAreasFunc();
$google_maps_areas->init();