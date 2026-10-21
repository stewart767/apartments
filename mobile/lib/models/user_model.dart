class UserModel {
  final int id;
  final String name;
  final String email;
  final String role;
  final String status;
  final String? phone;
  final String? whatsapp;
  final String? gender;
  final String? dateOfBirth;
  final String? avatar;
  final TenantDetailModel? tenantDetail;
  final OwnerDetailModel? ownerDetail;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    required this.status,
    this.phone,
    this.whatsapp,
    this.gender,
    this.dateOfBirth,
    this.avatar,
    this.tenantDetail,
    this.ownerDetail,
  });

  bool get isTenant => role == 'tenant';
  bool get isOwner => role == 'owner';
  bool get isManager => role == 'property_manager';
  bool get isSuperAdmin => role == 'super_admin';

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      name: json['name'] ?? '',
      email: json['email'] ?? '',
      role: json['role'] ?? 'tenant',
      status: json['status'] ?? 'active',
      phone: json['phone'],
      whatsapp: json['whatsapp'],
      gender: json['gender'],
      dateOfBirth: json['date_of_birth'],
      avatar: json['avatar'],
      tenantDetail: json['tenant_detail'] != null
          ? TenantDetailModel.fromJson(json['tenant_detail'])
          : null,
      ownerDetail: json['owner_detail'] != null
          ? OwnerDetailModel.fromJson(json['owner_detail'])
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'role': role,
      'status': status,
      'phone': phone,
      'whatsapp': whatsapp,
      'gender': gender,
      'date_of_birth': dateOfBirth,
      'avatar': avatar,
      'tenant_detail': tenantDetail?.toJson(),
      'owner_detail': ownerDetail?.toJson(),
    };
  }
}

class TenantDetailModel {
  final String? nationalIdPassport;
  final String? emergencyContact;
  final bool isStudent;
  final String? studentRegNumber;
  final String? institution;

  TenantDetailModel({
    this.nationalIdPassport,
    this.emergencyContact,
    this.isStudent = false,
    this.studentRegNumber,
    this.institution,
  });

  factory TenantDetailModel.fromJson(Map<String, dynamic> json) {
    return TenantDetailModel(
      nationalIdPassport: json['national_id_passport'],
      emergencyContact: json['emergency_contact'],
      isStudent: json['is_student'] == true || json['is_student'] == 1,
      studentRegNumber: json['student_reg_number'],
      institution: json['institution'] is Map ? json['institution']['name'] : json['institution'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'national_id_passport': nationalIdPassport,
      'emergency_contact': emergencyContact,
      'is_student': isStudent,
      'student_reg_number': studentRegNumber,
      'institution': institution,
    };
  }
}

class OwnerDetailModel {
  final String? businessName;
  final String? registrationNumber;
  final String? taxNumber;
  final String? idType;
  final String? idNumber;
  final String? address;
  final String? verificationStatus;

  OwnerDetailModel({
    this.businessName,
    this.registrationNumber,
    this.taxNumber,
    this.idType,
    this.idNumber,
    this.address,
    this.verificationStatus,
  });

  factory OwnerDetailModel.fromJson(Map<String, dynamic> json) {
    return OwnerDetailModel(
      businessName: json['business_name'],
      registrationNumber: json['registration_number'],
      taxNumber: json['tax_number'],
      idType: json['id_type'],
      idNumber: json['id_number'],
      address: json['address'],
      verificationStatus: json['verification_status'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'business_name': businessName,
      'registration_number': registrationNumber,
      'tax_number': taxNumber,
      'id_type': idType,
      'id_number': idNumber,
      'address': address,
      'verification_status': verificationStatus,
    };
  }
}
