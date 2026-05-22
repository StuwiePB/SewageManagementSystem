/// Base URL of your BruDMS site as reachable from the phone (not localhost).
///
/// Set when running or building, for example:
///   flutter run --dart-define=BRUDMS_BASE_URL=https://your-herd-share-link.test
///
/// On the same Wi‑Fi you can use your PC LAN IP, e.g. http://192.168.1.10
/// (use the host/port Herd shows for this project).
const String brudmsBaseUrl = String.fromEnvironment(
  'BRUDMS_BASE_URL',
  defaultValue: 'http://sewagemanagementsystem.test',
);

/// First page to open (customer login).
const String brudmsStartPath = String.fromEnvironment(
  'BRUDMS_START_PATH',
  defaultValue: '/login',
);

Uri get brudmsStartUri {
  final base = Uri.parse(brudmsBaseUrl);
  final path = brudmsStartPath.startsWith('/') ? brudmsStartPath : '/$brudmsStartPath';
  return base.replace(path: path, query: null, fragment: null);
}
