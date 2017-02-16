var labels = '';
var labelIndex = 0;
var map;
var markers = [];
var LatLngList = [];
var files;
var poly;
var polies=[];


/**
 * Init map on the post admin page
 */
function initMap() {
    var myLatlng = {lat: 51.508530, lng: -0.076132};
    map = new google.maps.Map(document.getElementById('map'), {
        center: myLatlng,
        scrollwheel: false,
        zoom: 10
    });
    addListenerForAddMarkers(myLatlng);
}


function addListenerForAddMarkers(myLatlng) {
    google.maps.event.addListener(map, 'click', function (event) {
        var newLi = document.createElement('li');

        id_marker = addMarker(event.latLng, map) - 1;
        newLi.innerHTML =
            '<button class="marker__remove button button-primary">Delete marker</button>' +
            '<button class="marker__save button button-primary">Save</button>' +
            '<div class="marker__group-name" data-c="' +
            event.latLng.lat().toFixed(3) + ',' +
            event.latLng.lng().toFixed(3) + '"' +
            ' data-id ="' + id_marker + '">' +
            '<label class="marker__lable-name">Name</label>' +
            '<input name="name" class="marker__input">' +
            '</div>' +
            '<div class="marker__group-icon">' +
            '<lable class="marker__label-select">Icon</lable>' +
            '<select class="marker__select-method">' +
            '<option value="0">Choose</option>' +
            '<option value="1">Select icon</option>' +
            '<option value="2">Upload icon</option>' +
            '</select></div>' +
            '<div class=" marker__group-icon no-active marker__download">' +
            '<div class="marker__download-icon" data-id="' + id_marker + '" data-src="">' +
            '<img  src="/wp-content/plugins/googlmapsareas/img/default.png" width="116px" height="116px"/><div>' +
            '<button type="submit" class="upload_image_button button">Загрузить</button>' +
            '<button type="submit" class="remove_image_button button">&times;</button>' +
            '</div></div></div>' +
            '<div class=" marker__group-icon no-active marker__select">' +
            '<lable class="marker__label-select">Select icon</lable>' +
            '<select class="marker__select-icon" >' +
            '<option value="default">Default</option></select></div>' +
            '<div class="marker__group-icon">' +
            '<lable class="marker__label-action">Action</lable>' +
            '<select class="marker__select-action">' +
            '<option value="0">Add</option>' +
            '<option value="1">Title</option>' +
            '<option value="2">Info windows</option>' +
            '<option value="3">Animate effect</option>' +
            '<option value="4">Link</option>' +
            '<option value="5">Custom script</option>' +
            '</select>' +
            '</div>';


        marker__list.appendChild(newLi);
        getMarker(newLi);

        clickDownlosd();

    });
}

/**
 * Add marker when click on the map
 *
 * @param object location Coordinates click
 * @param object map
 * @return object markers All marker in this map.
 */
function addMarker(location, map) {
    var marker = new google.maps.Marker({
        position: location,
        label: labels[labelIndex++ % labels.length],
        map: map,
    });

    return markers.push(marker);
}

/**
 * Add get default markers and insert in select
 */
function getMarker(newLi) {
    jQuery.ajax({
        type: 'POST',
        url: '/wp-content/plugins/googlmapsareas/ajax.php',
        data: {action: 'get_all_markers'},
        success: function (data) {
            var all_marker_name = JSON.parse(data);
            jQuery.each(all_marker_name, function () {
                jQuery(newLi).find('.marker__select-icon').append('<option value="' + this + '.png">' + this + '</option>');
            });
        }
    });
}

/**
 * Remove marker
 */
jQuery(document).on('click', '.marker__remove', function (e) {
    e.preventDefault();

    var id = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');
    var id_map_post = jQuery(this).closest('.marker__wrapper').attr('data-postid');
    jQuery(this).closest('li').remove();
    markers[id].setMap(null);
    jQuery.ajax({
        type: 'POST',
        url: '/wp-content/plugins/googlmapsareas/ajax.php',
        data: {
            action: 'remove_marker',
            id_marker: id,
            id_map_post: id_map_post,
        },
        success: function (data) {
            process(data);
        }
    });

});

/**
 * Save or update marker
 */
