## PHP Interface

Production-ready starter for user authentication in PHP: registration, login, email verification via SMTP, session-based access control, and a simple dashboard. Built with native PHP, MySQL (PDO), PHPMailer for email, and Dotenv for configuration.

### Requirements
- PHP 8.1+ with `pdo_mysql`
- Composer
- MySQL 5.7+ / MariaDB 10.3+
 - Ability to send email via SMTP (e.g., Gmail, SMTP provider)

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

### Features
- Registration with server-side validation
- Secure password hashing (bcrypt via `password_hash`)
- Login with session management and logout
- Email verification flow with signed token links
- Minimal dashboard protected by auth middleware-style checks
- Environment-based configuration via `.env`

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

### Architecture at a Glance
- `config.php`: Loads Dotenv and initializes a single PDO instance for MySQL
- `register.php` / `login.php`: Handle form POSTs, validate input, persist/read users
- `verify.php`: Confirms email using a token (time-bound/randomized)
- `dashboard.php`: Example protected page; checks session for authenticated users
- `db_event.php`: Shared database helpers (queries, inserts, token ops)

Flow overview:
1. User signs up → user row created with `verified=false` and a unique token
2. Email with verification link sent via PHPMailer → user clicks link
3. `verify.php` validates token → sets `verified=true`
4. Login allowed only after verification (unless explicitly disabled)

### What is a .env file?
`.env` is a plain text file that stores configuration as key-value pairs. It should never be committed to version control. Dotenv loads these values into environment variables so your code can read them without hardcoding secrets (database passwords, SMTP credentials, API keys). Keep `.env` private.

Typical values you set:
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`: database connection
- `MAIL_HOST`, `MAIL_PORT`, `MAIL_USER`, `MAIL_PASS`: SMTP settings
- `MAIL_FROM`, `MAIL_FROM_NAME`: sender identity

Minimal config reference:
- `DB_HOST`: Database host name or IP
- `DB_NAME`: Database/schema name
- `DB_USER`: Database user with required privileges
- `DB_PASS`: Database user password
- `MAIL_HOST`: SMTP host, e.g. `smtp.gmail.com`
- `MAIL_PORT`: SMTP port, typically `587` (TLS) or `465` (SSL)
- `MAIL_USER`: SMTP username (your email for Gmail)
- `MAIL_PASS`: SMTP password (App Password if Gmail)
- `MAIL_FROM`: From address used in emails
- `MAIL_FROM_NAME`: Human-friendly sender name

### Environment and Security Notes
- Ensure `display_errors` is disabled in production and proper error logging is configured
- Use strong, unique database credentials and least-privilege DB user
- Serve over HTTPS in production
- Replace placeholder secrets/tokens and mail settings if email verification is enabled
- Never commit `.env` to your repository; add it to `.gitignore`
 - Regenerate verification tokens on demand and keep short expirations for better security
 - Rate-limit registration and login endpoints to mitigate brute-force attempts
 - Sanitize and validate all inputs; escape outputs in templates

### Common Commands
```bash
# Install deps
composer install

# Update deps per constraints
composer update

# Start dev server
php -S 127.0.0.1:8000
```

### Email via Gmail (App Password)
You can use your Gmail account to send verification emails via SMTP. Google requires an App Password (not your normal password).

Steps to create a Gmail App Password:
1. Enable 2-Step Verification on your Google account (Security → 2-Step Verification)
2. Go to Security → App passwords (`https://myaccount.google.com/apppasswords`)
3. Select App: “Mail”, Device: “Other (Custom name)” (e.g., "PHP Interface"), then generate
4. Google shows a 16-character App Password — copy it

Use these values in your `.env`:
```dotenv
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=your_email@gmail.com
MAIL_PASS=your_16_char_app_password
MAIL_FROM=your_email@gmail.com
MAIL_FROM_NAME="PHP Interface"
```

Notes:
- Port 587 with TLS is recommended for Gmail
- App Passwords only work if 2-Step Verification is enabled
- Some hosting providers block SMTP ports; ensure 587 is allowed

### Installing PHPMailer and Dotenv
This project already includes them in `composer.json`. If starting fresh, run:
```bash
composer require phpmailer/phpmailer:^7.0 vlucas/phpdotenv:^5.6
```

Basic PHPMailer setup (illustrative):
```php
use PHPMailer\PHPMailer\PHPMailer;

$mailer = new PHPMailer(true);
$mailer->isSMTP();
$mailer->Host = getenv('MAIL_HOST');
$mailer->Port = (int) getenv('MAIL_PORT');
$mailer->SMTPAuth = true;
$mailer->Username = getenv('MAIL_USER');
$mailer->Password = getenv('MAIL_PASS');
$mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // for port 587
$mailer->setFrom(getenv('MAIL_FROM'), getenv('MAIL_FROM_NAME'));
```

Basic Dotenv bootstrap (illustrative):
```php
use Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
```

### Quick Start
1) `composer install`
2) Create and fill `.env` (DB + SMTP)
3) `php setup_db.php` to create DB/tables
4) Review `db_event.php` — contains reusable DB helpers (user CRUD, tokens, queries)
5) `php -S 127.0.0.1:8000` and open the site
6) Register, verify via email, then log in

### Troubleshooting
- If you see database connection errors, confirm values in `.env` and that MySQL is running
- If tables are missing, re-run `php setup_db.php`
- For 500 errors, check your PHP error log and file permissions
- For email issues:
  - Verify `.env` SMTP values, especially `MAIL_HOST`, `MAIL_PORT`, `MAIL_USER`, `MAIL_PASS`
  - For Gmail, ensure App Password is used and 2-Step Verification is enabled
  - Check firewall/host restrictions on port 587

### FAQ
- Why not commit `.env`? It contains secrets; use `.env.example` to share keys without values.
- Can I use another SMTP provider? Yes—set `MAIL_*` to your provider's values.
- Do I need SSL? Use HTTPS in production and TLS (port 587) for SMTP.

### License
This project is provided as-is; add your preferred license here.


