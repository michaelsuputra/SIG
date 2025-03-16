@extends('layouts.1home')
@section('content')

<div id="map" style="width: 80%; height: 500px; margin: auto;"></div>

<table class="table table-bordered" style="width: 80%; margin: auto; margin-top: 20px; border: 4px;">
    <thead>
        <tr>
            <th>Name</th>
            <th>Latitude</th>
            <th>Longitude</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody id="marker-table"></tbody>
</table>

<script>
    var mapboxAccessToken = "{{ env('MAPBOX_ACCESS_TOKEN') }}";

    var mapboxURL = 'https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=' + mapboxAccessToken;




    var peta1 = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    });
    var peta3 = L.tileLayer(mapboxURL, {
        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
            '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
            'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
        id: 'mapbox/streets-v11'
    });

    var peta2 = L.tileLayer(mapboxURL, {
        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
            '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
            'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
        id: 'mapbox/satellite-v9'
    });

    var peta3 = L.tileLayer(mapboxURL, {
        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
            '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
            'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
        id: 'mapbox/streets-v11'
    });

    
    var peta4 = L.tileLayer(mapboxURL, {
        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
            '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
            'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
        id: 'mapbox/dark-v10'
    });

    var map = L.map('map', {
        center: [-8.795306484652961, 115.17586101733077], // Lokasi awal (Bali)
        zoom: 10,
        layers: [peta1]
    });

    var baseMaps = {
        "Grayscale": peta1,
        "Satellite": peta2,
        "Streets": peta3,
        "Dark": peta4,
    };

     L.control.layers(baseMaps).addTo(map);

    // Fetch markers from database
    fetch('/markers')
        .then(response => response.json())
        .then(data => {
            data.forEach(marker => {
                L.marker([marker.latitude, marker.longitude])
                    .addTo(map)
                    .bindPopup(`<b>${marker.name}</b><br>(${marker.latitude}, ${marker.longitude})`)
                    .on('dblclick', function () {
                        deleteMarker(marker.id);
                    });
                
                var table = document.getElementById('marker-table');
                var row = table.insertRow();
                row.insertCell(0).innerHTML = marker.name;
                row.insertCell(1).innerHTML = marker.latitude;
                row.insertCell(2).innerHTML = marker.longitude;
                row.insertCell(3).innerHTML = `<button onclick="deleteMarker(${marker.id})" class="btn btn-danger btn-sm">Hapus</button>`;
            });
        });

    function onMapClick(e) {
        var name = prompt("Masukkan Nama Lokasi:");
        if (name) {
            var newMarker = L.marker(e.latlng)
                .addTo(map)
                .bindPopup(`<b>${name}</b><br>(${e.latlng.lat}, ${e.latlng.lng})`)
                .openPopup();

            fetch('/markers', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    name: name,
                    latitude: e.latlng.lat,
                    longitude: e.latlng.lng
                })
            }).then(response => response.json())
              .then(data => location.reload());
        }
    }
    map.on('click', onMapClick);

    function deleteMarker(id) {
        if (confirm("Hapus marker ini?")) {
            fetch(`/markers/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(response => response.json())
              .then(data => location.reload());
        }
    }
</script>

@endsection
