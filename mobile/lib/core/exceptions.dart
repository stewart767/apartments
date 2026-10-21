class AppException implements Exception {
  final String message;
  final int? statusCode;
  final dynamic errors;

  AppException(this.message, {this.statusCode, this.errors});

  @override
  String toString() => message;
}

class NetworkException extends AppException {
  NetworkException([String message = 'Unable to connect to server. Please check your internet connection.'])
      : super(message, statusCode: 0);
}

class UnauthorizedException extends AppException {
  UnauthorizedException([String message = 'Session expired. Please log in again.'])
      : super(message, statusCode: 401);
}

class ForbiddenException extends AppException {
  ForbiddenException([String message = 'You do not have permission to perform this action.'])
      : super(message, statusCode: 403);
}

class NotFoundException extends AppException {
  NotFoundException([String message = 'The requested resource was not found.'])
      : super(message, statusCode: 404);
}

class ValidationException extends AppException {
  ValidationException(String message, {dynamic errors})
      : super(message, statusCode: 422, errors: errors);
}

class ServerException extends AppException {
  ServerException([String message = 'Internal server error occurred. Please try again later.'])
      : super(message, statusCode: 500);
}
