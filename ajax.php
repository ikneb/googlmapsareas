<?php
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');


switch ($_POST['action']) {

    case 'get_all_markers':

        $all_ikon = scandir(plugin_dir_path(__FILE__) . 'img/marker-icon');
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

        echo json_encode($all_ikon);
        break;

    case 'save_marker':
        global $wpdb;
        break;
}

