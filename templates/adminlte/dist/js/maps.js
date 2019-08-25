function initMap() {
    var input = document.getElementById('searchMapInput');

    var autocomplete = new google.maps.places.Autocomplete(input);
    autocomplete.addListener('place_changed', function() {
        var place = autocomplete.getPlace();
        $('input[name=placelatitude]').val(place.geometry.location.lat());
        $('input[name=placelongitude]').val(place.geometry.location.lng());
    });
}