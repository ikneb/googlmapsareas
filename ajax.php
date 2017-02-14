<?php
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');
require_once('classes/Marker.php');


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
            $marker->label_text = $_POST['label_text'] ? $_POST['label_text'] : '';
            $marker->window_text = $_POST['window_text'] ? $_POST['window_text'] : '';
            $marker->animate = $_POST['animate'] ? $_POST['animate'] : '';
            echo $marker->save();
            break;

        case 'remove_marker':
            echo Marker::remove($_POST['id_marker'], $_POST['id_map_post']);
            break;
    }
}


if(!empty($_FILES['img'])){
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
}