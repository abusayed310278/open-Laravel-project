import 'dart:io';

/// Bypasses TLS certificate validation in development environments
/// to allow smooth testing on local Laragon / self-signed HTTPS hosts.
class AppHttpOverrides extends HttpOverrides {
  @override
  HttpClient createHttpClient(SecurityContext? context) {
    return super.createHttpClient(context)
      ..badCertificateCallback = (X509Certificate cert, String host, int port) => true;
  }
}
