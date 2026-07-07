# CodeIgniter 2 → 4 migration — feature parity

This document confirms that everything from the legacy CodeIgniter 2 codebase
(`system/`, `application/`, root `index.php`, `install/`) was ported to the
CodeIgniter 4 app before the legacy code was removed. Nothing was dropped except
the insecure HTTP auto-updater (replaced by migrations + the installer).

## Legacy → CI4 map

| Legacy (CI2) | CI4 equivalent |
|---|---|
| `install/` (index/install1/install2 + `database_tables.sql`) — collect DB creds, write `application/config/database.php`, create schema, insert default settings, create admin | `app/Controllers/Install.php` + `app/Views/install/index.php` — collect DB creds **+ port + table prefix**, write `.env`, run **migrations** (schema), run **seeds** (settings + roles), create admin. Detects fresh-install vs upgrade vs done. |
| `database_tables.sql` (9 tables) | `app/Database/Migrations/2014-01-01-000001_CreateCoreTables.php` (idempotent) — users, ideas, comments, votes, categories, settings, flags, logs |
| Default settings inserted by `install2.php` (title, language, maxvotes=20, max_results=10, welcometext-title, welcometext-description, mainmail, smtp-host/port/user/pass, recaptchapublic/private) | `app/Database/Seeds/SettingsSeeder.php` — same 13 keys, same defaults, idempotent (preserves existing values on upgrade) |
| `controllers/home.php` | `app/Controllers/Home.php` (index/category/search/idea/profile/login/postidea/register) |
| `controllers/action.php` | `app/Controllers/Action.php` (register/login/logout/vote/unvote/changepassword/newidea/comment/flag) |
| `controllers/admin.php` | `app/Controllers/Admin.php` (index/dashboard/ideas/users/system) |
| `controllers/adminaction.php` | `app/Controllers/Adminaction.php` (login/banuser/unban/deletecomment/deleteidea/approveidea/ideastatus/editsettings/editadmin/addcategory/updatecategories/deletecategory) |
| `models/get.php` + `models/post.php` (god models) | `app/Models/*` (Idea/User/Comment/Vote/Category/Setting/Log/Flag/Tag/Attachment/Role/Permission/RememberToken) |
| `libraries/Hashing.php` (bcrypt `crypt`) | `password_hash`/`password_verify` in `UserModel`; legacy `$2a$` hashes verify and re-hash on login |
| `_sessions` remember-me (rand tokens) | `remember_tokens` (selector + hashed validator, `random_bytes`/`hash_equals`) + CI4 sessions |
| `libraries/Display.php` (slugify) | `url_title()` helper |
| `language/*/{default,log}_lang.php` | `app/Language/phpback/english.php` (merged array, exposed as `$lang`) |
| Auto-updater (`visualappeal/php-auto-update`, HTTP) | **Removed** (supply-chain risk). Upgrades happen via `php spark migrate` / the installer. |
| Tags / attachments / filters / roles (broken CI4-on-CI2 PRs) | Rebuilt to work: `TagModel`, validated uploads in `writable/uploads` + `Download` controller, homepage filters, roles/permissions seeds |
| Upstream PR #164 (status filter per category) | `Home::category()` + status dropdown in `category_ideas.php` |

## Upgrade safety (1.3.x → CI4)
- Core-table migrations are guarded with `tableExists`/`fieldExists`, so an existing DB keeps its data; only new tables/columns are added.
- New feature tables carry **no FK to the legacy `ideas`** (its `id` is signed INT), avoiding InnoDB errno 150; integrity is enforced in `IdeaModel::deleteIdea()`.
- Table prefix (`voice_`) is configurable in the installer / `.env`.
- `users.role_id` is backfilled from `isadmin`.
- Existing users keep their passwords.
