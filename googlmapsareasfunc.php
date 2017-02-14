<?php
/*
Plugin Name: Google Maps Areas
Plugin URI: http://
Description: Google Maps Areas plugin.
Version:  1.0
Author: Weeteam
Author URI: http://
*/

require_once('classes/Marker.php');


class GooglMapsAreasFunc
{

    function __construct()
    {
        register_activation_hook(__FILE__, array('GooglMapsAreasFunc', 'install'));
        register_uninstall_hook(__FILE__, array('GooglMapsAreasFunc', 'uninstall'));
    }

    function init()
    {
        // Actions and filters
        add_action('init', array($this, 'create_post_type'));
        add_action('edit_form_advanced', array($this, 'my_add_to_map'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('init', array($this, 'reset_editor'));
        add_shortcode('map', array($this, 'shortcode_func'));
        add_action('wp_ajax_get_all_marker', 'get_all_marker');
        add_action('wp_ajax_nopriv_get_all_marker', 'get_all_marker');
    }


    function install()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "googlmapsareas";
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
              id INT(9) NOT NULL AUTO_INCREMENT,
              id_marker INT(9) NOT NULL,
              id_map_post INT(9) NOT NULL,
              coordinates varchar(255) NOT NULL default '',
              name varchar(255) NOT NULL default '',
              icon varchar(255) NOT NULL default '',
              label_text varchar(255) NOT NULL default '',
              window_text varchar(255) NOT NULL default '',
              animate varchar(255) NOT NULL default '',
              PRIMARY KEY (id)
        ) $charset_collate";


        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    function uninstall()
    {

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
        wp_localize_script('goglemapsareas-init-js', 'ajax_object',
            array('ajax_url' => admin_url('admin-ajax.php'), 'we_value' => 1234));
        wp_enqueue_media();

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
        global $post;
        echo '<p class="map__shortcode">Use shrtcode for render map <strong>[map id=' .
            $post->ID . ']</strong></p>';
        if ($post->post_type == 'wt_maps') {
            ?>

            <div class="map__wrapper">
                <script
                    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCByLB65AuBykaj_lFmuiZWrIRYXvLLvJI&callback=initMap"
                    async defer>
                </script>
                <div id="map"></div>
            </div>
            <div class="marker__wrapper" data-postid="<?php echo $post->ID; ?>">
                <ul id="marker__list">
                    <?php
                    $markers = Marker::getMarkerByPostId($post->ID);
                    $all_icon = Marker::getAllDefaultIcon();
                    if (!empty($markers)) {
                        foreach ($markers as $marker) {
                            ?>
                            <li>
                                <button class="marker__remove button button-primary">Delete field</button>
                                <button class="marker__save button button-primary">Save</button>
                                <div class="marker__group-name" data-c="<?php echo $marker->coordinates; ?>"
                                     data-id="<?php echo $marker->id_marker; ?>">
                                    <label class="marker__lable-name">Name</label>
                                    <input name="name" class="marker__input" value="<?php echo $marker->name; ?>">
                                </div>
                                <div class="marker__group-icon">
                                    <lable class="marker__label-select">Icon</lable>
                                    <select class="marker__select-method" >
                                        <option value="0">Choose</option>
                                        <option value="1">Select icon</option>
                                        <option value="2">Download icon</option>
                                    </select>
                                    <button type="button" name="my_icon" class="my_icon btn btn-primary no-active upload_image_button"></button>
                                    <select class="marker__select no-active" >
                                        <option value="default">Default</option>
                                        <?php foreach ($all_icon as $icon) {
                                            $icon_selected = $icon . '.png';
                                            ?>
                                            <option
                                                value="<?php echo $icon ?>.png" <?php if ($icon_selected == $marker->icon) echo 'selected'; ?>>
                                                <?php echo $icon ?></option>
                                        <?php } ?>
                                    </select>

                                </div>
                                <div class="marker__group-icon">
                                    <lable class="marker__label-action">Action</lable>
                                    <select class="marker__select-action">
                                        <option value="0">Add</option>
                                        <option value="1">Title</option>
                                        <option value="2">Info windows</option>
                                        <option value="3">Animate effect</option>
                                    </select>
                                </div>
                                <?php
                                if (!empty($marker->label_text)) { ?>
                                    <div class="marker__group-title">
                                        <img src="/wp-content/plugins/googlmapsareas/img/61391.png"></img>
                                        <lable class="marker__label-title">Lable for icon</lable>
                                        <textarea name="label"
                                                  class="marker__label-text"><?php echo $marker->label_text; ?></textarea>
                                    </div>
                                <?php }
                                if (!empty($marker->window_text)) { ?>
                                    <div class="marker__group-windows">
                                        <img src="/wp-content/plugins/googlmapsareas/img/61391.png"></img>
                                        <lable class="marker__label-title">Window info for icon</lable>
                                        <textarea name="window"
                                                  class="marker__windows-text"><?php echo $marker->window_text; ?></textarea>
                                    </div>
                                <?php }
                                if (!empty($marker->animate)) { ?>
                                    <div class="marker__group-animate">
                                        <img src="/wp-content/plugins/googlmapsareas/img/61391.png"></img>
                                        <lable class="marker__label-title">Animate effect</lable>
                                        <select class="marker__select-animate">
                                            <option value="0">Select animation effect</option>
                                            <option
                                                value="BOUNCE" <?php if ($marker->animate == 'BOUNCE') echo 'selected' ?>>
                                                BOUNCE
                                            </option>
                                            <option
                                                value="DROP" <?php if ($marker->animate == 'DROP') echo 'selected' ?>>
                                                DROP
                                            </option>
                                        </select>
                                    </div>
                                <?php } ?>
                            </li>
                            <script>
                                jQuery(window).load(function () {
                                    <?php $coord = explode( ',', $marker->coordinates );?>
                                    var marker = new google.maps.Marker({
                                        position: {lat: <?php echo $coord[0]; ?>, lng:<?php echo $coord[1]; ?>},
                                        label: labels[labelIndex++ % labels.length],
                                        map: map,
                                    });
                                    <?php if($marker->icon != 'default'){ ?>
                                    img = '<?php echo $marker->icon; ?>';
                                    marker.setIcon('/wp-content/plugins/googlmapsareas/img/marker-icon/' + img);
                                    <?php }
                                        if(!empty($marker->label_text)){ ?>
                                    marker.setTitle('<?php echo $marker->label_text; ?>');
                                    <?php }
                                    if(!empty($marker->window_text)){?>
                                    var contentString = '<div id="content"><?php echo $marker->window_text;?></div>';
                                    google.maps.event.clearListeners(marker, 'click');
                                    var infowindow = new google.maps.InfoWindow({
                                        content: contentString,
                                        maxWidth: 200
                                    });
                                    google.maps.event.addListener(marker, 'click', function () {
                                        infowindow.open(map, marker);
                                    });
                                    <?php }
                                        if(!empty($marker->animate)){ ?>
                                    marker.addListener('click', function () {
                                        marker.setAnimation(google.maps.Animation.<?php echo $marker->animate;?>);
                                    });
                                    <?php }
                                ?>
                                    markers[<?php echo $marker->id_marker?>] = marker;
                                    LatLngList.push(  new google.maps.LatLng (<?php echo $marker->coordinates; ?>) );
                                });
                            </script>
                        <?php }?>
                        <script>
                            jQuery(window).load(function () {
                                var latlngbounds = new google.maps.LatLngBounds();
                                LatLngList.forEach(function(latLng){
                                    latlngbounds.extend(latLng);
                                });
                                map.setCenter(latlngbounds.getCenter());
                                map.fitBounds(latlngbounds);
                            });
                        </script>
                    <?php }?>
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
        $markers = Marker::getMarkerByPostId($attr['id']);
        $markers = json_encode($markers);
        $result =
            '<div class="map__wrapper">
                <script
                    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCByLB65AuBykaj_lFmuiZWrIRYXvLLvJI&callback=initMap"
                    async defer>
                </script>
                <div id="map" style="width:100%;height:400px;"></div>
            </div>';

        wp_enqueue_script('goglemapsareas-front-map', plugin_dir_url(__FILE__) . 'js/frontMap.js');
        wp_localize_script('goglemapsareas-front-map', 'markers_object', $markers);

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
?>