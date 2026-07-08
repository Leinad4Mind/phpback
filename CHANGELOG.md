# Changelog

All notable changes to this project will be documented in this file.

## [v2.0.2] - Unreleased
### Features
- **Sign in with Google:** Native Google OAuth2 login (authorization-code flow with PKCE, no new dependencies). Configure the Client ID/Secret in Admin Panel → System Settings; existing accounts are auto-linked by verified email and unknown emails are auto-registered.
- **Simplified URLs:** Public pages dropped the `home/` prefix (`/login`, `/register`, `/idea/42`, `/category/…`, `/profile/…`, `/postidea`). Old `home/…` URLs (bookmarks, links in sent emails) permanently redirect to the new ones.

### Bug Fixes
- **Infinite Ban Notice:** Fixed a 404 when indefinitely banned users were redirected to the ban notice page (`home/login/banned/-1`).

---

## [v2.0.1] - 2026-07-08
### Features
- **Multi-CAPTCHA Support:** Added native support for reCAPTCHA v2 (checkbox and invisible), reCAPTCHA v3, and Cloudflare Turnstile directly from the Admin Panel.
- **Admin Panel Refinements:** Modernized admin panel layout, removed redundant navigation links, and preserved original application logo colors without inversion.
- **Category Management:** Administrators can now edit Category descriptions directly from the Admin Panel.
- **Language Support:** Added missing translations for all CAPTCHA options across all languages with sorted language arrays.
- **Idea Status Management:** Administrators can now change the status of an idea even if it's already in the 'declined' (recusada) or 'completed' (finalizada) state.

### Bug Fixes
- **Wysiwyg Editor Styling:** Fixed editor colors to respect light/dark themes and ensure text remains readable (especially bold text).
- **Settings Duplication:** Fixed an issue where the application title would appear twice in the Admin Panel due to a database seeder duplication.

---

## [v2.0.0] - 2026-07-07
### Major Changes
- **Framework Upgrade:** Migrated from legacy CodeIgniter 2 to CodeIgniter 4.
- **System Requirements:** Upgraded requirements to PHP 8.2+ and MySQL 8.0+ / MariaDB 11.4+.

### Features & Security
- **Security Overhaul:** Full security pass including CSRF protection, output escaping, modern password hashing, and hardened fail-fast file uploads.
- **Installer & Upgrader:** New web installer and CLI upgrader for fresh installs and safe idempotent upgrades from v1.3.x.
- **Database:** Implemented database migrations and seeders, configurable table prefix, and safer upgrade paths (e.g. dropping legacy FK constraints).
- **Rebuilt Features:** Tags, attachments, status filters (including PR #164), and roles/permissions are now fully functional on the new framework.
- **Testing & CI:** Implemented PHPUnit test harness, extensive unit/feature tests, and GitHub Actions CI.
- **Modern UI & Vue Islands:** Replaced Bootstrap 3 with Tailwind CSS v4 and introduced progressively enhanced Vue "Islands" using Shadcn-Vue for interactive elements (voting, comments, tags, status).
- **Cleanup:** Removed insecure legacy HTTP auto-updater, legacy CI2 core files, and development SQLite files from tracking.

---

## [v1.3.2] - 2017-04-30
### Features
- Improved errors and validations #102
- Allow 3 character length names #96
- German language added #73
- Dutch language added #70

### Bug Fixing
- Fix errors #116 #115
- Fix ideas with '(' in the title #98 #100
- Fix default banned #95
- Fix email regex issue #88 #91
- Fix portuguese language #60

## [v1.3.1] - 2016-04-20
### Features
- Portuguese (european) language added #54

### Bug Fixing
- Get -1 after deleting idea #52
- Delete votes when an idea is deleted
- Fix possible vulnerabilities (Special thanks to John Page [hyp3rlinx] for reporting)
- AutoUpdate system improved and fixed.

## [v1.3.0] - 2016-03-26
### Features
- French language added #30
- Use mysqli as driver instead of mysql #27
- Security improvements to hashing library #32 #37
- .htaccess and compatibility improvements #40
- Automated Selenium test added #38 #39
- Reponsive design updates #46

### Bug Fixing
- #28 idea accounting even if there are not considered by admin
- #35 Fix pointer in admin panel tabs
- #45 Unable to delete comment

## [v1.2.0] - 2015-12-14
### Release Notes
- XSS security improved. (Thanks John Page [hyp3rlinx] for vulnerability reporting)
- Added Update System and composer.json. Now PHPBack version can be upgraded to the last version by 1 click from the admin panel.

## [v1.1c] - 2015-10-28
### Release Notes
- Fixes issues #9 and #12
- Added indonesia language

## [v1.1b] - 2015-10-22
### Release Notes
- Typo fixes
- Update installation process to be better supported
- Add link to FAQ.
- Fix cross-site issue
- Add Portuguese language support
- Security fix: Use preg_match instead of ereg
- Security fix: Add salt for crypt function
- Upgrade CodeIgniter version to 2.2.5

## [v1.1] - 2015-10-17
### Release Notes
- Typo fixes
- Update installation process to be better supported
- Add link to FAQ.
- Fix cross-site issue
- Add Portuguese language support
- Security fix: Use preg_match instead of ereg
- Security fix: Add salt for crypt function
- Upgrade CodeIgniter version to 2.2.5

## [v1.0a] - 2015-07-04
### Release Notes
- Add version 1.0 to system view

## [v1.0] - 2015-07-04
### Release Notes
- Minor fix

## [v1.0-beta] - 2014-11-23
### Release Notes
- Beta version of PHPBack.
