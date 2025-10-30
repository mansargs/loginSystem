## PHP Interface

A simple PHP authentication interface with user registration, login, email verification, and a basic dashboard. Uses Composer for dependencies and MySQL for persistence.

### Requirements
- PHP 8.1+ with `pdo_mysql`
- Composer
- MySQL 5.7+ / MariaDB 10.3+

### Project Structure
- `index.php`: Landing page
- `register.php`, `login.php`, `logout.php`: Auth pages
- `verify.php`: Email/verification handler
- `dashboard.php`: Protected area after login
- `config.php`: Database configuration and PDO bootstrap
- `setup_db.php`: One-time database/table bootstrap script
- `db_event.php`: Helper/database operations
- `style.css`: Basic styles
- `vendor/`: Composer dependencies

### Setup
1) Install dependencies
```bash
composer install --no-interaction --prefer-dist
```

2) Configure environment (.env)
Do NOT hardcode credentials in `config.php`. Create a `.env` file in the project root and put your settings there:
```bash
cp .env.example .env  # if the example file exists, otherwise create .env
```

Then edit `.env` and set your database and mail settings. At minimum the host name and other DB credentials must be set in `.env`:
```dotenv
# Database
DB_HOST=127.0.0.1
DB_NAME=php_interface
DB_USER=root
DB_PASS=

# Optional: Mail (if verification emails are used)
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USER=no-reply@example.com
MAIL_PASS=secret
MAIL_FROM=no-reply@example.com
MAIL_FROM_NAME="PHP Interface"
```
The app uses `vlucas/phpdotenv` to load these variables; `config.php` should read from `$_ENV`/`getenv()`.

3) Initialize database schema
Run the bootstrap script to create the database and tables (idempotent):
```bash
php setup_db.php
```

### Running Locally
Use PHP's built-in server from the project root:
```bash
php -S 127.0.0.1:8000
```
Open `http://127.0.0.1:8000/` in your browser.

### Usage
- Go to `/register.php` to create an account
- Check your email (or verification flow) and visit `/verify.php?token=...` if applicable
- Log in at `/login.php`, then access `/dashboard.php`
- Log out at `/logout.php`

### Environment and Security Notes
- Ensure `display_errors` is disabled in production and proper error logging is configured
- Use strong, unique database credentials and least-privilege DB user
- Serve over HTTPS in production
- Replace placeholder secrets/tokens and mail settings if email verification is enabled

### Common Commands
```bash
# Install deps
composer install

# Update deps per constraints
composer update

# Start dev server
php -S 127.0.0.1:8000
```

### Troubleshooting
- If you see database connection errors, confirm values in `.env` and that MySQL is running
- If tables are missing, re-run `php setup_db.php`
- For 500 errors, check your PHP error log and file permissions

### License
This project is provided as-is; add your preferred license here.


