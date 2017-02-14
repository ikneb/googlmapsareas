<?php

/**
 * Created by PhpStorm.
 * User: junta
 * Date: 2/13/17
 * Time: 1:14 PM
 */
class Marker
{
    public $id_marker;
    public $id_map_post;
    public $name;
    public $coordinates;
    public $icon;
    public $label_text;
    public $window_text;
    public $animate;


    public function save()
    {
        global $wpdb;

        $id_marker = $this->id_marker ? $this->id_marker : 0;
        $isset = $wpdb->get_row("SELECT * FROM " .
            $wpdb->prefix . "googlmapsareas WHERE
        id_marker =" . $id_marker . " AND id_map_post = " . $this->id_map_post);

        if (isset($isset)) {
            $wpdb->update(
                $wpdb->prefix . "googlmapsareas", array(
                'id_marker' => $id_marker,
                'id_map_post' => $this->id_map_post,
                'coordinates' => $this->coordinates,
                'name' => $this->name,
                'icon' => $this->icon,
                'label_text' => $this->label_text,
                'window_text' => $this->window_text,
                'animate' => $this->animate,
            ),
                array('id_marker' => $id_marker,
                    'id_map_post' => $this->id_map_post)
            );
            return true;
        } else {
            $wpdb->insert(
                $wpdb->prefix . "googlmapsareas", array(
                    'id_marker' => $id_marker,
                    'id_map_post' => $this->id_map_post,
                    'coordinates' => $this->coordinates,
                    'name' => $this->name,
                    'icon' => $this->icon,
                    'label_text' => $this->label_text,
                    'window_text' => $this->window_text,
                    'animate' => $this->animate,
                )
            );
            return true;
        }
        return false;
    }


    public static function getMarkerByPostId($id_post)
    {
        global $wpdb;

        $markers = $wpdb->get_results("SELECT * FROM " .
            $wpdb->prefix . "googlmapsareas WHERE id_map_post = " . $id_post);

        return $markers;
    }

    public static function getAllDefaultIcon(){

        $all_ikon = scandir(plugin_dir_path(__FILE__) . '../img/marker-icon');
        $array = array_flip($all_ikon);
        unset($array['.']);
        unset($array['..']);
        $all_ikon = array_flip($array);
        $type = array('.png', '.svg', '.jpg', '.icon');
        for ($i = 2; $i < count($all_ikon) + 2; $i++) {
            if ($all_ikon[$i]) {
                $all_ikon[$i] = str_replace($type, '', $all_ikon[$i]);
            }
        }
        return $all_ikon;
    }


    public static function remove($id_marker, $id_post_map){
        global $wpdb;

        $wpdb->delete( $wpdb->prefix . 'googlmapsareas',
            array(
                'id_marker' => $id_marker,
                'id_map_post' => $id_post_map
            ) );
        return true;
    }
}