jQuery(document).on('click', '.marker__save', function (e) {
    e.preventDefault();
    var id_marker = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');
    var id_map_post = jQuery(this).closest('.marker__wrapper').attr('data-postid');
    var name = jQuery(this).closest('li').find('input[name="name"]').val();
    var coordinates = jQuery(this).closest('li').find('.marker__group-name').attr('data-c');
    var icon = jQuery(this).closest('li').find('.marker__select-icon').val();
    var method = jQuery(this).closest('li').find('.marker__select-method').val();
    var attachment_id = jQuery(this).closest('li').find('.marker__download-icon').attr('data-src');
    var label_text = jQuery(this).closest('li').find('textarea[name="label"]').val() ?
        jQuery(this).closest('li').find('textarea[name="label"]').val() : '';
    var window_text = jQuery(this).closest('li').find('textarea[name="window"]').val() ?
        jQuery(this).closest('li').find('textarea[name="window"]').val() : '';
    var animate = jQuery(this).closest('li').find('.marker__select-animate').val() ?
        jQuery(this).closest('li').find('.marker__select-animate').val() : '';
    var link = jQuery(this).closest('li').find('.marker__link-text').val() ?
        jQuery(this).closest('li').find('.marker__link-text').val() : '';
    var script = jQuery(this).closest('li').find('.marker__script-text').val() ?
        jQuery(this).closest('li').find('.marker__script-text').val() : '';

    if (method == 2 && attachment_id == '') {
        icon = 'default';
        method = 0;
    }


    jQuery.ajax({
        type: 'POST',
        url: '/wp-content/plugins/googlmapsareas/ajax.php',
        data: {
            action: 'save_marker',
            id_marker: id_marker,
            id_map_post: id_map_post,
            name: name,
            coordinates: coordinates,
            method: method,
            icon: icon,
            label_text: label_text,
            window_text: window_text,
            animate: animate,
            link: link,
            script: script,
            attachment_id: attachment_id
        },
        success: function (data) {
            process(data);
        }
    });
});

/**
 * Select icon for marker
 */
jQuery(document).on('click', '.marker__select-icon', function (e) {
    e.preventDefault();
    var img = jQuery(this).val();
    var id = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');

    if (img == 'default') {
        markers[id].setIcon();
    } else {
        var icon = {
            url: '/wp-content/plugins/googlmapsareas/img/marker-icon/' + img,
            scaledSize: new google.maps.Size(50, 50)
        };
        markers[id].setIcon(icon);
    }
});

/**
 * Select action on the click for marker
 */
jQuery(document).on('click', '.marker__select-action', function (e) {
    e.preventDefault();
    var select = jQuery(this).val();
    var id = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');

    switch (select) {
        case '1':
            if (!jQuery(this).closest('li').find('.marker__group-title').hasClass('marker__group-title')) {
                jQuery(this).closest('li').append(
                    '<div class="marker__group-title">' +
                    '<img src="/wp-content/plugins/googlmapsareas/img/61391.png"></img>' +
                    '<lable class="marker__label-title">Lable for hover icon</lable>' +
                    '<textarea name="label" class="marker__label-text"></textarea>' +
                        //'<p>Will be displayed when you hover on marker</p>' +
                    '</div>'
                );
            }
            break;
        case '2':
            if (!jQuery(this).closest('li').find('.isset-click').hasClass('isset-click')) {
                jQuery(this).closest('li').append(
                    '<div class="marker__group-windows isset-click">' +
                    '<img src="/wp-content/plugins/googlmapsareas/img/61391.png"></img>' +
                    '<lable class="marker__label-title">Window info when click' +
                    '<textarea name="window" class="marker__windows-text"></textarea></lable>' +
                    '</div>'
                );
            }
            break;
        case '3':
            if (!jQuery(this).closest('li').find('.isset-click').hasClass('isset-click')) {
                jQuery(this).closest('li').append(
                    '<div class="marker__group-animate isset-click">' +
                    '<img src="/wp-content/plugins/googlmapsareas/img/61391.png"></img>' +
                    '<lable class="marker__label-title">Animate effect when click</lable>' +
                    '<select class="marker__select-animate">' +
                    '<option value="0">Select animation effect</option>' +
                    '<option value="BOUNCE">BOUNCE</option>' +
                    '<option value="DROP">DROP</option>' +
                    '</select>' +
                    '</div>'
                );
            }
            break;
        case '4':
            if (!jQuery(this).closest('li').find('.isset-click').hasClass('isset-click')) {
                jQuery(this).closest('li').append(
                    '<div class="marker__group-link isset-click">' +
                    '<img src="/wp-content/plugins/googlmapsareas/img/61391.png"></img>' +
                    '<lable class="marker__label-title">Link when click icon' +
                    '<textarea name="link" class="marker__link-text"></textarea></lable>' +
                    '</div>'
                );
            }
            break;
        case '5':
            if (!jQuery(this).closest('li').find('.isset-click').hasClass('isset-click')) {
                jQuery(this).closest('li').append(
                    '<div class="marker__group-script isset-click">' +
                    '<img src="/wp-content/plugins/googlmapsareas/img/61391.png"></img>' +
                    '<lable class="marker__label-title">Custom script when click' +
                    '<textarea name="script" class="marker__script-text"></textarea></lable>' +
                    '</div>'
                );
            }
            break;
    }
});

