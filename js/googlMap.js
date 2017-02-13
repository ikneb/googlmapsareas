var labels = '';
var labelIndex = 0;
var map;
var markers = [];


function initMap() {
    var myLatlng = {lat: 49.994783, lng: 36.1430755};
    map = new google.maps.Map(document.getElementById('map'), {
        center: myLatlng,
        scrollwheel: false,
        zoom: 10
    });

    google.maps.event.addListener(map, 'click', function (event) {
        var newLi = document.createElement('li');

        id_marker = addMarker(event.latLng, map)-1;
        newLi.innerHTML =
            '<button class="marker__remove button button-primary">Delete field</button>' +
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
            '<select class="marker__select">' +
            '<option value="default">Default</option>' +
            '</select>' +
            '</div>' +
            '<div class="marker__group-icon">' +
            '<lable class="marker__label-action">Action</lable>' +
            '<select class="marker__select-action">' +
            '<option value="0">Add</option>' +
            '<option value="1">Title</option>' +
            '<option value="2">Info windows</option>' +
            '<option value="3">Animate effect</option>' +
            '</select>' +
            '</div>';

        marker__list.appendChild(newLi);
        getMarker(newLi);

    });
}


function addMarker(location, map) {
    var marker = new google.maps.Marker({
        position: location,
        label: labels[labelIndex++ % labels.length],
        map: map,
    });

    return markers.push(marker);
}

function getMarker(newLi) {
    jQuery.ajax({
        type: 'POST',
        url: '/wp-content/plugins/googlmapsareas/ajax.php',
        data: {action: 'get_all_markers'},
        success: function (data) {
            var all_marker_name = JSON.parse(data);
            jQuery.each(all_marker_name, function () {
                jQuery(newLi).find('.marker__select').append('<option value="' + this + '.png">' + this + '</option>');
            });
        }
    });
}


jQuery(document).on('click', '.marker__remove', function (e) {
    e.preventDefault();
    jQuery(this).closest('li').remove();
    var id = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');
    markers[id].setMap(null);
});


jQuery(document).on('click', '.marker__save', function (e) {
    e.preventDefault();
    var id_marker = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');
    var id_map_post = jQuery(this).closest('.marker__wrapper').attr('data-postid');
    var name = jQuery(this).closest('li').find('input[name="name"]').val();
    var coordinates = jQuery(this).closest('li').find('.marker__group-name').attr('data-c');
    var icon = jQuery(this).closest('li').find('.marker__select').val();
    var label_text = jQuery(this).closest('li').find('textarea[name="label"]').val()?
        jQuery(this).closest('li').find('textarea[name="label"]').val(): '';
    var window_text = jQuery(this).closest('li').find('textarea[name="window"]').val()?
        jQuery(this).closest('li').find('textarea[name="window"]').val(): '';
    var animate = jQuery(this).closest('li').find('.marker__select-animate').val()?
        jQuery(this).closest('li').find('.marker__select-animate').val() : '';

    jQuery.ajax({
        type: 'POST',
        url: '/wp-content/plugins/googlmapsareas/ajax.php',
        data: {
            action: 'save_marker',
            id_marker: id_marker,
            id_map_post: id_map_post,
            name: name,
            coordinates: coordinates,
            icon: icon,
            label_text: label_text,
            window_text: window_text,
            animate: animate
        },
        success: function (data) {
            console.log(data);
        }
    });
});

jQuery(document).on('click', '.marker__select', function (e) {
    e.preventDefault();
    var img = jQuery(this).val();
    var id = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');

    if (img == 'default') {
        markers[id].setIcon();
    } else {
        markers[id].setIcon('/wp-content/plugins/googlmapsareas/img/marker-icon/' + img);
    }
});


jQuery(document).on('click', '.marker__select-action', function (e) {
    e.preventDefault();
    var select = jQuery(this).val();
    var id = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');

    switch (select) {
        case '0':
            console.log(0);
            break;
        case '1':
            if (!jQuery(this).closest('li').find('.marker__group-title').hasClass('marker__group-title')) {
                jQuery(this).closest('li').append(
                    '<div class="marker__group-title">' +
                    '<img src="/wp-content/plugins/googlmapsareas/img/61391.png"></img>' +
                    '<lable class="marker__label-title">Lable for icon</lable>' +
                    '<textarea name="label" class="marker__label-text"></textarea>' +
                        //'<p>Will be displayed when you hover on marker</p>' +
                    '</div>'
                );
            }
            break;
        case '2':
            if (!jQuery(this).closest('li').find('.marker__group-windows').hasClass('marker__group-windows')) {
                jQuery(this).closest('li').append(
                    '<div class="marker__group-windows">' +
                    '<img src="/wp-content/plugins/googlmapsareas/img/61391.png"></img>' +
                    '<lable class="marker__label-title">Window info for icon' +
                    '<textarea name="window" class="marker__windows-text"></textarea></lable>' +
                        //'<p>Will be displayed when you click on marker</p>' +
                    '</div>'
                );
            }
            break;
        case '3':
            if (!jQuery(this).closest('li').find('.marker__group-animate').hasClass('marker__group-animate')) {
                jQuery(this).closest('li').append(
                    '<div class="marker__group-animate">' +
                    '<img src="/wp-content/plugins/googlmapsareas/img/61391.png"></img>' +
                    '<lable class="marker__label-title">Animate effect</lable>' +
                    '<select class="marker__select-animate">' +
                    '<option value="0">Select animation effect</option>' +
                    '<option value="BOUNCE">BOUNCE</option>' +
                    '<option value="DROP">DROP</option>' +
                    '</select>' +
                    '</div>'
                );
            }
            break;
    }
});


jQuery(document).on('keyup', '.marker__label-text', function (e) {
    e.preventDefault();
    var id = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');
    var value = jQuery(this).val();
    markers[id].setTitle(value);
});


jQuery(document).on('click', '.marker__group-title img', function (e) {
    e.preventDefault();
    var id = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');
    markers[id].setTitle();
    jQuery(this).closest('.marker__group-title').remove();
});


jQuery(document).on('click', '.marker__group-windows img', function (e) {
    e.preventDefault();
    var id = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');
    google.maps.event.clearListeners(markers[id], 'click');
    jQuery(this).closest('.marker__group-windows').remove();
});


jQuery(document).on('click', '.marker__group-animate img', function (e) {
    e.preventDefault();
    var id = jQuery(this).closest('li').find('.marker__group-name').attr('data-id');
    google.maps.event.clearListeners(markers[id], 'click');
    jQuery(this).closest('.marker__group-animate').remove();
});


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
