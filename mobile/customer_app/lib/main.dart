import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:webview_flutter_android/webview_flutter_android.dart';
import 'package:webview_flutter_wkwebview/webview_flutter_wkwebview.dart';

import 'app_config.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const BrudmsCustomerApp());
}

class BrudmsCustomerApp extends StatelessWidget {
  const BrudmsCustomerApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'BruDMS',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF04BCFF),
          brightness: Brightness.dark,
        ),
        useMaterial3: true,
      ),
      home: CustomerWebViewShell(startUri: brudmsStartUri),
    );
  }
}

class CustomerWebViewShell extends StatefulWidget {
  const CustomerWebViewShell({super.key, required this.startUri});

  final Uri startUri;

  @override
  State<CustomerWebViewShell> createState() => _CustomerWebViewShellState();
}

class _CustomerWebViewShellState extends State<CustomerWebViewShell> {
  late final WebViewController _controller;
  var _loading = true;
  var _canGoBack = false;
  var _loadError = false;

  @override
  void initState() {
    super.initState();
    _controller = _createController();
    _controller.loadRequest(widget.startUri);
  }

  WebViewController _createController() {
    late final PlatformWebViewControllerCreationParams params;
    if (WebViewPlatform.instance is WebKitWebViewPlatform) {
      params = WebKitWebViewControllerCreationParams(
        allowsInlineMediaPlayback: true,
        mediaTypesRequiringUserAction: const <PlaybackMediaTypes>{},
      );
    } else {
      params = const PlatformWebViewControllerCreationParams();
    }

    final controller = WebViewController.fromPlatformCreationParams(params)
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setBackgroundColor(const Color(0xFF121820))
      ..setNavigationDelegate(
        NavigationDelegate(
          onPageStarted: (_) => setState(() {
            _loading = true;
            _loadError = false;
          }),
          onPageFinished: (_) async {
            final back = await _controller.canGoBack();
            if (!mounted) return;
            setState(() {
              _loading = false;
              _canGoBack = back;
            });
          },
          onWebResourceError: (_) {
            if (!mounted) return;
            setState(() {
              _loading = false;
              _loadError = true;
            });
          },
          onNavigationRequest: (_) => NavigationDecision.navigate,
        ),
      );

    final platform = controller.platform;
    if (platform is AndroidWebViewController) {
      platform.setMediaPlaybackRequiresUserGesture(false);
      platform.setGeolocationPermissionsPromptCallbacks(
        onShowPrompt: (_) async => const GeolocationPermissionsResponse(
          allow: true,
          retain: true,
        ),
      );
    }

    return controller;
  }

  Future<void> _reload() async {
    setState(() {
      _loadError = false;
      _loading = true;
    });
    await _controller.reload();
  }

  Future<bool> _handleBack() async {
    if (await _controller.canGoBack()) {
      await _controller.goBack();
      return false;
    }
    return true;
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: !_canGoBack,
      onPopInvokedWithResult: (didPop, result) async {
        if (didPop) return;
        final shouldPop = await _handleBack();
        if (shouldPop && context.mounted) {
          SystemNavigator.pop();
        }
      },
      child: Scaffold(
        backgroundColor: const Color(0xFF121820),
        appBar: AppBar(
          backgroundColor: const Color(0xFF121820),
          foregroundColor: Colors.white,
          title: const Text('BruDMS'),
          actions: [
            IconButton(
              tooltip: 'Back',
              onPressed: _canGoBack ? () => _controller.goBack() : null,
              icon: const Icon(Icons.arrow_back),
            ),
            IconButton(
              tooltip: 'Refresh',
              onPressed: _reload,
              icon: const Icon(Icons.refresh),
            ),
          ],
        ),
        body: SafeArea(
          child: Stack(
            children: [
              WebViewWidget(controller: _controller),
              if (_loading)
                const Center(
                  child: CircularProgressIndicator(color: Color(0xFF04BCFF)),
                ),
              if (_loadError)
                Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.wifi_off, size: 48, color: Colors.white70),
                        const SizedBox(height: 16),
                        Text(
                          'Cannot reach the server.\n\n'
                          'Use a URL your phone can open (Herd Share or LAN IP), then run:\n'
                          'flutter run --dart-define=BRUDMS_BASE_URL=YOUR_URL',
                          textAlign: TextAlign.center,
                          style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                                color: Colors.white70,
                              ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          widget.startUri.toString(),
                          textAlign: TextAlign.center,
                          style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                color: Colors.white38,
                              ),
                        ),
                        const SizedBox(height: 20),
                        FilledButton(
                          onPressed: _reload,
                          child: const Text('Try again'),
                        ),
                      ],
                    ),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }
}
