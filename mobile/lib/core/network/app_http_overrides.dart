import 'dart:async';
import 'dart:convert';
import 'dart:io';

class AppHttpOverrides extends HttpOverrides {
  static final Map<String, String> _dnsCache = {
    'res.cloudinary.com': '104.16.78.6',
    'images.unsplash.com': '151.101.66.208',
    'picsum.photos': '104.26.5.30',
    'fastly.picsum.photos': '151.101.65.91',
    'loremflickr.com': '188.40.30.104',
  };

  static final RegExp _ipRegex = RegExp(r'^\d+\.\d+\.\d+\.\d+$');

  static Future<String> resolveHost(String host) async {
    if (_dnsCache.containsKey(host)) return _dnsCache[host]!;
    if (_ipRegex.hasMatch(host) || host == 'localhost') return host;

    // Resolve host via Google DNS-over-HTTPS using raw IP 8.8.8.8
    try {
      final dohClient = HttpClient();
      dohClient.badCertificateCallback = (_, _, _) => true;
      dohClient.connectionTimeout = const Duration(seconds: 4);

      final req = await dohClient.getUrl(Uri.parse('https://8.8.8.8/resolve?name=$host&type=A'));
      final res = await req.close();
      if (res.statusCode == 200) {
        final body = await utf8.decoder.bind(res).join();
        final json = jsonDecode(body);
        final answers = json['Answer'] as List?;
        if (answers != null) {
          for (final a in answers) {
            if (a['type'] == 1 && a['data'] != null) {
              final ip = a['data'].toString().trim();
              if (_ipRegex.hasMatch(ip)) {
                _dnsCache[host] = ip;
                return ip;
              }
            }
          }
        }
      }
    } catch (_) {}

    return host;
  }

  @override
  HttpClient createHttpClient(SecurityContext? context) {
    final client = super.createHttpClient(context);
    client.badCertificateCallback = (X509Certificate cert, String host, int port) => true;

    client.connectionFactory = (Uri uri, String? proxyHost, int? proxyPort) async {
      final socketFuture = () async {
        final port = uri.port != 0 ? uri.port : (uri.scheme == 'https' ? 443 : 80);
        final targetHost = await resolveHost(uri.host);

        final rawSocket = await Socket.connect(
          targetHost,
          port,
          timeout: const Duration(seconds: 15),
        );

        if (uri.scheme == 'https') {
          return await SecureSocket.secure(
            rawSocket,
            host: uri.host,
            onBadCertificate: (X509Certificate cert) => true,
            supportedProtocols: ['http/1.1'],
          );
        }

        return rawSocket;
      }();

      return ConnectionTask.fromSocket(socketFuture, () {});
    };

    return client;
  }
}
