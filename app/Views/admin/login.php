<div class="login-screen">
	<div class="login-form">
	<form action="<?= base_url('adminaction/login') ?>" method="POST">
		<?= csrf_field() ?>
		<div class="loginlogo">
			<img src="<?= base_url('img/logo_free.png') ?>" alt="PHPBack" />
		</div>
		<?php if ($error === 'noadmin'): ?>
			<div style="color:#C0392B;font-size:20px">You are not an admin</div>
		<?php else: ?>
			<?php if ($error === 'error'): ?>
				<div style="color:#C0392B;font-size:15px">Email or password are incorrect</div>
			<?php elseif ($error === 'toomany'): ?>
				<div style="color:#C0392B;font-size:15px">Too many attempts. Please try again later.</div>
			<?php endif; ?>
			<div class="form-group">
			  <input type="email" class="form-control login-field" placeholder="Enter your email" id="login-name" name="email" required />
			  <label class="login-field-icon fui-user" for="login-name"></label>
			</div>
			<div class="form-group">
			  <input type="password" class="form-control login-field" placeholder="Password" id="login-pass" name="password" required />
			  <label class="login-field-icon fui-lock" for="login-pass"></label>
			</div>
			<input type="submit" class="btn btn-primary btn-lg btn-block" value="Log In">
		<?php endif; ?>
	</form>
	</div>
</div>
</body>
</html>