/**
 * Remove action when hover and render title marker
 */
jQuery(document).on('click', '.marker__group-title img', function (e) {
    e.preventDefault();
    var id = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');
    markers[id].setTitle();
    jQuery(this).closest('.marker__group-title').remove();
});

/**
 * Remove action window when click
 */
jQuery(document).on('click', '.marker__group-windows img', function (e) {
    e.preventDefault();
    var id = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');
    google.maps.event.clearListeners(markers[id], 'click');
    jQuery(this).closest('.marker__group-windows').remove();
});

/**
 * Remove action animation
 */
jQuery(document).on('click', '.marker__group-animate img', function (e) {
    e.preventDefault();
    var id = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');
    google.maps.event.clearListeners(markers[id], 'click');
    jQuery(this).closest('.marker__group-animate').remove();
});

/**
 * Remove action link
 */
jQuery(document).on('click', '.marker__group-link img', function (e) {
    e.preventDefault();
    var id = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');
    google.maps.event.clearListeners(markers[id], 'click');
    jQuery(this).closest('.marker__group-link').remove();
});

/**
 * Remove action custom script
 */
jQuery(document).on('click', '.marker__group-script img', function (e) {
    e.preventDefault();
    var id = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');
    google.maps.event.clearListeners(markers[id], 'click');
    jQuery(this).closest('.marker__group-script').remove();
});

/**
 * Add listener whit custom script on click marker
 */
jQuery(document).on('keyup', '.marker__script-text', function (e) {
    e.preventDefault();
    var id = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');
    var value = jQuery(this).val();
    google.maps.event.clearListeners(markers[id], 'click');
    google.maps.event.addListener(markers[id], 'click', function () {
        eval(value);
    });
});

/**
 * Add listener whit redirect link on click marker
 */
jQuery(document).on('keyup', '.marker__link-text', function (e) {
    e.preventDefault();
    var id = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');
    var value = jQuery(this).val();
    google.maps.event.clearListeners(markers[id], 'click');
    google.maps.event.addListener(markers[id], 'click', function () {
        window.open(value);
    });
});

/**
 * Add title on hover marker
 */
jQuery(document).on('keyup', '.marker__label-text', function (e) {
    e.preventDefault();
    var id = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');
    var value = jQuery(this).val();
    markers[id].setTitle(value);
});


/**
 * Add listener whit window on click marker
 */
jQuery(document).on('keyup', '.marker__windows-text', function (e) {
    e.preventDefault();
    var id = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');
    var value = jQuery(this).val();

    var contentString = '<div id="content">' + value + '</div>';

    google.maps.event.clearListeners(markers[id], 'click');


    google.maps.event.addListener(markers[id], 'click', function () {

        var infowindow = new google.maps.InfoWindow({
            content: contentString,
            maxWidth: 200
        });
        infowindow.open(map, markers[id]);
    });
});

/**
 * Add listener whit animate effect on click marker
 */
jQuery(document).on('click', '.marker__select-animate', function (e) {
    e.preventDefault();
    var select = jQuery(this).val();
    var id = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');

    switch (select) {
        case '0':
            break;
        case 'BOUNCE':
            markers[id].addListener('click', function () {
                markers[id].setAnimation(google.maps.Animation.BOUNCE);
            });
            break;
        case 'DROP':
            markers[id].addListener('click', function () {
                markers[id].setAnimation(google.maps.Animation.DROP);
            });
            break;
        case '3':

            break;
    }
});

/**
 * Select method change marker icon
 */
