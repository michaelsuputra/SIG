@extends('layouts.1home') 
@section('content')  

<div id="map" style="width: 100%; height: 500px; margin: auto;"></div>  

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

<!-- Tambahkan GoogleMutant.js -->
<script src="https://unpkg.com/leaflet.gridlayer.googlemutant@latest/dist/Leaflet.GoogleMutant.js"></script>

<script>    
    var map = L.map('map', {        
        center: [-8.795306484652961, 115.17586101733077], // Lokasi awal (Bali)        
        zoom: 10    
    });    

    const streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '&copy; <a href="https://www.esri.com">Esri</a>'
    });

    const hybridLayer = L.gridLayer.googleMutant({
        type: 'hybrid'
    });

    var baseMaps = {        
        "Streets": streetLayer,        
        "Satellite": satelliteLayer,        
        "Hybrid": hybridLayer    
    };        

    L.control.layers(baseMaps).addTo(map);    

    // Fetch markers from database    
    fetch('/markers')        
        .then(response => response.json())        
        .then(data => {            
            if (!Array.isArray(data)) return; // Pastikan data berbentuk array            
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
        }).catch(error => console.error('Error fetching markers:', error));    

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