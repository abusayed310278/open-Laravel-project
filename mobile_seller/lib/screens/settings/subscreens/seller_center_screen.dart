import 'dart:math';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/network/api_client.dart';
import '../../../core/network/api_config.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_spacing.dart';
import '../../../core/widgets/app_snackbar.dart';
import '../../../providers/seller_auth_provider.dart';
import 'inspection_queue_screen.dart';

enum MapLayerStyle {
  voyager,
  osm,
  satellite,
}

class SellerCenterPoint {
  final String id;
  final String name;
  final String type;
  final String category; // 'drop_off', 'inspection', 'warehouse', 'express', 'store'
  final double latitude;
  final double longitude;
  final String address;
  final String city;
  final String phone;
  final String email;
  final String hours;
  final List<String> services;
  final bool isUserStore;

  const SellerCenterPoint({
    required this.id,
    required this.name,
    required this.type,
    required this.category,
    required this.latitude,
    required this.longitude,
    required this.address,
    required this.city,
    required this.phone,
    required this.email,
    required this.hours,
    required this.services,
    this.isUserStore = false,
  });
}

class SellerCenterScreen extends StatefulWidget {
  const SellerCenterScreen({super.key});

  @override
  State<SellerCenterScreen> createState() => _SellerCenterScreenState();
}

class _SellerCenterScreenState extends State<SellerCenterScreen> {
  final ApiClient _apiClient = ApiClient();
  final MapLayerStyle _currentLayer = MapLayerStyle.voyager;
  MapType _currentMapType = MapType.normal;
  GoogleMapController? _mapController;
  String _googleMapsApiKey = ApiConfig.googleMapsApiKey;
  bool _isBackendKeyLoaded = false;

  bool _isLoading = true;
  String _selectedCategory = 'all';
  String _searchQuery = '';
  SellerCenterPoint? _selectedPoint;
  final TextEditingController _searchController = TextEditingController();

  List<SellerCenterPoint> _allCenters = [];

  // Map state
  double _centerLat = 25.2950;
  double _centerLng = 51.5200;
  double _zoom = 11.5;

  // Drag tracking
  Offset? _lastPanFocalPoint;
  double _startZoom = 11.5;

  @override
  void initState() {
    super.initState();
    _loadCenterLocations();
  }

  @override
  void dispose() {
    _searchController.dispose();
    _mapController?.dispose();
    super.dispose();
  }

