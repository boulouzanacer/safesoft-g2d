class NotificationModel {
  final String id;
  final Map<String, dynamic> data;
  final String? readAt;
  final String? createdAt;

  NotificationModel({
    required this.id,
    required this.data,
    this.readAt,
    this.createdAt,
  });

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    return NotificationModel(
      id: (json['id'] ?? '').toString(),
      data: json['data'] is Map<String, dynamic> ? json['data'] as Map<String, dynamic> : <String, dynamic>{},
      readAt: json['read_at']?.toString(),
      createdAt: json['created_at']?.toString(),
    );
  }
}

