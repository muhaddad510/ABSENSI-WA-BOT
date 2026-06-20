@extends('adminlte::page')

@section('title', 'Lokasi Mengajar')

@section('content_header')
    <h1>Lokasi Mengajar</h1>
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />

    <style>
        #map {
            height: 450px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
        }

        .leaflet-control-geocoder {
            border-radius: 6px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.25);
        }
    </style>
@stop

@section('content')
    {{-- ALERT SUCCESS --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- ALERT ERROR VALIDATION --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i>
            <b>Gagal menyimpan lokasi!</b>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Koordinat Lokasi Mengajar</h3>
                </div>

                <form action="{{ route('teaching-location.store') }}" method="POST">
                    @csrf

                    <div class="card-body">
                        <div class="form-group">
                            <label>Latitude</label>
                            <input
                                type="text"
                                id="lat_display"
                                class="form-control"
                                value="{{ $loc->latitude ?? -6.200000 }}"
                                readonly
                            >
                            <input
                                type="hidden"
                                id="lat"
                                name="latitude"
                                value="{{ $loc->latitude ?? -6.200000 }}"
                            >
                        </div>

                        <div class="form-group">
                            <label>Longitude</label>
                            <input
                                type="text"
                                id="lng_display"
                                class="form-control"
                                value="{{ $loc->longitude ?? 106.816666 }}"
                                readonly
                            >
                            <input
                                type="hidden"
                                id="lng"
                                name="longitude"
                                value="{{ $loc->longitude ?? 106.816666 }}"
                            >
                        </div>

                        <div class="form-group">
                            <label>Radius Absensi (meter)</label>
                            <input
                                type="number"
                                id="radius_display"
                                class="form-control"
                                value="{{ $loc->radius_m ?? 200 }}"
                                min="1"
                            >
                            <input
                                type="hidden"
                                id="radius"
                                name="radius_m"
                                value="{{ $loc->radius_m ?? 200 }}"
                            >
                            <small class="text-muted d-block mt-1">
                                Default 200m. Mahasiswa dianggap hadir kalau jarak <= radius.
                            </small>
                        </div>

                        <small class="text-muted d-block">
                            <i class="fas fa-info-circle"></i>
                            Klik peta / geser marker untuk ubah lokasi.
                        </small>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-save"></i> Simpan Lokasi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-body p-2">
                    <div id="map"></div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const initialLat = {{ $loc->latitude ?? -6.200000 }};
            const initialLng = {{ $loc->longitude ?? 106.816666 }};
            const initialRadius = {{ $loc->radius_m ?? 200 }};

            const map = L.map('map').setView([initialLat, initialLng], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);

            // Circle radius
            let circle = L.circle([initialLat, initialLng], {
                radius: initialRadius
            }).addTo(map);

            function updateInput(lat, lng) {
                document.getElementById('lat_display').value = lat.toFixed(6);
                document.getElementById('lng_display').value = lng.toFixed(6);

                document.getElementById('lat').value = lat.toFixed(6);
                document.getElementById('lng').value = lng.toFixed(6);

                // update circle center
                circle.setLatLng([lat, lng]);
            }

            function updateRadius(r) {
                const radiusVal = parseInt(r || 200);

                document.getElementById('radius_display').value = radiusVal;
                document.getElementById('radius').value = radiusVal;

                circle.setRadius(radiusVal);
            }

            marker.on('dragend', function () {
                const pos = marker.getLatLng();
                updateInput(pos.lat, pos.lng);
            });

            map.on('click', function (e) {
                marker.setLatLng(e.latlng);
                updateInput(e.latlng.lat, e.latlng.lng);
            });

            document.getElementById('radius_display').addEventListener('input', function (e) {
                updateRadius(e.target.value);
            });

            L.Control.geocoder({
                defaultMarkGeocode: false,
                placeholder: "Cari lokasi...",
                errorMessage: "Lokasi tidak ditemukan."
            })
            .on('markgeocode', function (e) {
                const center = e.geocode.center;
                marker.setLatLng(center);
                map.setView(center, 17);
                updateInput(center.lat, center.lng);
            })
            .addTo(map);

            // init
            updateRadius(initialRadius);
        });
    </script>
@stop
