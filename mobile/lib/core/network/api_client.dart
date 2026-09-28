import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
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
    debugPrint('🌐 [API GET Request] ➔ $url');
    try {
      final headers = await _getHeaders();
      final response = await _client.get(Uri.parse(url), headers: headers);
      _logResponse('GET', url, response);
      return _handleResponse(response);
    } catch (e) {
      debugPrint('❌ [API GET Error] ➔ $url | Error: $e');
      _handleError(e);
    }
  }

  Future<dynamic> post(String url, {Map<String, dynamic>? body}) async {
    debugPrint('🌐 [API POST Request] ➔ $url');
    if (body != null) {
      debugPrint('📤 [API POST Payload] ➔ ${jsonEncode(body)}');
    }
    try {
      final headers = await _getHeaders();
      final response = await _client.post(
        Uri.parse(url),
        headers: headers,
        body: body != null ? jsonEncode(body) : null,
      );
      _logResponse('POST', url, response);
      return _handleResponse(response);
    } catch (e) {
      debugPrint('❌ [API POST Error] ➔ $url | Error: $e');
      _handleError(e);
    }
  }

  Future<dynamic> put(String url, {Map<String, dynamic>? body}) async {
    debugPrint('🌐 [API PUT Request] ➔ $url');
    if (body != null) {
      debugPrint('📤 [API PUT Payload] ➔ ${jsonEncode(body)}');
    }
    try {
      final headers = await _getHeaders();
      final response = await _client.put(
        Uri.parse(url),
        headers: headers,
        body: body != null ? jsonEncode(body) : null,
      );
      _logResponse('PUT', url, response);
      return _handleResponse(response);
    } catch (e) {
      debugPrint('❌ [API PUT Error] ➔ $url | Error: $e');
      _handleError(e);
    }
  }

  Future<dynamic> delete(String url) async {
    debugPrint('🌐 [API DELETE Request] ➔ $url');
    try {
      final headers = await _getHeaders();
      final response = await _client.delete(Uri.parse(url), headers: headers);
      _logResponse('DELETE', url, response);
      return _handleResponse(response);
    } catch (e) {
      debugPrint('❌ [API DELETE Error] ➔ $url | Error: $e');
      _handleError(e);
    }
  }

  Future<dynamic> postMultipart(
    String url, {
    required File file,
    required String fileParamName,
    Map<String, String>? fields,
  }) async {
    debugPrint('🌐 [API Multipart Request] ➔ $url | File: ${file.path}');
    try {
      final headers = await _getHeaders(isMultipart: true);
      final request = http.MultipartRequest('POST', Uri.parse(url));
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
      _logResponse('MULTIPART POST', url, response);
      return _handleResponse(response);
    } catch (e) {
      debugPrint('❌ [API Multipart Error] ➔ $url | Error: $e');
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
