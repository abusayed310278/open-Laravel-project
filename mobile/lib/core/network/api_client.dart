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
  final http.Client _client;

  ApiClient({http.Client? client}) : _client = client ?? http.Client();

  Uri _resolveUri(String url) {
    if (url.startsWith('http://') || url.startsWith('https://')) {
      return Uri.parse(url);
    }
    final base = ApiConfig.baseUrl.endsWith('/')
        ? ApiConfig.baseUrl.substring(0, ApiConfig.baseUrl.length - 1)
        : ApiConfig.baseUrl;
    final path = url.startsWith('/') ? url : '/$url';
    return Uri.parse('$base$path');
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
    };

    if (!isMultipart) {
      headers['Content-Type'] = 'application/json';
    }

    if (token != null && token.isNotEmpty) {
      headers['Authorization'] = 'Bearer $token';
    }

    return headers;
  }

  Future<dynamic> get(String url) async {
    final uri = _resolveUri(url);
    debugPrint('🌐 [API GET Request] ➔ $uri');
    try {
      final headers = await _getHeaders();
      final response = await _client.get(uri, headers: headers);
      _logResponse('GET', uri.toString(), response);
      return _handleResponse(response);
    } catch (e) {
      debugPrint('❌ [API GET Error] ➔ $uri | Error: $e');
      _handleError(e);
    }
  }

  Future<dynamic> post(String url, {Map<String, dynamic>? body}) async {
    final uri = _resolveUri(url);
    debugPrint('🌐 [API POST Request] ➔ $uri');
    if (body != null) {
      debugPrint('📤 [API POST Payload] ➔ ${jsonEncode(body)}');
    }
    try {
      final headers = await _getHeaders();
      final response = await _client.post(
        uri,
        headers: headers,
        body: body != null ? jsonEncode(body) : null,
      );
      _logResponse('POST', uri.toString(), response);
      return _handleResponse(response);
    } catch (e) {
      debugPrint('❌ [API POST Error] ➔ $uri | Error: $e');
      _handleError(e);
    }
  }

  Future<dynamic> put(String url, {Map<String, dynamic>? body}) async {
    final uri = _resolveUri(url);
    debugPrint('🌐 [API PUT Request] ➔ $uri');
    if (body != null) {
      debugPrint('📤 [API PUT Payload] ➔ ${jsonEncode(body)}');
    }
    try {
      final headers = await _getHeaders();
      final response = await _client.put(
        uri,
        headers: headers,
        body: body != null ? jsonEncode(body) : null,
      );
      _logResponse('PUT', uri.toString(), response);
      return _handleResponse(response);
    } catch (e) {
      debugPrint('❌ [API PUT Error] ➔ $uri | Error: $e');
      _handleError(e);
    }
  }

  Future<dynamic> delete(String url) async {
    final uri = _resolveUri(url);
    debugPrint('🌐 [API DELETE Request] ➔ $uri');
    try {
      final headers = await _getHeaders();
      final response = await _client.delete(uri, headers: headers);
      _logResponse('DELETE', uri.toString(), response);
      return _handleResponse(response);
    } catch (e) {
      debugPrint('❌ [API DELETE Error] ➔ $uri | Error: $e');
      _handleError(e);
    }
  }

  Future<dynamic> postMultipart(
    String url, {
    required File file,
    required String fileParamName,
    Map<String, String>? fields,
  }) async {
    final uri = _resolveUri(url);
    debugPrint('🌐 [API Multipart Request] ➔ $uri | File: ${file.path}');
    try {
      final headers = await _getHeaders(isMultipart: true);
      final request = http.MultipartRequest('POST', uri);
      request.headers.addAll(headers);

      if (fields != null) {
        request.fields.addAll(fields);
      }

      final stream = http.ByteStream(file.openRead());
      final length = await file.length();
      final multipartFile = http.MultipartFile(
        fileParamName,
        stream,
        length,
        filename: file.path.split('/').last.split('\\').last,
      );
      request.files.add(multipartFile);

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);
      _logResponse('MULTIPART POST', uri.toString(), response);
      return _handleResponse(response);
    } catch (e) {
      debugPrint('❌ [API Multipart Error] ➔ $uri | Error: $e');
      _handleError(e);
    }
  }

  void _logResponse(String method, String url, http.Response response) {
    debugPrint('📥 [API Response $method] ➔ Status: ${response.statusCode} | URL: $url');
    if (kDebugMode) {
      final bodySnippet = response.body.length > 300 ? '${response.body.substring(0, 300)}...' : response.body;
      debugPrint('📄 [API Response Body] ➔ $bodySnippet');
    }
  }

  dynamic _handleResponse(http.Response response) {
    dynamic jsonBody;
    try {
      jsonBody = jsonDecode(response.body);
    } catch (_) {
      jsonBody = null;
    }

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return jsonBody;
    }

    String message = 'An error occurred (${response.statusCode})';
    Map<String, dynamic>? validationErrors;

    if (jsonBody is Map<String, dynamic>) {
      if (jsonBody.containsKey('message')) {
        message = jsonBody['message'].toString();
      }
      if (jsonBody.containsKey('errors') && jsonBody['errors'] is Map) {
        validationErrors = jsonBody['errors'] as Map<String, dynamic>;
        final firstKey = validationErrors.keys.first;
        final firstVal = validationErrors[firstKey];
        if (firstVal is List && firstVal.isNotEmpty) {
          message = firstVal.first.toString();
        }
      }
    }

    throw ApiException(
      message,
      statusCode: response.statusCode,
      errors: validationErrors,
    );
  }

  Never _handleError(dynamic error) {
    if (error is ApiException) {
      throw error;
    }
    if (error is SocketException) {
      throw ApiException('Network error. Please check your internet connection or server host.');
    }
    throw ApiException(error.toString());
  }
}
