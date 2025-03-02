import L from 'leaflet';


document.addEventListener("DOMContentLoaded", function () {
    var latInput = document.getElementById("latitude");
    var lngInput = document.getElementById("longitude");
    var latText = document.getElementById("latText");
    var lngText = document.getElementById("lngText");
    var mapzoom = document.getElementById("mapzoom") ? parseInt(document.getElementById("mapzoom").value) : 5;
    console.log("zoom: "+mapzoom);
    var initialLat = latInput.value ? parseFloat(latInput.value) : 40.4168; // Spain
    var initialLng = lngInput.value ? parseFloat(lngInput.value) : -3.7038;

    var map = L.map('map').setView([initialLat, initialLng], mapzoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const customIcon = L.icon({
        iconUrl: '/images/marker-icon.png',
        iconSize: [25, 41], // Tamaño del icono
        iconAnchor: [12, 41], // Punto donde se "ancla" en el mapa
        popupAnchor: [0, -41] // Punto desde donde aparece el popup
    });    

    var marker = L.marker([initialLat, initialLng], { icon: customIcon ,draggable: true }).addTo(map)
    //.bindPopup('A pretty CSS popup.<br> Easily customizable.')
    .openPopup();
    function updateCoordinates(lat, lng) {
        latInput.value = lat;
        lngInput.value = lng;
        latText.textContent = lat;
        lngText.textContent = lng;
    }

    marker.on('dragend', function (e) {
        var latlng = marker.getLatLng();
        updateCoordinates(latlng.lat, latlng.lng);
        reverseGeocode(latlng.lat, latlng.lng);

    });

    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        updateCoordinates(e.latlng.lat, e.latlng.lng);
    });

});



function reverseGeocode(lat, lon) {
    const geocodeUrl = `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`;

    fetch(geocodeUrl)
        .then(response => response.json())
        .then(data => {
            if (data && data.name) {
                const location_name = data.name;
                document.getElementById('location_name').value = location_name;
            }
            if (data && data.address && data.address.road) {
                const location_streetAddress = data.address.road;
                document.getElementById('location_streetAddress').value = location_streetAddress;
            }
            if (data && data.address && data.address.postcode) {
                const location_postalCode = data.address.postcode;
                document.getElementById('location_postalCode').value = location_postalCode;
            }
            if (data && data.address && data.address.city) {
                const location_addressLocality = data.address.city;
                document.getElementById('location_addressLocality').value = location_addressLocality;
            }
            if (data && data.address && data.address.province) {
                const location_addressRegion = data.address.province;
                document.getElementById('location_addressRegion').value = location_addressRegion;
            }
            if (data && data.address && data.address.country) {
                const location_addressCountry = data.address.country;
                document.getElementById('location_addressCountry').value = location_addressCountry;
            }
        })
        .catch(error => {
            console.error('Error geocodificando la localización:', error);
        });
}


