<?php
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');
require_once('classes/Marker.php');
require_once('classes/Polylines.php');

if(!empty($_POST['action'])) {
    switch ($_POST['action']) {

        case 'get_all_markers':

            $all_ikon = Marker::getAllDefaultIcon();
            echo json_encode($all_ikon);
            break;

        case 'save_marker':

            $marker = new Marker();
            $marker->id_marker = $_POST['id_marker'] ? $_POST['id_marker'] : '';
            $marker->id_map_post = $_POST['id_map_post'] ? $_POST['id_map_post'] : '';
            $marker->coordinates = $_POST['coordinates'] ? $_POST['coordinates'] : '';
            $marker->name = $_POST['name'] ? $_POST['name'] : '';
            $marker->icon = $_POST['icon'] ? $_POST['icon'] : '';
            $marker->method = $_POST['method'] ? $_POST['method'] : '';
            $marker->label_text = $_POST['label_text'] ? $_POST['label_text'] : '';
            $marker->window_text = $_POST['window_text'] ? $_POST['window_text'] : '';
            $marker->animate = $_POST['animate'] ? $_POST['animate'] : '';
            $marker->link = $_POST['link'] ? $_POST['link'] : '';
            $marker->script = $_POST['script'] ? $_POST['script'] : '';
            $marker->attachment_id = $_POST['attachment_id'] ? $_POST['attachment_id'] : '';
            echo $marker->save();
            break;

        case 'remove_marker':
            echo Marker::remove($_POST['id_marker'], $_POST['id_map_post']);
            break;
        case 'get_img':
            if($_POST['attachment_id']){
                $img = wp_get_attachment_image_src($_POST['attachment_id'], 'large');
                echo $img[0];
            }
            break;

        case 'center-map':
            global $wpdb;
            if(!empty($_POST['coord']) && !empty($_POST['id_post'])){
                $wpdb->insert(
                    $wpdb->prefix . "postmeta", array(
                        'post_id' => $_POST['id_post'],
                        'meta_key' => 'coordinate',
                        'meta_value' => $_POST['coord']
                    )
                );
            }
            if(!empty($_POST['zoom']) && !empty($_POST['id_post'])) {
                $wpdb->insert(
                    $wpdb->prefix . "postmeta", array(
                        'post_id' => $_POST['id_post'],
                        'meta_key' => 'zoom',
                        'meta_value' => $_POST['zoom']
                    )
                );
            }

            break;

        case 'center-marker':
            global $wpdb;
            if(!empty($_POST['id_post'])){
                $wpdb->delete( $wpdb->prefix . "postmeta",
                    array(
                        'post_id' => $_POST['id_post'],
                        'meta_key' => 'zoom'
                    ) );
                $wpdb->delete( $wpdb->prefix . "postmeta",
                    array(
                        'post_id' => $_POST['id_post'],
                        'meta_key' => 'coordinate'
                    ) );
            }

            break;
        case 'save_polyline':

                $polyline = new Polylines();
                $polyline->id = $_POST['id'] ? $_POST['id'] : '';
                $polyline->id_map_post = $_POST['id_post'] ? $_POST['id_post'] : '';
                $polyline->name = $_POST['name'] ? $_POST['name'] : '';
                $polyline->color = $_POST['color'] ? $_POST['color'] : '';
                $polyline->thick = $_POST['thick'] ? $_POST['thick'] : '';
                $polyline->coordinates = $_POST['coordinate'] ? $_POST['coordinate'] : '';
                echo $polyline->save();
            break;

        case 'remove_polyline':
            if(isset($_POST['id']))
            echo Polylines::remove($_POST['id']);
            break;
    }
}
