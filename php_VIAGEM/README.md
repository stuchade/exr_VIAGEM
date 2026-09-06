# How it works

## Architecture
- backend (PHP server): `backend/api.php` exposes a small JSON API (parcels / parcel / owner).
- frontend (HTML/JS client): `frontend/index.html` renders the parcels on a Leaflet map.
- `router.php` serves the frontend and dispatches API calls so both run from one origin.

## How to run
1. Start the server:
   ```
   make up
   ```
   (or: `php -S localhost:8000 router.php`)
2. Open http://localhost:8000/ in your browser.
