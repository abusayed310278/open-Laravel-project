import 'package:flutter_test/flutter_test.dart';

import 'package:mobile/main.dart';

void main() {
  testWidgets('App boots to the splash screen', (WidgetTester tester) async {
    await tester.pumpWidget(const OpenboxApp());

    expect(find.text('Openbox'), findsOneWidget);
  });
}
