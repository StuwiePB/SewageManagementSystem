import 'package:brudms_customer/main.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('app shell builds', (WidgetTester tester) async {
    await tester.pumpWidget(
      const BrudmsCustomerApp(),
    );
    expect(find.text('BruDMS'), findsOneWidget);
  });
}
