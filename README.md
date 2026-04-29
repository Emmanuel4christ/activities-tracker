# NEWN

`NEWN` is a plain PHP + SQLite web app that:

- tracks visitor activity on the public site
- identifies each visitor by IP address, country label, and a dedicated browser cookie
- shows page views, clicks, visibility changes, and form typing in the admin backend
- can notify Telegram and WhatsApp with IP-aware, non-sensitive visitor activity summaries when channels are configured
- automatically clears idle visitor sessions and related activity after 20 minutes of inactivity
- lets the site admin redirect a live visitor to another internal page from the control panel

The database is local and temporary in practice: SQLite is created automatically at `storage/newn.sqlite`, and you can reset the whole app by deleting that file.

## What is included

- `pages/`: separate public-facing PHP pages such as `home.php`, `pulse.php`, `journey.php`, and `contact.php`
- `admin/`: separate admin control panel with its own `index.php`, `logout.php`, and admin APIs
- `index.php`: root shim that redirects into `pages/home.php`
- `admin.php`: root shim that redirects into `admin/`
- `api/track.php`: receives visitor events
- `api/poll-command.php`: lets a visitor browser receive redirect commands
- `admin/api/live.php`: returns live sessions and recent events for the control panel
- `admin/api/command.php`: queues a redirect for a selected visitor session
- `storage/newn.sqlite`: auto-created on first run
- `storage/sessions/`: local PHP session storage

## Notifications

The alert pipeline uses the official Telegram Bot API and WhatsApp Cloud API endpoints, with throttling to avoid noisy bursts and with recent-activity summaries that do not include filtered sensitive fields.

Set these environment variables before starting PHP if you want notifications enabled:

- `NEWN_TELEGRAM_BOT_TOKEN`
- `NEWN_TELEGRAM_CHAT_ID`
- `NEWN_WHATSAPP_ACCESS_TOKEN`
- `NEWN_WHATSAPP_PHONE_NUMBER_ID`
- `NEWN_WHATSAPP_TO`
- optional `NEWN_WHATSAPP_GRAPH_VERSION` (defaults to `v23.0`)

PowerShell example:

```powershell
$env:NEWN_TELEGRAM_BOT_TOKEN = '123456:your-bot-token'
$env:NEWN_TELEGRAM_CHAT_ID = '123456789'
$env:NEWN_WHATSAPP_ACCESS_TOKEN = 'EA...'
$env:NEWN_WHATSAPP_PHONE_NUMBER_ID = '123456789012345'
$env:NEWN_WHATSAPP_TO = '15551234567'
```

## Public-site safety model

The public pages now avoid exposing:

- admin links
- visible IP, country, or cookie identity details
- OTP collection
- password collection
- full payment card collection
- biometric or facial verification capture
- direct identity-document upload

Instead, each page contains a different non-sensitive intake form for safe request triage and support routing.

## Visitor identity model

Each visitor session is identified with:

- IP address
- country label
- persistent `newn_visitor` cookie token

Country behavior:

- `127.0.0.1` and `::1` resolve as `Localhost`
- private LAN addresses resolve as `Private Network`
- public IPs can resolve to a country label through a lightweight live lookup when the server can reach the network
- if lookup is unavailable, the country label falls back to `Unknown`

## Default admin login

- Username: `admin`
- Password: `New12345`

The login page no longer exposes these credentials in the UI. Change them in [`lib/app.php`](./lib/app.php) if you want different defaults.

## Requirements

- PHP 8.1 or newer
- `pdo_sqlite` enabled

## Run on localhost

### Option 1: PHP built-in server

From this project folder:

```powershell
php -S 127.0.0.1:8000
```

If `php` is not on your PATH and you use XAMPP, you can run:

```powershell
C:\xampp\php\php.exe -S 127.0.0.1:8000
```

Then open:

- Public site: [http://127.0.0.1:8000/pages/home.php](http://127.0.0.1:8000/pages/home.php)
- Admin control panel: [http://127.0.0.1:8000/admin/](http://127.0.0.1:8000/admin/)

### Option 2: XAMPP / WAMP

1. Put the whole `NEWN` folder inside your web root such as `htdocs`.
2. Start Apache.
3. Open:
   - `http://localhost/NEWN/pages/home.php`
   - `http://localhost/NEWN/admin/`

## How to test the live monitoring

1. Open the public site in one browser window or profile.
2. Open the admin control panel in another window or profile and sign in.
3. On the public site:
   - move between the separate public pages in `pages/`
   - click buttons and links
   - submit the different safe intake forms on each page
4. In the admin control panel:
   - watch the session appear
   - review IP, country, and cookie token
   - review recent events and latest typed input
   - send the visitor to another page from the redirect form

Using separate browser windows or profiles makes the live demo easier to observe.

## Reset the temporary database

Delete the SQLite files in `storage/`:

- `newn.sqlite`
- `newn.sqlite-shm`
- `newn.sqlite-wal`

The app will recreate them on the next request.

## Notes

- Sensitive-looking field names such as password or card data are filtered and not shown in the event stream.
- Idle visitor rows, their activity history, and queued commands are purged automatically after 20 minutes without activity.
- The admin area uses stricter session cookies, CSRF protection for login/logout and route commands, no-store caching headers, additional browser isolation headers, and basic login-attempt throttling.
- The project is intentionally lightweight and framework-free so it is easy to run and extend locally.
