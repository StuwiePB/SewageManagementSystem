# BruDMS Customer (WebView shell)

Full-screen app that loads the existing **customer web portal** (login, dashboard, reports, chat, etc.). No duplicate UI—same Laravel site as in the browser.

## 1. Make the site reachable on your phone

`http://sewagemanagementsystem.test` only works on your PC. On the phone, use one of:

| Method | What to do |
|--------|------------|
| **Herd Share** | In Herd, share this site and copy the HTTPS URL. |
| **Same Wi‑Fi** | Find your PC’s IP (`ipconfig`), open `http://YOUR_IP/...` on the phone in Chrome first. Use the exact host/port Herd uses. |

Confirm login works in **Chrome on the phone** before using the app.

## 2. Run on a physical phone (no emulator)

**Android**

1. Enable **Developer options → USB debugging** on the phone.
2. Connect USB, allow debugging.
3. From this folder:

```powershell
cd mobile\customer_app
flutter pub get
flutter devices
flutter run --dart-define=BRUDMS_BASE_URL=https://YOUR-PHONE-REACHABLE-URL
```

Use your real base URL (no trailing path), e.g. Herd Share `https://abc123.share.herd.app` or `http://192.168.0.12`.

Optional: different start page (default is `/login`):

```powershell
flutter run --dart-define=BRUDMS_BASE_URL=https://YOUR-URL --dart-define=BRUDMS_START_PATH=/login
```

**Install APK without USB (after a successful run)**

```powershell
flutter build apk --debug --dart-define=BRUDMS_BASE_URL=https://YOUR-URL
```

APK: `build\app\outputs\flutter-apk\app-debug.apk` — copy to the phone and install.

**iPhone** requires a Mac with Xcode for `flutter run` / TestFlight.

## 3. App bar

- Thin native bar: **Back** (WebView history), **Refresh**, title **BruDMS**.
- Android system back also walks WebView history, then exits the app.

## 4. Limits (wrapper)

- Report **camera / file** pickers depend on the WebView; if something fails, test the same step in Chrome on the phone.
- **Reverb / live support chat** needs the broadcast URL to be reachable from the phone (same as the web app).
- Production: set `BRUDMS_BASE_URL` to your public HTTPS domain and prefer `flutter build apk --release`.

## Project layout

- `lib/app_config.dart` — base URL and start path (`BRUDMS_*` dart-defines)
- `lib/main.dart` — WebView shell
