<script>
(function() {
    function registerCoordinatePicker() {
        if (!window.Alpine || window.__coordinatePickerRegistered) return;
        window.__coordinatePickerRegistered = true;

        const compFactory = ({ lat, lng }) => ({
            lat: lat,
            lng: lng,
            tempLat: lat || '',
            tempLng: lng || '',
            showMapModal: false,
            isLoading: false,
            isLoadingGps: false,
            gpsNotice: '',
            accuracyCircle: null,
            searchQuery: '',
            mapId: 'map-picker-' + Math.random().toString(36).substring(2, 9),
            map: null,
            marker: null,

            openMapModal() {
                this.showMapModal = true;
                this.tempLat = this.lat || '';
                this.tempLng = this.lng || '';
                this.gpsNotice = '';
                this.$nextTick(() => {
                    this.initLeaflet();
                    setTimeout(() => {
                        if (this.map) this.map.invalidateSize();
                    }, 150);
                    setTimeout(() => {
                        if (this.map) this.map.invalidateSize();
                    }, 400);
                });
            },

            closeMapModal() {
                this.showMapModal = false;
            },

            applyLocation() {
                if (this.tempLat && this.tempLng) {
                    this.lat = this.tempLat;
                    this.lng = this.tempLng;
                }
                this.closeMapModal();
            },

            async copyCoordinate(val, label) {
                if (!val) {
                    alert(label + ' belum terisi.');
                    return;
                }
                try {
                    await navigator.clipboard.writeText(val);
                    alert(label + ' berhasil disalin: ' + val);
                } catch (e) {
                    const el = document.createElement('textarea');
                    el.value = val;
                    document.body.appendChild(el);
                    el.select();
                    document.execCommand('copy');
                    document.body.removeChild(el);
                    alert(label + ' berhasil disalin: ' + val);
                }
            },

            async pasteCoordinates(target) {
                try {
                    const text = await navigator.clipboard.readText();
                    const trimmed = text.trim();
                    
                    const pair = trimmed.match(/^([-+]?\d{1,2}(?:\.\d+)?)[,\s]+([-+]?\d{1,3}(?:\.\d+)?)$/);
                    if (pair) {
                        this.lat = parseFloat(pair[1]).toFixed(7);
                        this.lng = parseFloat(pair[2]).toFixed(7);
                        alert('Berhasil menempelkan Latitude (' + this.lat + ') dan Longitude (' + this.lng + ')!');
                        return;
                    }

                    const single = trimmed.match(/^[-+]?\d{1,3}(?:\.\d+)?$/);
                    if (single) {
                        if (target === 'lat') {
                            this.lat = parseFloat(single[0]).toFixed(7);
                        } else {
                            this.lng = parseFloat(single[0]).toFixed(7);
                        }
                        alert('Koordinat berhasil ditempel: ' + single[0]);
                    } else {
                        alert('Teks di clipboard bukan format koordinat yang valid: ' + trimmed);
                    }
                } catch (err) {
                    const fallback = prompt('Tempelkan koordinat di sini (contoh: -0.9345797, 100.226458):');
                    if (fallback) {
                        const pair = fallback.trim().match(/^([-+]?\d{1,2}(?:\.\d+)?)[,\s]+([-+]?\d{1,3}(?:\.\d+)?)$/);
                        if (pair) {
                            this.lat = parseFloat(pair[1]).toFixed(7);
                            this.lng = parseFloat(pair[2]).toFixed(7);
                        } else if (target === 'lat') {
                            this.lat = fallback.trim();
                        } else {
                            this.lng = fallback.trim();
                        }
                    }
                }
            },

            initLeaflet() {
                this.loadLeafletAssets(() => {
                    const defaultLat = parseFloat(this.tempLat) || -6.2088;
                    const defaultLng = parseFloat(this.tempLng) || 106.8456;
                    const zoom = (this.tempLat && this.tempLng) ? 16 : 13;

                    const mapEl = document.getElementById(this.mapId);
                    if (!mapEl) return;

                    if (this.map) {
                        this.map.remove();
                        this.map = null;
                    }

                    this.map = L.map(this.mapId).setView([defaultLat, defaultLng], zoom);

                    const googleHybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
                        maxZoom: 22,
                        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                        attribution: 'Google'
                    });

                    const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors',
                        maxZoom: 19
                    });

                    googleHybrid.addTo(this.map);
                    L.control.layers({ 'Satelit (Google)': googleHybrid, 'Street (OSM)': osm }).addTo(this.map);

                    const customIcon = L.icon({
                        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    });

                    this.marker = L.marker([defaultLat, defaultLng], {
                        draggable: true,
                        icon: customIcon
                    }).addTo(this.map);

                    if (!this.tempLat && !this.tempLng) {
                        this.tempLat = defaultLat.toFixed(7);
                        this.tempLng = defaultLng.toFixed(7);
                    }

                    this.marker.on('dragend', (e) => {
                        const pos = e.target.getLatLng();
                        this.tempLat = pos.lat.toFixed(7);
                        this.tempLng = pos.lng.toFixed(7);
                    });

                    this.map.on('click', (e) => {
                        const pos = e.latlng;
                        this.tempLat = pos.lat.toFixed(7);
                        this.tempLng = pos.lng.toFixed(7);
                        this.marker.setLatLng(pos);
                    });

                    window.pickNearbyOdp = (lat, lng) => {
                        this.tempLat = parseFloat(lat).toFixed(7);
                        this.tempLng = parseFloat(lng).toFixed(7);
                        this.marker.setLatLng([lat, lng]);
                    };

                    let firstValidOdp = null;
                    fetch('/map/odps')
                        .then(r => r.json())
                        .then(odps => {
                            if (!Array.isArray(odps)) return;
                            const odpIcon = L.divIcon({
                                html: '<div style="background:#10b981;border:2px solid #ffffff;border-radius:4px;width:20px;height:20px;display:flex;align-items:center;justify-content:center;color:#ffffff;font-size:10px;font-weight:bold;box-shadow:0 2px 6px rgba(0,0,0,0.5);">&#9632;</div>',
                                className: '',
                                iconSize: [20, 20],
                                iconAnchor: [10, 10]
                            });
                            odps.forEach(odp => {
                                const oLat = parseFloat(odp.latitude);
                                const oLng = parseFloat(odp.longitude);
                                if (isNaN(oLat) || isNaN(oLng)) return;
                                if (!firstValidOdp) firstValidOdp = [oLat, oLng];
                                const m = L.marker([oLat, oLng], { icon: odpIcon }).addTo(this.map);
                                m.bindPopup('<div style="min-width:140px;font-family:sans-serif;"><strong style="color:#0f172a;font-size:12px;">ODP: ' + (odp.name || '') + '</strong><br><span style="font-size:11px;color:#64748b;">' + (odp.code || '') + '</span><div style="margin-top:6px;"><button type="button" style="padding:4px 8px;background:#4f46e5;color:#fff;border:none;border-radius:4px;font-size:11px;cursor:pointer;font-weight:600;" onclick="window.pickNearbyOdp(' + oLat + ', ' + oLng + ')">Pilih Dekat Sini</button></div></div>');
                            });

                            if ((!this.lat || !this.lng) && firstValidOdp && defaultLat === -6.2088) {
                                this.tempLat = firstValidOdp[0].toFixed(7);
                                this.tempLng = firstValidOdp[1].toFixed(7);
                                this.marker.setLatLng(firstValidOdp);
                                this.map.setView(firstValidOdp, 15);
                            }
                        })
                        .catch(() => {});

                    setTimeout(() => {
                        this.map.invalidateSize();
                    }, 300);
                });
            },

            loadLeafletAssets(cb) {
                if (window.L) {
                    cb();
                    return;
                }
                this.isLoading = true;
                if (!document.getElementById('leaflet-css')) {
                    const link = document.createElement('link');
                    link.id = 'leaflet-css';
                    link.rel = 'stylesheet';
                    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    document.head.appendChild(link);
                }

                if (!document.getElementById('leaflet-js')) {
                    const script = document.createElement('script');
                    script.id = 'leaflet-js';
                    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                    script.onload = () => {
                        this.isLoading = false;
                        cb();
                    };
                    document.head.appendChild(script);
                } else {
                    const poll = setInterval(() => {
                        if (window.L) {
                            clearInterval(poll);
                            this.isLoading = false;
                            cb();
                        }
                    }, 50);
                }
            },

            useCurrentLocation() {
                if (!navigator.geolocation) {
                    alert('Browser Anda tidak mendukung Geolocation.');
                    return;
                }
                this.isLoadingGps = true;
                this.gpsNotice = 'Sedang mendeteksi posisi GPS perangkat...';

                const handleSuccess = (pos) => {
                    this.isLoadingGps = false;
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    const accuracy = pos.coords.accuracy || 0;

                    this.tempLat = lat.toFixed(7);
                    this.tempLng = lng.toFixed(7);

                    if (!this.showMapModal) {
                        this.lat = this.tempLat;
                        this.lng = this.tempLng;
                    }

                    if (this.accuracyCircle && this.map) {
                        this.map.removeLayer(this.accuracyCircle);
                        this.accuracyCircle = null;
                    }

                    if (this.map && this.marker) {
                        this.marker.setLatLng([lat, lng]);

                        if (accuracy > 0 && accuracy < 100000) {
                            this.accuracyCircle = L.circle([lat, lng], {
                                radius: accuracy,
                                color: '#4f46e5',
                                fillColor: '#818cf8',
                                fillOpacity: 0.15,
                                weight: 1.5
                            }).addTo(this.map);
                        }

                        if (accuracy < 100) {
                            this.map.setView([lat, lng], 18);
                            this.gpsNotice = `<strong>Akurasi GPS Tinggi (±${Math.round(accuracy)}m).</strong> Titik koordinat berhasil diterapkan.`;
                        } else if (accuracy < 2500) {
                            this.map.setView([lat, lng], 16);
                            this.gpsNotice = `<strong>Akurasi ±${Math.round(accuracy)}m.</strong> Silakan geser pin di peta ke posisi tiang/kantor yang persis.`;
                        } else {
                            this.map.setView([lat, lng], 13);
                            this.gpsNotice = `<strong>Info:</strong> Lokasi terdeteksi dari IP Internet provider (estimasi ±${Math.round(accuracy / 1000)} km). <strong>Silakan ketik nama desa/alamat di kotak cari atau geser pin ke lokasi sebenarnya.</strong>`;
                        }
                    }
                };

                const handleError = (err) => {
                    navigator.geolocation.getCurrentPosition(
                        handleSuccess,
                        (errFallback) => {
                            this.isLoadingGps = false;
                            if (errFallback.code === 1) {
                                this.gpsNotice = '<strong>Izin Lokasi Ditolak:</strong> Izinkan akses lokasi pada ikon setelan/gembok di address bar browser Anda.';
                            } else {
                                this.gpsNotice = '<strong>Lokasi Tidak Terdeteksi:</strong> ' + (errFallback.message || err.message) + '. Silakan ketik nama daerah/desa di kotak pencarian atau tempel titik koordinat.';
                            }
                            if (!this.showMapModal) {
                                alert(errFallback.code === 1 ? 'Izin akses lokasi GPS ditolak oleh browser Anda. Mohon izinkan akses lokasi pada pengaturan browser.' : 'Gagal mendeteksi lokasi GPS: ' + (errFallback.message || err.message));
                            }
                        },
                        { enableHighAccuracy: false, timeout: 8000, maximumAge: 300000 }
                    );
                };

                navigator.geolocation.getCurrentPosition(
                    handleSuccess,
                    handleError,
                    { enableHighAccuracy: true, timeout: 5000, maximumAge: 60000 }
                );
            },

            async searchLocation() {
                const query = (this.searchQuery || '').trim();
                if (!query) return;

                const coordRegex = /([-+]?\d{1,2}(?:\.\d+)?)[,\s]+([-+]?\d{1,3}(?:\.\d+)?)/;
                const coordMatch = query.match(coordRegex);
                if (coordMatch) {
                    const lat = parseFloat(coordMatch[1]);
                    const lng = parseFloat(coordMatch[2]);
                    if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                        this.tempLat = lat.toFixed(7);
                        this.tempLng = lng.toFixed(7);
                        if (this.map && this.marker) {
                            this.marker.setLatLng([lat, lng]);
                            this.map.setView([lat, lng], 17);
                        }
                        this.gpsNotice = `Peta diarahkan ke koordinat: <strong>${this.tempLat}, ${this.tempLng}</strong>`;
                        return;
                    }
                }

                this.isLoading = true;
                this.gpsNotice = '';
                try {
                    let res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&countrycodes=id&limit=5&q=${encodeURIComponent(query)}`);
                    let data = await res.json();
                    
                    if (!data || data.length === 0) {
                        res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=5&q=${encodeURIComponent(query)}`);
                        data = await res.json();
                    }

                    this.isLoading = false;
                    if (data && data.length > 0) {
                        const first = data[0];
                        const lat = parseFloat(first.lat);
                        const lng = parseFloat(first.lon);
                        this.tempLat = lat.toFixed(7);
                        this.tempLng = lng.toFixed(7);
                        if (this.map && this.marker) {
                            this.marker.setLatLng([lat, lng]);
                            this.map.setView([lat, lng], 16);
                        }
                        this.gpsNotice = `Ditemukan: <strong>${first.display_name.split(',').slice(0, 3).join(', ')}</strong>`;
                    } else {
                        alert('Lokasi tidak ditemukan. Coba ketik nama desa/kelurahan, jalan, kota, atau tempel koordinat lat, lng.');
                    }
                } catch (e) {
                    this.isLoading = false;
                    alert('Gagal mencari lokasi: ' + e.message);
                }
            }
        });

        Alpine.data('coordinatePicker', compFactory);
        window.coordinatePicker = compFactory;
    }

    if (window.Alpine) {
        registerCoordinatePicker();
    } else {
        document.addEventListener('alpine:init', registerCoordinatePicker);
    }
    document.addEventListener('DOMContentLoaded', registerCoordinatePicker);
    document.addEventListener('livewire:navigated', registerCoordinatePicker);
    document.addEventListener('livewire:init', registerCoordinatePicker);
})();
</script>