jQuery(document).on('click', '.marker__select-method', function (e) {
    e.preventDefault();
    var select = jQuery(this).val();
    switch (select) {
        case '0':
            break;
        case '1':
            var id = jQuery(this).closest('li').find('.marker__download-icon').attr('data-id');
            markers[id].setIcon();
            jQuery(this).closest('li').find('.marker__download-icon').children().attr('src', '/wp-content/plugins/googlmapsareas/img/default.png');
            jQuery(this).closest('li').find('.marker__select').removeClass('no-active');
            jQuery(this).closest('li').find('.marker__download').addClass('no-active');
            break;
        case '2':
            var id = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');
            markers[id].setIcon();
            jQuery(this).closest('li').find('.marker__download').removeClass('no-active');
            jQuery(this).closest('li').find('.marker__select').addClass('no-active');

            break;
    }
});

/**
 * Notification process save or remove markers
 */
function process(data) {
    if (data) {
        jQuery('.map__wrapper').append(
            '<div class="notice notice-success is-dismissible">' +
            ' <p><strong>Success.</strong></p></div>');
        setTimeout(function () {
            jQuery('.notice-success').remove();
        }, 2000);
    } else {
        jQuery('.map__wrapper').append(
            '<div class="notice notice-error is-dismissible">' +
            ' <p><strong>Error.</strong></p></div>');
        setTimeout(function () {
            jQuery('.notice-error').remove();
        }, 2000);

    }
}


/**
 * Download or remove icon
 */
jQuery(window).load(function () {
    clickDownlosd();
});
function clickDownlosd() {
    jQuery(function ($) {
        $('.upload_image_button').click(function () {
            var send_attachment_bkp = wp.media.editor.send.attachment;
            var button = $(this);
            wp.media.editor.send.attachment = function (props, attachment) {
                $(button).parent().prev().attr('src', attachment.url);
                $(button).prev().val(attachment.id);
                wp.media.editor.send.attachment = send_attachment_bkp;

                var id = button.closest('.marker__download-icon').attr('data-id');
                var icon = {
                    url: attachment.url,
                    scaledSize: new google.maps.Size(50, 50)
                };
                markers[id].setIcon(icon);

                button.closest('.marker__download-icon').attr('data-src', attachment.id);
            }
            wp.media.editor.open(button);
            return false;
        });

        $('.remove_image_button').click(function (e) {
            e.preventDefault();
            var button = $(this);
            var id = button.closest('.marker__download-icon').attr('data-id');

            button.closest('.marker__download-icon').attr('data-src', '');
            button.closest('li').find('.marker__download-icon').children().attr('src', '/wp-content/plugins/googlmapsareas/img/default.png');
            markers[id].setIcon();
        });
    });

}

/**
 * Save center map
 */
jQuery(document).on('click', '.current', function (e) {
    var center = map.getCenter();
    var coord = center.lat() + ',' + center.lng();
    var zoom = map.getZoom();
    var id_post = jQuery(this).closest('.center-map').attr('data-id');
    jQuery.ajax({
        type: 'POST',
        url: '/wp-content/plugins/googlmapsareas/ajax.php',
        data: {
            action: 'center-map',
            coord: coord,
            zoom: zoom,
            id_post: id_post
        },
        success: function (data) {
            console.log(data);
        }
    });

});


/**
 * Remove center map
 */
jQuery(document).on('click', '.center-marker', function (e) {

    var id_post = jQuery(this).closest('.center-map').attr('data-id');
    jQuery.ajax({
        type: 'POST',
        url: '/wp-content/plugins/googlmapsareas/ajax.php',
        data: {
            action: 'center-marker',
            id_post: id_post
        },
        success: function (data) {
            console.log(data);
        }
    });

});

/**
 * Add polyline
 */
jQuery(document).on('click', '.polylines-add', function (e) {
    e.preventDefault();
    jQuery(this).addClass('no-active');
    var newLi = document.createElement('li');
    newLi.innerHTML =
        '<button class="polylines__remove button button-primary no-active">Delete polyline</button>' +
        '<button class="polylines__save button button-primary no-active">Save</button>' +
        '<div class="polylines__group">' +
        '<label class="polylines__lable">Name</label>' +
        '<input name="name" class="marker__input">' +
        '</div>' +
        '<div class="polylines__group">' +
        '<lable class="polylines__lable">Color</lable>' +
        '<select class="polylines__select-color">' +
        '<option value="#000000">Black</option>' +
        '<option value="#0000FF">Blue</option>' +
        '<option value="#FF0000">Red</option>' +
        '</select></div>' +
        '<div class="polylines__group">' +
        '<lable class="polylines__lable">Thickness of the line(px)</lable>' +
        '<input class="polylines__select-thick" type="number" name="thick" value="1">' +
        '</div>';

    polyline__add.appendChild(newLi);

    poly = new google.maps.Polyline({
        strokeColor: '#000000',
        strokeOpacity: 1.0,
        strokeWeight: 2,
        editable: true
    });
    map.setOptions({draggableCursor: 'crosshair'});
    poly.setMap(map);
    google.maps.event.clearListeners(map, 'click');
    map.addListener('click', function(event){
        if(jQuery(newLi).find('.polylines__save').hasClass('no-active')){
            jQuery(newLi).find('.polylines__save').removeClass('no-active')
            jQuery(newLi).find('.polylines__remove').removeClass('no-active')
        }
        var path = poly.getPath();
        path.push(event.latLng);
        coord_poly =  getPathVariableCode(poly);
        jQuery('#polyline__add').find('li').attr('data-cord', coord_poly);
    });

});

