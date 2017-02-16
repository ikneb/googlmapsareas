<?php

/**
 * Model for work with markers
 */
class Marker
{
    public $id_marker;
    public $id_map_post;
    public $name;
    public $coordinates;
    public $icon;
    public $method;
    public $label_text;
    public $window_text;
    public $animate;
    public $link;
    public $script;
    public $attachment_id;


    /**
     * Insert if not exist or update markers
     *
     * @return boolean
     */
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
                'method' => $this->method,
                'label_text' => $this->label_text,
                'window_text' => $this->window_text,
                'animate' => $this->animate,
                'link' => $this->link,
                'script' => $this->script,
                'attachment_id' => $this->attachment_id,
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
                    'method' => $this->method,
                    'label_text' => $this->label_text,
                    'window_text' => $this->window_text,
                    'animate' => $this->animate,
                    'link' => $this->link,
                    'script' => $this->script,
                    'attachment_id' => $this->attachment_id,
                )
            );
            return true;
        }
        return false;
    }


    /**
     * Get all markers by post id.
     *
     * @param int  $id_post Id current post.
     * @return array $markers All marker that was saved for this post.
     */
    public static function getMarkerByPostId($id_post)
    {
        global $wpdb;

        $markers = $wpdb->get_results("SELECT * FROM " .
            $wpdb->prefix . "googlmapsareas WHERE id_map_post = " . $id_post);

        return $markers;
    }

    /**
     * Get all name default icon.
     *
     * @return array $all_icon All icon name in folder img.
     */
    public static function getAllDefaultIcon()
    {

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

    /**
     * Remove single marker by id
     *
     * @param int $id_marker
     * @param int $id_post_map
     * @return boolean
     */
    public static function remove($id_marker, $id_post_map)
    {
        global $wpdb;

        $wpdb->delete($wpdb->prefix . 'googlmapsareas',
            array(
                'id_marker' => $id_marker,
                'id_map_post' => $id_post_map
            ));
        return true;
    }
}
