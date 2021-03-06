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
require_once('classes/Polylines.php');

class GooglMapsAreasFunc
{

    /**
     * We setup action for install and uninstall plugin
     */
    function __construct()
    {
        register_activation_hook(__FILE__, array('GooglMapsAreasFunc', 'install'));
        register_uninstall_hook(__FILE__, array('GooglMapsAreasFunc', 'uninstall'));
    }

    /**
     * Init is where we setup actions and filters
     */
    function init()
    {
        add_action('init', array($this, 'create_post_type'));
        add_action('edit_form_advanced', array($this, 'my_add_to_map'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('init', array($this, 'reset_editor'));
        add_shortcode('map', array($this, 'shortcode_func'));
        add_action('wp_ajax_get_all_marker', 'get_all_marker');
        add_action('wp_ajax_nopriv_get_all_marker', 'get_all_marker');
    }

    /**
     * Create table in db for save markers
     */
    function install()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "googlmapsareas";
        $charset_collate = $wpdb->get_charset_collate();
        $sql_googlmapsareas = "CREATE TABLE IF NOT EXISTS $table_name (
              id INT(11) NOT NULL AUTO_INCREMENT,
              id_marker INT(11) NOT NULL,
              id_map_post INT(11) NOT NULL,
              coordinates varchar(100) NOT NULL default '',
              name varchar(100) NOT NULL default '',
              icon varchar(50) NOT NULL default '',
              method varchar(255) NOT NULL default '',
              label_text varchar(255) NOT NULL default '',
              window_text varchar(255) NOT NULL default '',
              animate varchar(50) NOT NULL default '',
              link varchar(255) NOT NULL default '',
              script varchar(255) NOT NULL default '',
              attachment_id INT(11) NOT NULL,
              PRIMARY KEY (id)
        ) $charset_collate";

        $table_name = $wpdb->prefix . "polylines";
        $sql_polylines = "CREATE TABLE IF NOT EXISTS $table_name (
              id INT(11) NOT NULL AUTO_INCREMENT,
              id_map_post INT(11) NOT NULL,
              name varchar(100) NOT NULL default '',
              coordinates text NOT NULL default '',
              color varchar(255) NOT NULL default '',
              thick varchar(255) NOT NULL default '',
              PRIMARY KEY (id)
        ) $charset_collate";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_googlmapsareas);
        dbDelta($sql_polylines);
    }

    /**
     * Remove table in db
     */
    function uninstall()
    {

        global $wpdb;
        $table_name = $wpdb->prefix . "googlmapsareas";
        $sql = "DROP TABLE IF EXISTS $table_name;";
        $wpdb->query($sql);
    }

    /**
     * Include script
     */
    function admin_enqueue_scripts()
    {
        global $post;
        wp_enqueue_media();
        if (isset($post) && $post->post_type == 'wt_maps') {
            wp_enqueue_style('goglemapsareas-all', plugin_dir_url(__FILE__) . 'css/all.css');
        }

        wp_enqueue_script('goglemapsareas-init-js', plugin_dir_url(__FILE__) . 'js/googlMap.js');
        wp_localize_script('goglemapsareas-init-js', 'ajax_object',
            array('ajax_url' => admin_url('admin-ajax.php'), 'we_value' => 1234));


    }

    /**
     * Create custom post type
     */
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

    /**
     * Add map, if is exist, marker on the page post type.
     * If marker have action, we add it in this method
     */
    function my_add_to_map()
    {
        global $post;
        echo '<p class="map__shortcode">Use shortcode for render map <strong>[map id=' .
            $post->ID . ']</strong>.
             You can resize map, if you add "width" and "height" attribute([map id=' .
            $post->ID . ' width=100% height=500]).</p>';
        if ($post->post_type == 'wt_maps') {
            ?>

            <div class="map__wrapper">
                <script
                    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCByLB65AuBykaj_lFmuiZWrIRYXvLLvJI&callback=initMap"
                    async defer>
                </script>
                <div id="map"></div>
            </div>
            <?php
            $coord = get_post_meta($post->ID, 'coordinate', true);
            $zoom = get_post_meta($post->ID, 'zoom', true);
            $coordinates = explode(',', $coord);
            ?>
            <div class="center-map" data-id="<?php echo $post->ID; ?>">
                <input type="radio" name="group1" value="1" <?php if (empty($coord)) echo 'checked'; ?> checked
                       class="center-marker"> Locate the center between the markers<br/>
                <input type="radio" name="group1" value="1"
                       class="current" <?php if (!empty($coord)) echo 'checked'; ?>>
                Locate the center at the current position(Position save on the moment switch radio button)<br/>
            </div>
            <div class="polylines__wrapper" data-id="<?php echo $post->ID; ?>">
                <h2>Polylines</h2>
                <button class="polylines-add button button-default">Add Polylines</button>
                <!--<button class="polylines-save button button-default">Save Polylines</button>
                <button class="polylines-remove button button-default">Remove Polylines</button>-->
                <ul id="polyline__add">
                    <?php
                    $polylines = Polylines::getAllByPostId($post->ID);
                    if (!empty($polylines)) {
                        foreach ($polylines as $poly) {
                            ?>
                            <li data-id="<?php echo $poly->id; ?>" data-cord="<?php echo $poly->coordinates; ?>">
                                <button class="polylines__remove button button-primary">Delete polyline</button>
                            <button class="polylines__save button button-primary">Save</button>
                            <div class="polylines__group">
                            <label class="polylines__lable">Name</label>
                            <input name="name" class="marker__input" value="<?php echo $poly->name; ?>"> </div>
                            <div class="polylines__group">
                                <lable class="polylines__lable">Color</lable>
                                <select class="polylines__select-color">
                                    <option value="#000000" <?php if($poly->color == '#000000') echo 'selected';?>>Black</option>
                                    <option value="#0000FF" <?php if($poly->color == '#0000FF') echo 'selected';?>>Blue</option>
                                    <option value="#FF0000" <?php if($poly->color == '#FF0000') echo 'selected';?>>Red</option>
                                </select></div>
                            <div class="polylines__group">
                                <lable class="polylines__lable">Thickness of the line(px)</lable>
                                <input class="polylines__select-thick" type="number" name="thick" value="<?php echo $poly->thick; ?>">
                            </div></li>
                            <script>
                                jQuery(window).load(function () {
                                    var flightPlanCoordinates = [
                                        <?php echo $poly->coordinates; ?>
                                    ];
                                    poly = new google.maps.Polyline({
                                        path: flightPlanCoordinates,
                                        geodesic: true,
                                        strokeColor: '<?php echo $poly->color; ?>',
                                        strokeOpacity: 1.0,
                                        strokeWeight: <?php echo $poly->thick; ?>
                                    });

                                    poly.setMap(map);
                                    polies[<?php echo $poly->id?>] = poly;
                                });
                            </script>
                        <?php }
                    }
                    ?>

                </ul>
            </div>
            <div class="marker__wrapper" data-postid="<?php echo $post->ID; ?>">
                <h2>Markers</h2>
                <ul id="marker__list">
                    <?php

                    $markers = Marker::getMarkerByPostId($post->ID);
                    $all_icon = Marker::getAllDefaultIcon();
                    $default = plugin_dir_url(__FILE__) . 'img/default.png';
                    if (!empty($markers)) {
                    foreach ($markers as $marker) {
                        ?>
                        <li>
                            <button class="marker__remove button button-primary">Delete marker</button>
                            <button class="marker__save button button-primary">Save</button>
                            <div class="marker__group-name" data-c="<?php echo $marker->coordinates; ?>"
                                 data-id="<?php echo $marker->id_marker; ?>">
                                <label class="marker__lable-name">Name</label>
                                <input name="name" class="marker__input" value="<?php echo $marker->name; ?>">
                            </div>
                            <div class="marker__group-icon">
                                <lable class="marker__label-select">Icon</lable>
                                <select class="marker__select-method">
                                    <option value="0">Choose</option>
                                    <option value="1">Select icon</option>
                                    <option value="2">Upload icon</option>
                                </select>
                            </div>
                            <div class=" marker__group-icon <?php
                            if ($marker->method != 2 && $marker->attachment_id == 0) {
                                echo 'no-active';
                            } ?> marker__download">
                                <?php
                                if (!empty($marker->attachment_id)) {
                                    $default = wp_get_attachment_image_src($marker->attachment_id, 'large');
                                    $default = $default[0];
                                }
                                ?>
                                <div class="marker__download-icon" data-id="<?php echo $marker->id_marker; ?>"
                                     data-src="<?php echo $marker->attachment_id; ?>">
                                    <img src="<?php echo $default; ?>" width="116px"
                                         height="116px"/>

                                    <div>
                                        <button type="submit" class="upload_image_button button">Загрузить</button>
                                        <button type="submit" class="remove_image_button button">&times;</button>
                                    </div>
                                </div>
                            </div>
                            <div
                                class=" marker__group-icon <?php if ($marker->icon == 'default' || $marker->method == 2) echo 'no-active' ?> marker__select">
                                <lable class="marker__label-select">Select icon</lable>
                                <select class="marker__select-icon">
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
                                    <option value="4">Link</option>
                                    <option value="5">Custom script</option>
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
                            <?php }
                            if (!empty($marker->link)) { ?>
                                <div class="marker__group-link isset-click">
                                    <img src="/wp-content/plugins/googlmapsareas/img/61391.png"></img>
                                    <lable class="marker__label-title">Link when click icon
                                            <textarea name="link"
                                                      class="marker__link-text"><?php echo $marker->link; ?></textarea>
                                    </lable>
                                </div>
                            <?php }
                            if (!empty($marker->script)) { ?>
                                <div class="marker__group-script isset-click">
                                    <img src="/wp-content/plugins/googlmapsareas/img/61391.png"></img>
                                    <lable class="marker__label-title">Custom script when click
                                            <textarea name="script"
                                                      class="marker__script-text"><?php echo $marker->script; ?></textarea>
                                    </lable>
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
                                var icon = {
                                    url: '/wp-content/plugins/googlmapsareas/img/marker-icon/' + img,
                                    scaledSize: new google.maps.Size(50, 50)
                                };
                                marker.setIcon(icon);
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
                                if(!empty($marker->link)){ ?>
                                marker.addListener('click', function () {
                                    window.open('<?php echo $marker->link;?>');
                                });
                                <?php }
                                if(!empty($marker->script)){ ?>
                                marker.addListener('click', function () {
                                    eval(<?php echo $marker->script;?>);
                                });
                                <?php }
                                if ($marker->attachment_id != 0) {
                                    $img = wp_get_attachment_image_src($marker->attachment_id, 'large');
                                    $img = $img[0];
                                ?>
                                var icon = {
                                    url: '<?php echo $img; ?>',
                                    scaledSize: new google.maps.Size(50, 50)
                                };
                                marker.setIcon(icon);
                                <?php } ?>

                                markers[<?php echo $marker->id_marker?>] = marker;
                                LatLngList.push(new google.maps.LatLng(<?php echo $marker->coordinates; ?>));
                            });
                        </script>
                    <?php } ?>
                        <script>
                            jQuery(window).load(function () {
                                var latlngbounds = new google.maps.LatLngBounds();
                                LatLngList.forEach(function (latLng) {
                                    latlngbounds.extend(latLng);
                                });
                                <?php if(!empty($coordinates[0] && !empty($coordinates[0]) && !empty($zoom))){
                                ?>
                                map.setCenter(new google.maps.LatLng(<?php echo $coordinates[0]?>, <?php echo $coordinates[1]?>));
                                map.setZoom(Number(<?php echo (int)$zoom; ?>));
                                <?php } else {?>
                                map.setCenter(latlngbounds.getCenter());
                                map.fitBounds(latlngbounds);
                                <?php }?>
console.log(polies);
                            });
                        </script>
                    <?php } ?>
                </ul>
            </div>
            <?php
        }
    }

    /**
     * Remove editor
     */
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

    /**
     * Render shortcode on the front page
     *
     * @param array $attr All parameters that passed in the shortcode
     * @return string @result
     */
    function shortcode_func($attr)
    {
        $markers = Marker::getMarkerByPostId($attr['id']);
        $markers = json_encode($markers);
        $coordinate = get_post_meta((int)$attr['id'], 'coordinate', true);
        $coordinate = explode(',', $coordinate);
        $zoom = get_post_meta((int)$attr['id'], 'zoom', true);
        $polyline = Polylines::getAllByPostId($attr['id']);


        $width = '';
        $height = '';
        if (isset($attr['width'])) {
            $width = $attr['width'] . 'px';
        } else {
            $width = '100%';
        }
        if (isset($attr['height'])) {
            $height = $attr['height'] . 'px';
        } else {
            $height = '400px';
        }
        $result =
            '<div class="map__wrapper">
                <script
                    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCByLB65AuBykaj_lFmuiZWrIRYXvLLvJI&callback=initMap"
                    async defer>
                </script>
                <div id="map" style="width:' . $width . ';height:' . $height . ';"></div>
            </div>
            ';
       foreach($polyline as $poly){
            $result .= '<script>
            jQuery(window).load(function(){
            var flightPlanCoordinates =['.$poly->coordinates.'];

            var poly = new google.maps.Polyline({
                path: flightPlanCoordinates,
            geodesic: true,
            strokeColor: \''.$poly->color.'\',
            strokeOpacity: 1.0,
            strokeWeight: '.$poly->thick.'
            });
            poly.setMap(map);
            });
            </script>';
        }

        wp_enqueue_script('goglemapsareas-front-map', plugin_dir_url(__FILE__) . 'js/frontMap.js');
        wp_localize_script('goglemapsareas-front-map', 'markers_object', array(
            'markers' => $markers,
            'coordinate' => $coordinate,
            'zoom' => $zoom,
            'polylines' => $polyline,
        ));

        return $result;
    }

}

global $google_maps_areas;
$google_maps_areas = new GooglMapsAreasFunc();
$google_maps_areas->init();
?>