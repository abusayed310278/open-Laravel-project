import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'api_config.dart';
import 'storage_service.dart';

class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final Map<String, dynamic>? errors;

  ApiException(this.message, {this.statusCode, this.errors});

  @override
  String toString() => message;
}

class ApiClient {
  static const Duration defaultTimeout = Duration(seconds: 30);
  final http.Client _client;

  ApiClient({http.Client? client}) : _client = client ?? http.Client();

  Uri _resolveUri(String url, [Map<String, dynamic>? queryParams]) {
    if (url.startsWith('http://') || url.startsWith('https://')) {
      final uri = Uri.parse(url);
      if (queryParams != null && queryParams.isNotEmpty) {
        return uri.replace(queryParameters: {
          ...uri.queryParameters,
          ...queryParams.map((k, v) => MapEntry(k, v.toString())),
        });
      }
      return uri;
    }

    final base = ApiConfig.baseUrl.endsWith('/')
        ? ApiConfig.baseUrl.substring(0, ApiConfig.baseUrl.length - 1)
        : ApiConfig.baseUrl;
    final path = url.startsWith('/') ? url : '/$url';
    final fullUrl = '$base$path';

    final uri = Uri.parse(fullUrl);
    if (queryParams != null && queryParams.isNotEmpty) {
      return uri.replace(queryParameters: queryParams.map((k, v) => MapEntry(k, v.toString())));
    }
    return uri;
  }

  Future<Map<String, String>> _getHeaders({bool isMultipart = false}) async {
    String? token;
    try {
      final storage = await StorageService.getInstance();
      token = storage.getToken();
    } catch (_) {
      token = null;
    }

    final headers = <String, String>{
      'Accept': 'application/json',
      'Connection': 'close',
    };

    if (!isMultipart) {
      headers['Content-Type'] = 'application/json';
    }

    if (token != null && token.isNotEmpty) {
      headers['Authorization'] = 'Bearer $token';
    }

    return headers;
  }

  Future<dynamic> get(String url, {Map<String, dynamic>? queryParams, Duration? timeout}) async {
    final uri = _resolveUri(url, queryParams);
    if (kDebugMode) debugPrint('[ApiClient] GET $uri');
    try {
      final headers = await _getHeaders();
      final response = await _client.get(uri, headers: headers).timeout(timeout ?? defaultTimeout);
      return _handleResponse(response);
    } catch (e) {
      _handleNetworkError(e, uri);
    }
  }

  Future<dynamic> post(String url, {dynamic body, Duration? timeout}) async {
    final uri = _resolveUri(url);
    if (kDebugMode) debugPrint('[ApiClient] POST $uri');
    try {
      final headers = await _getHeaders();
      final response = await _client.post(
        uri,
        headers: headers,
        body: body != null ? jsonEncode(body) : null,
      ).timeout(timeout ?? defaultTimeout);
      return _handleResponse(response);
    } catch (e) {
      _handleNetworkError(e, uri);
    }
  }

  Future<dynamic> put(String url, {dynamic body, Duration? timeout}) async {
    final uri = _resolveUri(url);
    if (kDebugMode) debugPrint('[ApiClient] PUT $uri');
    try {
      final headers = await _getHeaders();
      final response = await _client.put(
        uri,
        headers: headers,
        body: body != null ? jsonEncode(body) : null,
      ).timeout(timeout ?? defaultTimeout);
      return _handleResponse(response);
    } catch (e) {
      _handleNetworkError(e, uri);
    }
  }

  Future<dynamic> patch(String url, {dynamic body, Duration? timeout}) async {
    final uri = _resolveUri(url);
    if (kDebugMode) debugPrint('[ApiClient] PATCH $uri');
    try {
      final headers = await _getHeaders();
      final response = await _client.patch(
        uri,
        headers: headers,
        body: body != null ? jsonEncode(body) : null,
      ).timeout(timeout ?? defaultTimeout);
      return _handleResponse(response);
    } catch (e) {
      _handleNetworkError(e, uri);
    }
  }

  Future<dynamic> delete(String url, {Duration? timeout}) async {
    final uri = _resolveUri(url);
    if (kDebugMode) debugPrint('[ApiClient] DELETE $uri');
    try {
      final headers = await _getHeaders();
      final response = await _client.delete(uri, headers: headers).timeout(timeout ?? defaultTimeout);
      return _handleResponse(response);
    } catch (e) {
      _handleNetworkError(e, uri);
    }
  }

  Future<dynamic> multipart(
    String url, {
    String method = 'POST',
    Map<String, String>? fields,
    List<http.MultipartFile>? files,
  }) async {
    final uri = _resolveUri(url);
    if (kDebugMode) debugPrint('[ApiClient] MULTIPART $method $uri');
    try {
      final request = http.MultipartRequest(method, uri);
      final headers = await _getHeaders(isMultipart: true);
      request.headers.addAll(headers);

      if (fields != null) {
        request.fields.addAll(fields);
      }
      if (files != null) {
        request.files.addAll(files);
      }

      final streamedResponse = await _client.send(request).timeout(const Duration(seconds: 60));
      final response = await http.Response.fromStream(streamedResponse);
      return _handleResponse(response);
    } catch (e) {
      _handleNetworkError(e, uri);
    }
  }

  dynamic _handleResponse(http.Response response) {
    dynamic body;
    try {
      body = jsonDecode(response.body);
    } catch (_) {
      body = null;
    }

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return body;
    }

    String message = 'An error occurred (${response.statusCode})';
    Map<String, dynamic>? errors;

    if (body is Map<String, dynamic>) {
      if (body.containsKey('message') && body['message'] is String) {
        message = body['message'];
      }
      if (body.containsKey('errors') && body['errors'] is Map<String, dynamic>) {
        final errMap = body['errors'] as Map<String, dynamic>;
        errors = errMap;
        if (errMap.isNotEmpty) {
          final firstError = errMap.values.first;
          if (firstError is List && firstError.isNotEmpty) {
            message = firstError.first.toString();
          } else if (firstError is String) {
            message = firstError;
          }
        }
      }
    }

    throw ApiException(message, statusCode: response.statusCode, errors: errors);
  }

  Never _handleNetworkError(dynamic error, [Uri? uri]) {
    if (kDebugMode) {
      debugPrint('[ApiClient ERROR] ${uri != null ? "[$uri] " : ""}$error');
    }
    if (error is ApiException) {
      throw error;
    }
    if (error is TimeoutException) {
      throw ApiException('Connection timed out. Please check your backend status and connection.');
    }
    if (error is SocketException) {
      final host = uri?.host ?? ApiConfig.backendHost;
      throw ApiException('Unable to connect to server at $host. Please check backend status and connection.');
    }
    if (error is http.ClientException) {
      throw ApiException('Network connection failed: ${error.message}');
    }
    throw ApiException('Something went wrong: ${error.toString()}');
  }
}
