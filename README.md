# EventPro

EventPro is a lightweight event management web application built with PHP and MySQL. It provides basic event planning features such as creating events, managing guests, seating arrangements, vendors, timelines, and budgets.

## Features

- Create, update and delete events
- Manage guests and guest lists
- Configure seating arrangements
- Add and track vendors
- Build event timelines
- Add and manage budget items
- Simple user auth (signup / signin)

## Tech stack

- PHP (plain PHP files)
- MySQL (SQL dump included)
- HTML/CSS/JavaScript (assets in `assets/`)

## Prerequisites

- PHP 7.4+ (or compatible)
- MySQL or MariaDB
- A webserver (Apache/Nginx) or the PHP built-in server for local testing

## Quick setup (local)

1. Copy the project into your webroot or clone it locally:

```bash
# example: move to your local development folder
# git clone <repo-url> EventPro-main
cd /path/to/EventPro-main
```

2. Create a database and import the provided `database.sql` file:

```bash
# create DB (replace `eventpro` with your preferred name)
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS eventpro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
# import schema and initial data
mysql -u root -p eventpro < database.sql
```

3. Update database credentials in `config/database.php` to match your MySQL user/password and the database name (`eventpro` if you used the step above).

4. Serve the app locally. From the project root you can use PHP's built-in webserver for quick testing:

```bash
php -S localhost:8000
```

Then open http://localhost:8000/index.php (or simply http://localhost:8000) in your browser.

If using Apache/Nginx, place the project inside your webroot (for example, `/var/www/html/EventPro-main`) and ensure your VirtualHost/document root points to the project folder.

## Important files and structure

- `index.php` — main landing/dashboard
- `events.php`, `event_details.php` — event listing and details
- `guests.php` — guest management
- `seating.php` — seating layout / assignments
- `vendors.php` — vendor management
- `timeline.php` — event timeline
- `budget.php` — budget items
- `signin.php`, `signup.php`, `logout.php` (in `handlers/`) — authentication flow
- `database.sql` — SQL schema + sample data (import to create DB)
- `config/database.php` — DB connection settings (update this)
- `handlers/` — server-side action scripts (add/update/delete)
- `includes/` — shared header/footer/navbar
- `assets/` — CSS, JS and images

## Usage notes

- There is a signup flow (`signup.php`) to create an application user; no preset admin credentials are provided. Sign up from the web UI to start using the app.
- After logging in, use the navigation to create events, add guests, create seating and manage vendors/timeline/budget.

## Security & production checklist

- Do not use the PHP built-in server for production. Use Apache or Nginx.
- Set proper permissions on configuration files and avoid storing credentials in version control.
- Use HTTPS in production.
- Consider using prepared statements / parameterized queries if not already present to avoid SQL injection.
- Review file upload handling and sanitization if you add file uploads.

## Troubleshooting

- "Cannot connect to database": verify `config/database.php` credentials and that MySQL is running. Ensure you imported `database.sql` into the same database name used in `config/database.php`.
- "Page shows errors": enable display_errors only for local development. For production, log errors instead.

## Development & contribution

1. Fork the repo and make a branch for your feature.
2. Keep changes small and focused (feature or bugfix per branch).
3. Open a pull request with a description of what you changed and why.

If you'd like, I can help scaffold automated tests or add basic input validation as a small follow-up.

## License

This project is provided without an explicit license in this repository. If you want a permissive license, add a `LICENSE` file (for example, MIT).

## Contact

If you want additional README sections (screenshots, demo, Dockerfile, CI setup), tell me what you'd like and I can add them.

---
Generated for the EventPro project by a helper script.
