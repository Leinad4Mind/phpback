<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>PHPBack Installer</title>
	<style>
		body{font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:#1ABC9C;color:#2C3E50;margin:0;padding:40px}
		.card{max-width:560px;margin:0 auto;background:#fff;border-radius:8px;padding:28px 32px;box-shadow:0 8px 30px rgba(0,0,0,.15)}
		h1{margin:0 0 4px;font-size:22px} .sub{color:#7f8c8d;margin:0 0 20px;font-size:14px}
		label{display:block;font-size:13px;font-weight:600;margin:12px 0 4px}
		input,select{width:100%;padding:9px 10px;border:1px solid #dfe4ea;border-radius:5px;font-size:14px;box-sizing:border-box}
		button{margin-top:18px;background:#16A085;color:#fff;border:0;border-radius:5px;padding:11px 18px;font-size:15px;cursor:pointer}
		button:hover{background:#12856f}
		.msg{padding:10px 12px;border-radius:5px;margin-bottom:16px;font-size:14px}
		.ok{background:#eafaf1;color:#1e824c;border:1px solid #abebc6}
		.err{background:#fdedec;color:#c0392b;border:1px solid #f5b7b1}
		.badge{display:inline-block;background:#f39c12;color:#fff;border-radius:12px;padding:1px 9px;font-size:13px}
		a.btn{display:inline-block;margin-top:10px;margin-right:8px;text-decoration:none;background:#2C3E50;color:#fff;padding:9px 16px;border-radius:5px}
		code{background:#f4f6f7;padding:2px 5px;border-radius:3px}
	</style>
</head>
<body>
<div class="card">
	<h1>PHPBack Installer</h1>
	<p class="sub">Set up or upgrade your feedback system.</p>

	<?php if (! empty($message)): ?><div class="msg ok"><?= esc($message) ?></div><?php endif; ?>
	<?php if (! empty($error)): ?><div class="msg err"><?= esc($error) ?></div><?php endif; ?>

	<?php if ($state === 'noconfig'): ?>
		<div class="msg err">Cannot connect to the database.<br><small><?= esc($connectError ?? '') ?></small></div>
		<p>Enter your database connection details. They will be written to the project's <code>.env</code> file.</p>
		<form method="post" action="<?= base_url('install/run') ?>">
			<?= csrf_field() ?>
			<input type="hidden" name="action" value="saveconfig">
			<label>Driver</label>
			<select name="db_driver">
				<option value="MySQLi">MySQL / MariaDB</option>
				<option value="Postgre">PostgreSQL</option>
				<option value="SQLite3">SQLite</option>
			</select>
			<label>Host</label><input name="db_host" value="localhost">
			<label>Database name</label><input name="db_name" required>
			<label>Username</label><input name="db_user">
			<label>Password</label><input type="password" name="db_pass">
			<button type="submit">Save &amp; continue</button>
		</form>

	<?php elseif ($state === 'install'): ?>
		<div class="msg ok">Database connected. Ready for a fresh installation.</div>
		<form method="post" action="<?= base_url('install/run') ?>">
			<?= csrf_field() ?>
			<input type="hidden" name="action" value="install">
			<label>Site title</label><input name="site_title" value="PHPBack">
			<hr>
			<p class="sub">Create the administrator account</p>
			<label>Name</label><input name="admin_name" required>
			<label>Email</label><input type="email" name="admin_email" required>
			<label>Password (min. 6 characters)</label><input type="password" name="admin_password" minlength="6" required>
			<button type="submit">Install PHPBack</button>
		</form>

	<?php elseif ($state === 'upgrade'): ?>
		<div class="msg ok">An existing PHPBack database was detected.</div>
		<p>There <?= $pending === 1 ? 'is' : 'are' ?> <span class="badge"><?= (int) $pending ?></span> pending database migration<?= $pending === 1 ? '' : 's' ?> to apply. This is safe and will not remove existing data.</p>
		<form method="post" action="<?= base_url('install/run') ?>">
			<?= csrf_field() ?>
			<input type="hidden" name="action" value="upgrade">
			<button type="submit">Apply upgrade</button>
		</form>

	<?php else: /* done */ ?>
		<div class="msg ok">PHPBack is installed and the database is up to date. 🎉</div>
		<p>For security, consider removing or protecting the installer route once you are done.</p>
		<a class="btn" href="<?= base_url('home') ?>">Go to site</a>
		<a class="btn" href="<?= base_url('admin') ?>">Admin panel</a>
	<?php endif; ?>
</div>
</body>
</html>