  Future<void> _loadCenterLocations() async {
    setState(() => _isLoading = true);

    // Default official Openbox Seller Centers, Drop-off Hubs and Warehouses in Qatar
    final List<SellerCenterPoint> centers = [
      const SellerCenterPoint(
        id: 'center_1',
        name: 'Doha Main Verification & Seller Center',
        type: 'Inspection & Drop-off Hub',
        category: 'inspection',
        latitude: 25.3218,
        longitude: 51.5290,
        address: 'Street 100, West Bay Commercial District',
        city: 'Doha',
        phone: '+974 4411 2233',
        email: 'inspection.doha@openbox.com',
        hours: 'Mon - Sat: 8:00 AM - 6:00 PM',
        services: [
          'Hardware Verification',
          'Instant Grading SLA',
          'Express Drop-off',
          'Merchant Onboarding',
        ],
      ),
      const SellerCenterPoint(
        id: 'center_2',
        name: 'Al Rayyan Quality Inspection Center',
        type: 'Inspection Hub & Logistics',
        category: 'inspection',
        latitude: 25.2919,
        longitude: 51.4244,
        address: 'Main Commercial Street, Gate 2',
        city: 'Al Rayyan',
        phone: '+974 4422 3344',
        email: 'inspection.rayyan@openbox.com',
        hours: 'Sun - Thu: 9:00 AM - 7:00 PM',
        services: [
          'Screen & Battery Check',
          'Drop-off Reception',
          'Consignment Storage',
        ],
      ),
      const SellerCenterPoint(
        id: 'center_3',
        name: 'Al Wakra Device Testing & Drop-off Facility',
        type: 'Drop-off & Testing Station',
        category: 'drop_off',
        latitude: 25.1768,
        longitude: 51.6034,
        address: 'Al Wakra Coastal Road, Building 18',
        city: 'Al Wakra',
        phone: '+974 4433 4455',
        email: 'inspection.wakra@openbox.com',
        hours: 'Mon - Sat: 9:00 AM - 5:00 PM',
        services: [
          'Parcel Drop-off',
          'Cosmetic Inspection',
          'Order Return Desk',
        ],
      ),
      const SellerCenterPoint(
        id: 'center_4',
        name: 'Industrial Area Central Warehouse & Bulk Depot',
        type: 'Primary Logistics Hub',
        category: 'warehouse',
        latitude: 25.2014,
        longitude: 51.4532,
        address: 'Street 38, Industrial Area Industrial Zone',
        city: 'Industrial Area',
        phone: '+974 4444 5566',
        email: 'warehouse.hub@openbox.com',
        hours: 'Sat - Thu: 7:00 AM - 8:00 PM',
        services: [
          'Bulk Consignment Ingestion',
          'Secure Vault Storage',
          'Fulfillment Packaging',
          'Courier Dispatch Hub',
        ],
      ),
      const SellerCenterPoint(
        id: 'center_5',
        name: 'The Pearl Qatar Express Drop-off Kiosk',
        type: 'Express Drop Station',
        category: 'express',
        latitude: 25.3713,
        longitude: 51.5458,
        address: 'Porto Arabia Boardwalk, Tower 12 Ground Floor',
        city: 'The Pearl',
        phone: '+974 4455 6677',
        email: 'express.pearl@openbox.com',
        hours: 'Daily: 10:00 AM - 10:00 PM',
        services: [
          'Quick 2-Minute Drop',
          'Barcode Scanning',
          'Receipt Handover',
        ],
      ),
      const SellerCenterPoint(
        id: 'center_6',
        name: 'Lusail City Drop & Seller Support Center',
        type: 'Smart Hub & Consultation',
        category: 'inspection',
        latitude: 25.4215,
        longitude: 51.5012,
        address: 'Marina Promenade Building, Lusail Marina',
        city: 'Lusail',
        phone: '+974 4466 7788',
        email: 'lusail.hub@openbox.com',
        hours: 'Sun - Thu: 8:30 AM - 6:30 PM',
        services: [
          'Fast Inspection',
          'Seller Account Support',
          'Device Photography Booth',
        ],
      ),
    ];

    try {
      final res = await _apiClient.get('/warehouse-deposits');
      if (res is Map<String, dynamic> && res['warehouses'] is List) {
        final list = res['warehouses'] as List;
        for (var i = 0; i < list.length; i++) {
          final wh = list[i];
          if (wh is Map<String, dynamic> && wh['name'] != null) {
            centers.add(
              SellerCenterPoint(
                id: 'api_wh_${wh['id'] ?? i}',
                name: wh['name'].toString(),
                type: 'Fulfillment & Deposit Warehouse',
                category: 'warehouse',
                latitude: 25.2500 + (i * 0.02),
                longitude: 51.4800 + (i * 0.015),
                address: wh['address']?.toString() ?? 'Openbox Logistics Center',
                city: 'Doha',
                phone: '+974 4400 1100',
                email: 'operations@openbox.com',
                hours: 'Sun - Thu: 8:00 AM - 5:00 PM',
                services: ['Consignment Ingestion', 'Inventory Storage'],
              ),
            );
          }
        }
      }
    } catch (_) {}

    // Fetch dynamic Google Maps API key from backend
    try {
      final configRes = await _apiClient.get('/location/config');
      if (configRes is Map<String, dynamic> && configRes['google_maps_api_key'] != null) {
        final key = configRes['google_maps_api_key'].toString().trim();
        if (key.isNotEmpty) {
          _googleMapsApiKey = key;
          ApiConfig.googleMapsApiKey = key;
          _isBackendKeyLoaded = true;
        }
      }
    } catch (_) {
      try {
        final mapRes = await _apiClient.get('/map-config');
        if (mapRes is Map<String, dynamic> && mapRes['google_maps_api_key'] != null) {
          final key = mapRes['google_maps_api_key'].toString().trim();
          if (key.isNotEmpty) {
            _googleMapsApiKey = key;
            ApiConfig.googleMapsApiKey = key;
            _isBackendKeyLoaded = true;
          }
        }
      } catch (_) {}
    }

    if (!mounted) return;

    // Include the merchant's registered store if available
    final user = context.read<SellerAuthProvider>().user;
    if (user != null && user.latitude != null && user.longitude != null) {
      centers.insert(
        0,
        SellerCenterPoint(
          id: 'user_store',
          name: '${user.storeName} (My Store)',
          type: 'Your Registered Storefront',
          category: 'store',
          latitude: user.latitude!,
          longitude: user.longitude!,
          address: user.address ?? user.location ?? 'Store Address',
          city: user.city ?? 'Doha',
          phone: user.phone ?? '',
          email: user.email,
          hours: 'Open Business Hours',
          services: ['Active Merchant Hub', 'Catalog Dispatch Location'],
          isUserStore: true,
        ),
      );
    }

    if (mounted) {
      setState(() {
        _allCenters = centers;
        _selectedPoint = centers.first;
        _centerLat = centers.first.latitude;
        _centerLng = centers.first.longitude;
        _isLoading = false;
      });
    }
  }