/**
 *   Writing all coordinate polyline in variable
 */
function getPathVariableCode(line) {
    var codeStr = '';
    var pathArr = line.getPath();
    for (var i = 0; i < pathArr.length; i++) {
        codeStr += '    {lat: ' + pathArr.getAt(i).lat() + ', lng: ' + pathArr.getAt(i).lng() + '}';
        if (i !== pathArr.length - 1) {
            codeStr += ',\n';
        }
    }

    return codeStr;
};


/**
 * Save polyline
 */
jQuery(document).on('click', '.polylines__save', function (e) {
    e.preventDefault();
    var myLatlng = {lat: 51.508530, lng: -0.076132};
    map.setOptions({draggableCursor: 'url(http://maps.google.com/mapfiles/openhand.cur), move'});
    google.maps.event.clearListeners(map, 'click');
    addListenerForAddMarkers(myLatlng);
    var coord = jQuery(this).closest('li').attr('data-cord');
    var id = jQuery(this).closest('li').attr('data-id') ? jQuery(this).closest('li').attr('data-id'): '';
    var id_post =  jQuery(this).closest('.polylines__wrapper').attr('data-id');
    var name = jQuery(this).closest('li').find('input[name="name"]').val();
    var color = jQuery(this).closest('li').find('.polylines__select-color').val();
    var thick = jQuery(this).closest('li').find('input[name="thick"]').val();


    var button =jQuery(this);
    jQuery.ajax({
        type: 'POST',
        url: '/wp-content/plugins/googlmapsareas/ajax.php',
        data: {
            action: 'save_polyline',
            id: id,
            coordinate: coord,
            name: name,
            id_post: id_post,
            color: color,
            thick: thick
        },
        success: function (data) {
            button.closest('li').attr('data-id', data );
            jQuery('.polylines-add').removeClass('no-active');
            process(data);
            polies[data] = poly;
        }
    });
});

/**
 * Remove polyline
 */
jQuery(document).on('click', '.polylines__remove', function (e) {
    e.preventDefault();
    var myLatlng = {lat: 51.508530, lng: -0.076132};
    map.setOptions({draggableCursor: 'url(http://maps.google.com/mapfiles/openhand.cur), move'});
    google.maps.event.clearListeners(map, 'click');
    addListenerForAddMarkers(myLatlng);
    var id = jQuery(this).closest('li').attr('data-id') ? jQuery(this).closest('li').attr('data-id'): '';
     polies[id].setMap(null);
    jQuery(this).closest('li').remove();
    jQuery('.polylines-add').removeClass('no-active');
    jQuery.ajax({
        type: 'POST',
        url: '/wp-content/plugins/googlmapsareas/ajax.php',
        data: {
            action: 'remove_polyline',
            id: id,
        },
        success: function (data) {
            process(data);
        }
    });
});


/**
 * Dynamic change color polylines
 */
jQuery(document).on('click', '.polylines__select-color', function(e){
    e.preventDefault();
    var  color = jQuery(this).val();
    var id = jQuery(this).closest('li').attr('data-id') ? jQuery(this).closest('li').attr('data-id'): '';
    if(id == ''){
        poly.setOptions({strokeColor: color});
    }else{
        polies[id].setOptions({strokeColor: color});
    }
});


/**
 * Dynamic change thick polylines
 */
jQuery(document).on('keyup', '.polylines__select-thick', function(e){
    e.preventDefault();
    var  thick = jQuery(this).val();
    var id = jQuery(this).closest('li').attr('data-id') ? jQuery(this).closest('li').attr('data-id'): '';

    if(id == ''){
        poly.setOptions({ strokeWeight: thick});
    }else{
        polies[id].setOptions({ strokeWeight: thick});
    }
})