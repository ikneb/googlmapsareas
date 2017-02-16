<?php

/**
 * Model for work with markers
 */
class Polylines
{
    public $id;
    public $id_map_post;
    public $name;
    public $coordinates;
    public $color;
    public $thick;


    /**
     * Insert if not exist or update polylines
     *
     * @return boolean
     */
    public function save()
    {
        global $wpdb;

        if (!empty($this->id)) {
            $wpdb->update(
                $wpdb->prefix . "polylines", array(
                'id_map_post' => $this->id_map_post,
                'coordinates' => $this->coordinates,
                'name' => $this->name,
                'color' => $this->color,
                'thick' => $this->thick,
            ),
                array(
                    'id' => $this->id,
                    'id_map_post' => $this->id_map_post,
                )
            );
            return true;
        } else {
            $wpdb->insert(
                $wpdb->prefix . "polylines", array(
                    'id_map_post' => $this->id_map_post,
                    'coordinates' => $this->coordinates,
                    'name' => $this->name,
                    'color' => $this->color,
                    'thick' => $this->thick,
                )
            );
            $lastid = $wpdb->insert_id;
            return $lastid;
        }
        return false;
    }

    public function remove()
    {

    }

    public static function  getAllByPostId($id_post)
    {
        global $wpdb;

        $polylines = $wpdb->get_results("SELECT * FROM " .
            $wpdb->prefix . "polylines WHERE id_map_post = " . $id_post);

        return $polylines;
    }

}