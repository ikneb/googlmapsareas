var markers = JSON.parse(markers_object.markers);

var LatLngList = [];
var map;
function initMap() {
    // Create a map object and specify the DOM element for display.
    map = new google.maps.Map(document.getElementById("map"), {
        center: {lat: 49.994783, lng: 36.1430755},
        scrollwheel: false,
        zoom: 10
    });
    jQuery.each(markers, function(){
        var arr_coord = this.coordinates.split(",");
        var marker = new google.maps.Marker({
            position: {lat: Number(arr_coord[0]), lng: Number(arr_coord[1])},
            map: map,
        });


        if(this.icon != 'default'){
            var icon = '/wp-content/plugins/googlmapsareas/img/marker-icon/' + this.icon;
            marker.setIcon(icon);
        }
        if(this.attachment_id != 0){
            attachment_id = this.attachment_id;
            jQuery.ajax({
                type: 'POST',
                url: '/wp-content/plugins/googlmapsareas/ajax.php',
                data: {
                    action: 'get_img',
                    attachment_id: attachment_id
                },
                success: function (data) {
                    var icon = {
                        url: data,
                        scaledSize: new google.maps.Size(50, 50)
                    };
                    marker.setIcon(icon);
                }
            });
        }
        if(this.label_text != ''){
            marker.setTitle(this.label_text);
        }
        if(this.window_text != ''){
            var contentString = '<div>' + this.window_text + '</div>';
            google.maps.event.clearListeners(marker, "click");
            var infowindow = new google.maps.InfoWindow({
                content: contentString,
                maxWidth: 200
            });
            google.maps.event.addListener(marker, "click", function () {
                infowindow.open(map, marker);
            });
        }
        var animat;
        if(this.animate == 'DROP'){
            animat = google.maps.Animation.DROP;
        }else if(this.animate == 'BOUNCE'){
            animat = google.maps.Animation.BOUNCE;
        }
        marker.addListener("click", function () {
            marker.setAnimation(animat);
        });
        if(this.link != ''){
            link = this.link;
            marker.addListener("click", function () {
                window.open(link);
            });
        }

        if(this.script != ''){
            script = this.script;
            marker.addListener("click", function () {
                eval(script);
            });
        }

        LatLngList.push(  new google.maps.LatLng (Number(arr_coord[0]),Number(arr_coord[1])) );
    });

    var latlngbounds = new google.maps.LatLngBounds();
    LatLngList.forEach(function(latLng){
        latlngbounds.extend(latLng);
    });
    if(markers_object.coordinate.length > 0){

        map.setCenter(new google.maps.LatLng(Number(markers_object.coordinate[0]), Number(markers_object.coordinate[1])));
        map.setZoom(Number(markers_object.zoom));
    }else{
        map.setCenter(latlngbounds.getCenter());
        map.fitBounds(latlngbounds);
    }
}