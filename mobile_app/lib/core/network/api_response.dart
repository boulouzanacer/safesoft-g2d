class ApiResponse<T> {
  final bool success;
  final T? data;
  final String message;
  final Map<String, dynamic>? errors;

  ApiResponse({
    required this.success,
    required this.data,
    required this.message,
    required this.errors,
  });

  factory ApiResponse.fromJson(
    Map<String, dynamic> json, {
    T Function(dynamic data)? parseData,
  }) {
    return ApiResponse<T>(
      success: json['success'] == true,
      data: parseData != null ? parseData(json['data']) : json['data'] as T?,
      message: (json['message'] ?? '').toString(),
      errors: json['errors'] is Map<String, dynamic> ? json['errors'] as Map<String, dynamic> : null,
    );
  }
}