  List<SellerCenterPoint> get _filteredCenters {
    return _allCenters.where((center) {
      final matchesCategory =
          _selectedCategory == 'all' || center.category == _selectedCategory;
      final q = _searchQuery.trim().toLowerCase();
      final matchesSearch = q.isEmpty ||
          center.name.toLowerCase().contains(q) ||
          center.address.toLowerCase().contains(q) ||
          center.city.toLowerCase().contains(q) ||
          center.type.toLowerCase().contains(q);
      return matchesCategory && matchesSearch;
    }).toList();
  }

  void _animateToCenter(SellerCenterPoint center) {
    setState(() {
      _selectedPoint = center;
      _centerLat = center.latitude;
      _centerLng = center.longitude;
      _zoom = 14.5;
    });
    _mapController?.animateCamera(
      CameraUpdate.newCameraPosition(
        CameraPosition(
          target: LatLng(center.latitude, center.longitude),
          zoom: 14.5,
        ),
      ),
    );
  }

  void _fitAllMarkers() {
    final filtered = _filteredCenters;
    if (filtered.isEmpty) return;

    if (filtered.length == 1) {
      _animateToCenter(filtered.first);
      return;
    }

    double minLat = filtered.first.latitude;
    double maxLat = filtered.first.latitude;
    double minLng = filtered.first.longitude;
    double maxLng = filtered.first.longitude;

    for (final c in filtered) {
      if (c.latitude < minLat) minLat = c.latitude;
      if (c.latitude > maxLat) maxLat = c.latitude;
      if (c.longitude < minLng) minLng = c.longitude;
      if (c.longitude > maxLng) maxLng = c.longitude;
    }

    setState(() {
      _centerLat = (minLat + maxLat) / 2.0;
      _centerLng = (minLng + maxLng) / 2.0;
      _zoom = 11.5;
    });

    _mapController?.animateCamera(
      CameraUpdate.newLatLngBounds(
        LatLngBounds(
          southwest: LatLng(minLat - 0.03, minLng - 0.03),
          northeast: LatLng(maxLat + 0.03, maxLng + 0.03),
        ),
        50.0,
      ),
    );
  }

  Set<Marker> _buildGoogleMapMarkers(List<SellerCenterPoint> centers) {
    return centers.map((center) {
      final isSelected = _selectedPoint?.id == center.id;
      final double hue;
      if (center.isUserStore) {
        hue = BitmapDescriptor.hueAzure;
      } else if (center.category == 'inspection') {
        hue = BitmapDescriptor.hueRed;
      } else if (center.category == 'drop_off') {
        hue = BitmapDescriptor.hueOrange;
      } else if (center.category == 'warehouse') {
        hue = BitmapDescriptor.hueViolet;
      } else {
        hue = BitmapDescriptor.hueGreen;
      }

      return Marker(
        markerId: MarkerId(center.id),
        position: LatLng(center.latitude, center.longitude),
        icon: BitmapDescriptor.defaultMarkerWithHue(hue),
        infoWindow: InfoWindow(
          title: center.name,
          snippet: '${center.type} • ${center.city}',
          onTap: () {
            setState(() => _selectedPoint = center);
          },
        ),
        zIndexInt: isSelected ? 3 : 1,
        onTap: () {
          setState(() {
            _selectedPoint = center;
            _centerLat = center.latitude;
            _centerLng = center.longitude;
          });
        },
      );
    }).toSet();
  }

  bool get _useNativeGoogleMap =>
      kIsWeb ||
      defaultTargetPlatform == TargetPlatform.android ||
      defaultTargetPlatform == TargetPlatform.iOS;

  Widget _buildMapView(List<SellerCenterPoint> centers) {
    if (_useNativeGoogleMap) {
      return GoogleMap(
        initialCameraPosition: CameraPosition(
          target: LatLng(_centerLat, _centerLng),
          zoom: _zoom,
        ),
        mapType: _currentMapType,
        markers: _buildGoogleMapMarkers(centers),
        onMapCreated: (controller) {
          _mapController = controller;
        },
        myLocationButtonEnabled: false,
        zoomControlsEnabled: false,
        compassEnabled: true,
        mapToolbarEnabled: false,
      );
    }

    return _buildInteractiveTileMap(centers);
  }

