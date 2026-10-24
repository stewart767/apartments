import 'package:flutter_test/flutter_test.dart';
import 'package:smartnest_mobile/core/constants.dart';
import 'package:smartnest_mobile/models/user_model.dart';

void main() {
  group('SmartNest App Unit & Smoke Tests', () {
    test('AppConstants contains correct defaults', () {
      expect(AppConstants.appName, equals('SmartNest'));
      expect(AppConstants.tokenKey, isNotEmpty);
      expect(AppConstants.roleTenant, equals('tenant'));
      expect(AppConstants.roleOwner, equals('owner'));
      expect(AppConstants.defaultProdUrl, equals('https://www.reliancehomes.co.tz/api/v1'));
    });

    test('UserModel serialization and role checks', () {
      final json = {
        'id': 1,
        'name': 'Test User',
        'email': 'user@example.com',
        'role': 'tenant',
        'status': 'active',
      };

      final user = UserModel.fromJson(json);
      expect(user.id, equals(1));
      expect(user.name, equals('Test User'));
      expect(user.role, equals('tenant'));
      expect(user.isTenant, isTrue);
      expect(user.isOwner, isFalse);
    });
  });
}
