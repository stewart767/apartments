import 'dart:io';
import 'package:dio/dio.dart';
import 'package:http_parser/http_parser.dart';
import 'constants.dart';
import 'exceptions.dart';
import 'storage_service.dart';

class ApiClient {
  late final Dio _dio;
  static ApiClient? _instance;

  ApiClient._internal() {
    _dio = Dio(
      BaseOptions(
        baseUrl: AppConstants.baseUrl,
        connectTimeout: const Duration(seconds: 15),
        receiveTimeout: const Duration(seconds: 15),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      ),
    );

    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await StorageService.instance.getToken();
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          return handler.next(options);
        },
        onError: (DioException e, handler) {
          final exception = _handleDioError(e);
          return handler.reject(
            DioException(
              requestOptions: e.requestOptions,
              response: e.response,
              type: e.type,
              error: exception,
            ),
          );
        },
      ),
    );
  }

  static ApiClient get instance => _instance ??= ApiClient._internal();
  Dio get dio => _dio;

  // Custom baseUrl setter for development/testing
  void setBaseUrl(String url) {
    final cleanUrl = url.trim().replaceAll(RegExp(r'/+$'), '');
    AppConstants.setBaseUrl(cleanUrl);
    _dio.options.baseUrl = cleanUrl;
  }

  // API connectivity test against /ping
  Future<bool> checkHealth([String? targetUrl]) async {
    try {
      final url = targetUrl != null ? targetUrl.trim().replaceAll(RegExp(r'/+$'), '') : _dio.options.baseUrl;
      final testDio = Dio(
        BaseOptions(
          baseUrl: url,
          connectTimeout: const Duration(seconds: 6),
          receiveTimeout: const Duration(seconds: 6),
          headers: {'Accept': 'application/json'},
        ),
      );
      final response = await testDio.get('/ping');
      return response.statusCode == 200 &&
          response.data is Map &&
          response.data['success'] == true;
    } catch (_) {
      return false;
    }
  }

  // GET Request
  Future<dynamic> get(
    String path, {
    Map<String, dynamic>? queryParameters,
  }) async {
    try {
      final response = await _dio.get(path, queryParameters: queryParameters);
      return _extractData(response);
    } on DioException catch (e) {
      throw e.error is AppException ? e.error as AppException : _handleDioError(e);
    } catch (e) {
      throw ServerException(e.toString());
    }
  }

  // POST Request
  Future<dynamic> post(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
  }) async {
    try {
      final response = await _dio.post(path, data: data, queryParameters: queryParameters);
      return _extractData(response);
    } on DioException catch (e) {
      throw e.error is AppException ? e.error as AppException : _handleDioError(e);
    } catch (e) {
      throw ServerException(e.toString());
    }
  }

  // PUT Request
  Future<dynamic> put(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
  }) async {
    try {
      final response = await _dio.put(path, data: data, queryParameters: queryParameters);
      return _extractData(response);
    } on DioException catch (e) {
      throw e.error is AppException ? e.error as AppException : _handleDioError(e);
    } catch (e) {
      throw ServerException(e.toString());
    }
  }

  // DELETE Request
  Future<dynamic> delete(
    String path, {
    Map<String, dynamic>? queryParameters,
  }) async {
    try {
      final response = await _dio.delete(path, queryParameters: queryParameters);
      return _extractData(response);
    } on DioException catch (e) {
      throw e.error is AppException ? e.error as AppException : _handleDioError(e);
    } catch (e) {
      throw ServerException(e.toString());
    }
  }

  // Multipart Form Post (for images/receipts)
  Future<dynamic> postMultipart(
    String path, {
    required Map<String, dynamic> fields,
    Map<String, File>? files,
    List<File>? multiFiles,
    String multiFilesKey = 'images',
  }) async {
    try {
      final formDataMap = <String, dynamic>{...fields};

      if (files != null) {
        for (var entry in files.entries) {
          final file = entry.value;
          final fileName = file.path.split('/').last.split('\\').last;
          formDataMap[entry.key] = await MultipartFile.fromFile(
            file.path,
            filename: fileName,
          );
        }
      }

      if (multiFiles != null && multiFiles.isNotEmpty) {
        final multipartList = <MultipartFile>[];
        for (var file in multiFiles) {
          final fileName = file.path.split('/').last.split('\\').last;
          multipartList.add(
            await MultipartFile.fromFile(
              file.path,
              filename: fileName,
            ),
          );
        }
        formDataMap['$multiFilesKey[]'] = multipartList;
      }

      final formData = FormData.fromMap(formDataMap);
      final response = await _dio.post(
        path,
        data: formData,
        options: Options(contentType: 'multipart/form-data'),
      );
      return _extractData(response);
    } on DioException catch (e) {
      throw e.error is AppException ? e.error as AppException : _handleDioError(e);
    } catch (e) {
      throw ServerException(e.toString());
    }
  }

  dynamic _extractData(Response response) {
    if (response.data is Map<String, dynamic>) {
      final map = response.data as Map<String, dynamic>;
      if (map.containsKey('data')) {
        return map['data'];
      }
      return map;
    }
    return response.data;
  }

  AppException _handleDioError(DioException error) {
    if (error.type == DioExceptionType.connectionTimeout ||
        error.type == DioExceptionType.receiveTimeout ||
        error.type == DioExceptionType.sendTimeout) {
      return NetworkException('Connection timed out. The server took too long to respond. Please verify your connection.');
    }
    if (error.type == DioExceptionType.badCertificate) {
      return NetworkException('SSL Certificate validation error. Please verify the server HTTPS certificate.');
    }
    if (error.type == DioExceptionType.connectionError) {
      return NetworkException('Unable to reach server at ${AppConstants.baseUrl}. Please check server availability or internet connection.');
    }

    final response = error.response;
    if (response == null) {
      return NetworkException(error.message ?? 'Network error connecting to ${AppConstants.baseUrl}');
    }

    final statusCode = response.statusCode ?? 500;
    String message = 'An unexpected error occurred';
    dynamic errors;

    if (response.data is Map<String, dynamic>) {
      final data = response.data as Map<String, dynamic>;
      message = data['message'] ?? message;
      errors = data['errors'];
    }

    switch (statusCode) {
      case 401:
        StorageService.instance.clearSession();
        return UnauthorizedException(message);
      case 403:
        return ForbiddenException(message);
      case 404:
        return NotFoundException(message != 'An unexpected error occurred' ? message : 'The requested resource was not found (404).');
      case 422:
        return ValidationException(message, errors: errors);
      case 500:
      case 502:
      case 503:
      case 504:
        return ServerException(message != 'An unexpected error occurred' ? message : 'Server error ($statusCode). Please try again later.');
      default:
        return ServerException(message);
    }
  }
}
