![PHPBack](http://www.phpback.org/images/logo_300.png)


## About
PHPBack is an open source feedback system you can use for your website. It gives your customers a way to communicate their ideas to improve your products.

This repository has been **migrated from the legacy CodeIgniter 2 codebase to CodeIgniter 4**, with a full security pass (CSRF protection, output escaping, modern password hashing, hardened file uploads) and the tags / attachments / status-filter features rebuilt to actually work on the new framework.

Please, visit the project website for more information: [http://www.phpback.org/](http://www.phpback.org/)

## Requirements
* PHP 8.1+
* MySQL 5.7+ / MariaDB 10.3+ (or PostgreSQL / SQLite for development)
* Composer

## Installation
1. Clone the repository and install dependencies:
   ```
   composer install
   ```
2. Copy `env` to `.env` and set at least:
   ```
   CI_ENVIRONMENT = production
   app.baseURL = 'https://your-domain/'
   database.default.hostname = localhost
   database.default.port = 3306
   database.default.database = phpback
   database.default.username = your_db_user
   database.default.password = your_db_password
   database.default.DBDriver = MySQLi
   # Table prefix — existing 1.3.x installs use `voice_`; leave empty for none.
   database.default.DBPrefix =
   ```
   Works with MySQL 5.7+/8 and MariaDB 10.3+/11.x.
3. Point your web server's document root at the `public/` directory. (On shared
   hosting the included root `.htaccess` forwards requests into `public/`.)
4. Open `https://your-domain/install` in a browser and follow the installer. It
   creates the database schema, seeds defaults and lets you create the first
   administrator.

Alternatively, from the command line:
```
php spark migrate
php spark db:seed SettingsSeeder
php spark db:seed RolesPermissionsSeeder
```

## Upgrading from 1.3.x
Point `.env` at your **existing** database — including the table prefix your
install uses (e.g. `database.default.DBPrefix = voice_`) — and open `/install`
(or run `php spark migrate`). The migrations are idempotent: your existing
tables and data are left untouched and only the new tables/columns are added.
Existing users keep their passwords (legacy bcrypt hashes are verified and
transparently re-hashed on the next login).

## License
PHPBack is licensed under GPLv3 (see `LICENSE.TXT`). Built with the CodeIgniter 4 framework.

`Ivan Diaz <ivan@phpback.org> © 2014`