  Future<void> _openGoogleMapsNavigation(SellerCenterPoint point) async {
    final uri = Uri.parse(
        'https://www.google.com/maps/dir/?api=1&destination=${point.latitude},${point.longitude}&destination_name=${Uri.encodeComponent(point.name)}');
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        if (mounted) {
          AppSnackBar.error(context, 'Could not open Google Maps navigation.');
        }
      }
    } catch (_) {
      if (mounted) {
        AppSnackBar.error(context, 'Unable to open maps app.');
      }
    }
  }

  Future<void> _callPhone(String phone) async {
    final cleanPhone = phone.replaceAll(RegExp(r'\s+'), '');
    final uri = Uri.parse('tel:$cleanPhone');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    } else {
      if (mounted) {
        AppSnackBar.warning(context, 'Call not supported on this device.');
      }
    }
  }

  // Mercator Map Math
  double _lonToPixelX(double lon, double zoom) {
    return (lon + 180.0) / 360.0 * (256.0 * pow(2.0, zoom));
  }

  double _latToPixelY(double lat, double zoom) {
    final sinLat = sin(lat * pi / 180.0).clamp(-0.9999, 0.9999);
    return (0.5 - log((1.0 + sinLat) / (1.0 - sinLat)) / (4.0 * pi)) *
        (256.0 * pow(2.0, zoom));
  }

  double _pixelXToLon(double px, double zoom) {
    return px / (256.0 * pow(2.0, zoom)) * 360.0 - 180.0;
  }

  double _pixelYToLat(double py, double zoom) {
    final yNorm = 0.5 - py / (256.0 * pow(2.0, zoom));
    final n = 2.0 * pi * yNorm;
    return 180.0 / pi * atan(0.5 * (exp(n) - exp(-n)));
  }

  String _getTileUrl(int z, int x, int y) {
    final maxCoord = (1 << z);
    final safeX = (x % maxCoord + maxCoord) % maxCoord;
    final safeY = y.clamp(0, maxCoord - 1);

    switch (_currentLayer) {
      case MapLayerStyle.voyager:
        final subdomains = ['a', 'b', 'c', 'd'];
        final sub = subdomains[(safeX + safeY) % subdomains.length];
        return 'https://$sub.basemaps.cartocdn.com/rastertiles/voyager/$z/$safeX/$safeY.png';
      case MapLayerStyle.osm:
        return 'https://tile.openstreetmap.org/$z/$safeX/$safeY.png';
      case MapLayerStyle.satellite:
        return 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/$z/$safeY/$safeX';
    }
  }

  @override
  Widget build(BuildContext context) {
    final filtered = _filteredCenters;

    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Seller Center',
              style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w800,
                  color: AppColors.ink),
            ),
            Text(
              'Certified drop-off hubs, verification & warehouses',
              style: TextStyle(
                  fontSize: 11,
                  color: AppColors.slate500,
                  fontWeight: FontWeight.w400),
            ),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'Fit All Pins',
            icon: const Icon(Icons.crop_free_rounded, color: AppColors.brand600),
            onPressed: _fitAllMarkers,
          ),
          PopupMenuButton<MapType>(
            tooltip: 'Google Maps Layer',
            icon: const Icon(Icons.layers_outlined, color: AppColors.brand600),
            onSelected: (type) => setState(() => _currentMapType = type),
            itemBuilder: (_) => const [
              PopupMenuItem(
                value: MapType.normal,
                child: Text('Google Normal Map'),
              ),
              PopupMenuItem(
                value: MapType.satellite,
                child: Text('Google Satellite'),
              ),
              PopupMenuItem(
                value: MapType.hybrid,
                child: Text('Google Hybrid'),
              ),
              PopupMenuItem(
                value: MapType.terrain,
                child: Text('Google Terrain'),
              ),
            ],
          ),
        ],
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: AppColors.brand500))
          : Column(
              children: [
                // 1. Search Bar & Category Filter Strip
                _buildSearchAndFilters(),

                // 2. Interactive Google Map Canvas
                Expanded(
                  child: Stack(
                    children: [
                      _buildMapView(filtered),

                      // Floating Quick Action Buttons on Map
                      Positioned(
                        right: 14,
                        top: 14,
                        child: Column(
                          children: [
                            _buildFloatingMapButton(
                              icon: Icons.add_rounded,
                              tooltip: 'Zoom In',
                              onTap: () {
                                setState(() {
                                  _zoom = (_zoom + 0.75).clamp(8.0, 18.0);
                                });
                                _mapController?.animateCamera(CameraUpdate.zoomIn());
                              },
                            ),
                            const SizedBox(height: 8),
                            _buildFloatingMapButton(
                              icon: Icons.remove_rounded,
                              tooltip: 'Zoom Out',
                              onTap: () {
                                setState(() {
                                  _zoom = (_zoom - 0.75).clamp(8.0, 18.0);
                                });
                                _mapController?.animateCamera(CameraUpdate.zoomOut());
                              },
                            ),
                            const SizedBox(height: 8),
                            _buildFloatingMapButton(
                              icon: Icons.my_location_rounded,
                              tooltip: 'Center on Hubs',
                              onTap: () {
                                if (filtered.isNotEmpty) {
                                  _animateToCenter(filtered.first);
                                }
                              },
                            ),
                          ],
                        ),
                      ),

                      // Floating Points Found Badge
                      Positioned(
                        left: 14,
                        top: 14,
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(AppRadius.pill),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.1),
                                blurRadius: 8,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.place_rounded,
                                  size: 14, color: AppColors.brand600),
                              const SizedBox(width: 4),
                              Text(
                                '${filtered.length} Centers Found',
                                style: const TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w700,
                                  color: AppColors.ink,
                                ),
                              ),
                              if (_isBackendKeyLoaded) ...[
                                const SizedBox(width: 6),
                                Tooltip(
                                  message: 'Google Maps API: ${_googleMapsApiKey.isNotEmpty ? "${_googleMapsApiKey.substring(0, min(10, _googleMapsApiKey.length))}..." : "Connected"}',
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                                    decoration: BoxDecoration(
                                      color: AppColors.successBg,
                                      borderRadius: BorderRadius.circular(AppRadius.pill),
                                    ),
                                    child: const Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Icon(Icons.check_circle_rounded, size: 10, color: AppColors.success),
                                        SizedBox(width: 2),
                                        Text(
                                          'Google Maps',
                                          style: TextStyle(
                                            fontSize: 9,
                                            fontWeight: FontWeight.w700,
                                            color: AppColors.success,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ),
                      ),

                      // Floating Bottom Center Card
                      if (_selectedPoint != null)
                        Positioned(
                          left: 12,
                          right: 12,
                          bottom: 12,
                          child: _buildSelectedCenterCard(_selectedPoint!),
                        ),
                    ],
                  ),
                ),

                // 3. Bottom Horizontal Center List Selector
                _buildBottomCentersList(filtered),
              ],
            ),
    );
  }

  Widget _buildInteractiveTileMap(List<SellerCenterPoint> centers) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final screenWidth = constraints.maxWidth;
        final screenHeight = constraints.maxHeight;

        final currentZoom = _zoom;
        final intZoom = currentZoom.floor().clamp(1, 18);
        final scaleFactor = pow(2.0, currentZoom - intZoom).toDouble();
        final tileSize = 256.0 * scaleFactor;

        final centerPixelX = _lonToPixelX(_centerLng, currentZoom);
        final centerPixelY = _latToPixelY(_centerLat, currentZoom);

        final topLeftX = centerPixelX - screenWidth / 2.0;
        final topLeftY = centerPixelY - screenHeight / 2.0;

        final minTileX = (topLeftX / tileSize).floor();
        final maxTileX = ((topLeftX + screenWidth) / tileSize).ceil();
        final minTileY = (topLeftY / tileSize).floor();
        final maxTileY = ((topLeftY + screenHeight) / tileSize).ceil();

        final List<Widget> tileWidgets = [];

        for (var tx = minTileX; tx <= maxTileX; tx++) {
          for (var ty = minTileY; ty <= maxTileY; ty++) {
            final posX = (tx * tileSize) - topLeftX;
            final posY = (ty * tileSize) - topLeftY;
            final url = _getTileUrl(intZoom, tx, ty);

            tileWidgets.add(
              Positioned(
                left: posX,
                top: posY,
                width: tileSize + 0.5,
                height: tileSize + 0.5,
                child: Image.network(
                  url,
                  fit: BoxFit.cover,
                  errorBuilder: (context, error, stackTrace) => Container(
                    color: AppColors.slate100,
                    alignment: Alignment.center,
                    child: const Icon(Icons.broken_image_outlined,
                        size: 16, color: AppColors.slate300),
                  ),
                ),
              ),
            );
          }
        }

        // Overlay marker pins
        final List<Widget> markerWidgets = [];
        for (final center in centers) {
          final markerPixelX = _lonToPixelX(center.longitude, currentZoom);
          final markerPixelY = _latToPixelY(center.latitude, currentZoom);

          final screenX = markerPixelX - topLeftX;
          final screenY = markerPixelY - topLeftY;

          // Check if pin is roughly visible
          if (screenX >= -50 &&
              screenX <= screenWidth + 50 &&
              screenY >= -50 &&
              screenY <= screenHeight + 50) {
            final isSelected = _selectedPoint?.id == center.id;

            markerWidgets.add(
              Positioned(
                left: screenX - 22,
                top: screenY - 44,
                child: GestureDetector(
                  onTap: () {
                    setState(() {
                      _selectedPoint = center;
                      _centerLat = center.latitude;
                      _centerLng = center.longitude;
                    });
                  },
                  child: _buildPinWidget(center, isSelected),
                ),
              ),
            );
          }
        }

        return GestureDetector(
          onScaleStart: (details) {
            _lastPanFocalPoint = details.focalPoint;
            _startZoom = _zoom;
          },
          onScaleUpdate: (details) {
            setState(() {
              if (details.scale != 1.0) {
                // Pinch zoom
                _zoom = (_startZoom + log(details.scale) / log(2))
                    .clamp(8.0, 18.0);
              }

              if (_lastPanFocalPoint != null) {
                final delta = details.focalPoint - _lastPanFocalPoint!;
                _lastPanFocalPoint = details.focalPoint;

                final currentCenterX = _lonToPixelX(_centerLng, _zoom);
                final currentCenterY = _latToPixelY(_centerLat, _zoom);

                final newCenterX = currentCenterX - delta.dx;
                final newCenterY = currentCenterY - delta.dy;

                _centerLng = _pixelXToLon(newCenterX, _zoom);
                _centerLat = _pixelYToLat(newCenterY, _zoom);
              }
            });
          },
          child: Container(
            decoration: const BoxDecoration(color: AppColors.slate200),
            clipBehavior: Clip.hardEdge,
            child: Stack(
              children: [
                ...tileWidgets,
                ...markerWidgets,
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildPinWidget(SellerCenterPoint center, bool isSelected) {
    Color pinColor = AppColors.brand500;
    IconData pinIcon = Icons.verified_rounded;

    if (center.isUserStore) {
      pinColor = AppColors.success;
      pinIcon = Icons.storefront_rounded;
    } else if (center.category == 'warehouse') {
      pinColor = AppColors.purple;
      pinIcon = Icons.warehouse_rounded;
    } else if (center.category == 'express') {
      pinColor = AppColors.info;
      pinIcon = Icons.bolt_rounded;
    } else if (center.category == 'drop_off') {
      pinColor = AppColors.warning;
      pinIcon = Icons.inventory_2_rounded;
    }

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        if (isSelected)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
            margin: const EdgeInsets.only(bottom: 2),
            decoration: BoxDecoration(
              color: AppColors.ink,
              borderRadius: BorderRadius.circular(4),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.2),
                  blurRadius: 4,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Text(
              center.name,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 9,
                fontWeight: FontWeight.w700,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
        Stack(
          alignment: Alignment.center,
          children: [
            if (isSelected)
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: pinColor.withValues(alpha: 0.25),
                  shape: BoxShape.circle,
                ),
              ),
            Container(
              width: isSelected ? 36 : 30,
              height: isSelected ? 36 : 30,
              decoration: BoxDecoration(
                color: pinColor,
                shape: BoxShape.circle,
                border: Border.all(color: Colors.white, width: 2),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.25),
                    blurRadius: 6,
                    offset: const Offset(0, 3),
                  ),
                ],
              ),
              child: Icon(pinIcon, color: Colors.white, size: isSelected ? 18 : 15),
            ),
          ],
        ),
        CustomPaint(
          size: const Size(8, 5),
          painter: _TrianglePainter(color: pinColor),
        ),
      ],
    );
  }

  Widget _buildSearchAndFilters() {
    return Container(
      color: AppColors.surface,
      padding: const EdgeInsets.fromLTRB(
          AppSpacing.md, AppSpacing.sm, AppSpacing.md, AppSpacing.sm),
      child: Column(
        children: [
          // Search TextField
          Container(
            height: 40,
            decoration: BoxDecoration(
              color: AppColors.slate100,
              borderRadius: BorderRadius.circular(AppRadius.md),
            ),
            child: TextField(
              controller: _searchController,
              onChanged: (val) {
                setState(() => _searchQuery = val);
              },
              style: const TextStyle(fontSize: 13, color: AppColors.ink),
              decoration: InputDecoration(
                hintText: 'Search center, city or address...',
                hintStyle:
                    const TextStyle(fontSize: 13, color: AppColors.slate400),
                prefixIcon: const Icon(Icons.search_rounded,
                    size: 18, color: AppColors.slate500),
                suffixIcon: _searchQuery.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.close_rounded,
                            size: 16, color: AppColors.slate500),
                        onPressed: () {
                          _searchController.clear();
                          setState(() => _searchQuery = '');
                        },
                      )
                    : null,
                border: InputBorder.none,
                contentPadding: const EdgeInsets.symmetric(vertical: 10),
              ),
            ),
          ),
          const SizedBox(height: 8),

          // Categories Filter
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _buildFilterChip('all', 'All Points (${_allCenters.length})',
                    Icons.place_rounded),
                const SizedBox(width: 6),
                _buildFilterChip('inspection', 'Inspection Hubs',
                    Icons.verified_outlined),
                const SizedBox(width: 6),
                _buildFilterChip('drop_off', 'Drop-off Stations',
                    Icons.inventory_2_outlined),
                const SizedBox(width: 6),
                _buildFilterChip('warehouse', 'Warehouses',
                    Icons.warehouse_outlined),
                const SizedBox(width: 6),
                _buildFilterChip(
                    'express', 'Express Drop', Icons.flash_on_rounded),
                const SizedBox(width: 6),
                _buildFilterChip(
                    'store', 'My Registered Store', Icons.storefront_rounded),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String category, String label, IconData icon) {
    final isSelected = _selectedCategory == category;
    return InkWell(
      onTap: () {
        setState(() => _selectedCategory = category);
      },
      borderRadius: BorderRadius.circular(AppRadius.pill),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.brand500 : AppColors.slate100,
          borderRadius: BorderRadius.circular(AppRadius.pill),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon,
                size: 13,
                color: isSelected ? Colors.white : AppColors.slate600),
            const SizedBox(width: 4),
            Text(
              label,
              style: TextStyle(
                fontSize: 11,
                fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
                color: isSelected ? Colors.white : AppColors.slate700,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFloatingMapButton({
    required IconData icon,
    required String tooltip,
    required VoidCallback onTap,
  }) {
    return Material(
      color: Colors.white,
      elevation: 3,
      shape: const CircleBorder(),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(8),
          child: Icon(icon, size: 20, color: AppColors.ink),
        ),
      ),
    );
  }

  Widget _buildSelectedCenterCard(SellerCenterPoint point) {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.md),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.12),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 38,
                height: 38,
                decoration: BoxDecoration(
                  color: point.isUserStore
                      ? AppColors.successBg
                      : AppColors.brand50,
                  borderRadius: BorderRadius.circular(AppRadius.md),
                ),
                child: Icon(
                  point.isUserStore
                      ? Icons.storefront_rounded
                      : point.category == 'warehouse'
                          ? Icons.warehouse_rounded
                          : Icons.verified_rounded,
                  color:
                      point.isUserStore ? AppColors.success : AppColors.brand500,
                  size: 20,
                ),
              ),
              const SizedBox(width: AppSpacing.sm),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      point.name,
                      style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w800,
                          color: AppColors.ink),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 2),
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: AppColors.brand50,
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(
                            point.type,
                            style: const TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.w700,
                                color: AppColors.brand700),
                          ),
                        ),
                        const SizedBox(width: 6),
                        const Icon(Icons.circle,
                            size: 6, color: AppColors.success),
                        const SizedBox(width: 4),
                        const Text(
                          'Open Now',
                          style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w600,
                              color: AppColors.success),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              IconButton(
                padding: EdgeInsets.zero,
                constraints: const BoxConstraints(),
                icon: const Icon(Icons.close_rounded,
                    size: 18, color: AppColors.slate400),
                onPressed: () => setState(() => _selectedPoint = null),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              const Icon(Icons.location_on_outlined,
                  size: 14, color: AppColors.slate400),
              const SizedBox(width: 4),
              Expanded(
                child: Text(
                  point.address,
                  style: const TextStyle(
                      fontSize: 12, color: AppColors.slate600),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Row(
            children: [
              const Icon(Icons.access_time_rounded,
                  size: 14, color: AppColors.slate400),
              const SizedBox(width: 4),
              Text(
                point.hours,
                style: const TextStyle(
                    fontSize: 11, color: AppColors.slate500),
              ),
            ],
          ),
          const SizedBox(height: 12),
          // Action Buttons: Directions & Call
          Row(
            children: [
              Expanded(
                flex: 2,
                child: ElevatedButton.icon(
                  onPressed: () => _openGoogleMapsNavigation(point),
                  icon: const Icon(Icons.directions_rounded, size: 16),
                  label: const Text('Get Directions'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.brand500,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 10),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(AppRadius.md)),
                    elevation: 0,
                  ),
                ),
              ),
              const SizedBox(width: 8),
              if (point.phone.isNotEmpty) ...[
                IconButton(
                  tooltip: 'Call Center',
                  style: IconButton.styleFrom(
                    backgroundColor: AppColors.slate100,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(AppRadius.md)),
                  ),
                  icon: const Icon(Icons.phone_rounded,
                      size: 18, color: AppColors.ink),
                  onPressed: () => _callPhone(point.phone),
                ),
                const SizedBox(width: 8),
              ],
              if (point.category == 'inspection')
                OutlinedButton(
                  onPressed: () {
                    Navigator.of(context).push(
                      MaterialPageRoute(
                          builder: (_) => const InspectionQueueScreen()),
                    );
                  },
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppColors.brand700,
                    side: const BorderSide(color: AppColors.brand300),
                    padding: const EdgeInsets.symmetric(
                        horizontal: 12, vertical: 10),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(AppRadius.md)),
                  ),
                  child: const Text('Book Inspection',
                      style: TextStyle(
                          fontSize: 12, fontWeight: FontWeight.w700)),
                ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildBottomCentersList(List<SellerCenterPoint> centers) {
    if (centers.isEmpty) {
      return Container(
        color: AppColors.surface,
        padding: const EdgeInsets.all(AppSpacing.md),
        child: const Center(
          child: Text('No centers match your search filter',
              style: TextStyle(fontSize: 12, color: AppColors.slate500)),
        ),
      );
    }

    return Container(
      color: AppColors.surface,
      padding: const EdgeInsets.only(top: 8, bottom: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: AppSpacing.md),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Quick Location List',
                    style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w800,
                        color: AppColors.ink)),
                Text('${centers.length} hubs available',
                    style: const TextStyle(
                        fontSize: 11, color: AppColors.slate500)),
              ],
            ),
          ),
          const SizedBox(height: 6),
          SizedBox(
            height: 74,
            child: ListView.separated(
              padding: const EdgeInsets.symmetric(horizontal: AppSpacing.md),
              scrollDirection: Axis.horizontal,
              itemCount: centers.length,
              separatorBuilder: (_, _) => const SizedBox(width: 8),
              itemBuilder: (context, index) {
                final center = centers[index];
                final isSelected = _selectedPoint?.id == center.id;

                return InkWell(
                  onTap: () => _animateToCenter(center),
                  borderRadius: BorderRadius.circular(AppRadius.md),
                  child: Container(
                    width: 210,
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color:
                          isSelected ? AppColors.brand50 : AppColors.slate50,
                      borderRadius: BorderRadius.circular(AppRadius.md),
                      border: Border.all(
                        color: isSelected
                            ? AppColors.brand500
                            : AppColors.slate200,
                        width: isSelected ? 1.5 : 1,
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Row(
                          children: [
                            Icon(
                              center.isUserStore
                                  ? Icons.storefront_rounded
                                  : Icons.location_on_rounded,
                              size: 14,
                              color: isSelected
                                  ? AppColors.brand600
                                  : AppColors.slate500,
                            ),
                            const SizedBox(width: 4),
                            Expanded(
                              child: Text(
                                center.name,
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w700,
                                  color: isSelected
                                      ? AppColors.brand700
                                      : AppColors.ink,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 2),
                        Text(
                          center.address,
                          style: const TextStyle(
                              fontSize: 10, color: AppColors.slate500),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 2),
                        Text(
                          center.city,
                          style: const TextStyle(
                              fontSize: 9,
                              fontWeight: FontWeight.w600,
                              color: AppColors.brand600),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _TrianglePainter extends CustomPainter {
  final Color color;
  _TrianglePainter({required this.color});

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.fill;
    final path = Path()
      ..moveTo(0, 0)
      ..lineTo(size.width, 0)
      ..lineTo(size.width / 2, size.height)
      ..close();
    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(covariant _TrianglePainter oldDelegate) =>
      oldDelegate.color != color;
}
