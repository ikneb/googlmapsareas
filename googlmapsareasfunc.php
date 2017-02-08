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
        add_action('edit_form_advanced', array($this,'my_add_to_core'));
        add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }

    function admin_enqueue_scripts(){
        global $post;
        if (isset($post) && $post->post_type == 'wt_maps') {
            wp_enqueue_style('goglemapsareas-all', plugin_dir_url(__FILE__) . 'css/all.css');
            wp_enqueue_script('goglemapsareas-plugins', plugin_dir_url(__FILE__) . 'js/plugin.js');
        }
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

    function my_add_to_core() {
        global $post;
        if ($post->post_type == 'wt_maps') {
            ?>
            <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCByLB65AuBykaj_lFmuiZWrIRYXvLLvJI&callback=initMap"
                    async defer></script>
            <div id="map"></div>
            <script>
                function initMap() {
                    // Create a map object and specify the DOM element for display.
                    var map = new google.maps.Map(document.getElementById('map'), {
                        center: {lat: 49.994783, lng: 36.1430755},
                        scrollwheel: false,
                        zoom: 10
                    });
                }

            </script>

            <?php
        }
    }
}

global $google_maps_areas;
$google_maps_areas = new GooglMapsAreasFunc();
$google_maps_areas->init();