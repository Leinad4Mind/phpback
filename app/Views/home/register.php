<div class="col-md-9">
	<small><ol class="breadcrumb">
			<li><a href="<?= base_url() ?>">Feedback</a></li>
			<li class="active"><?= esc($lang['label_registration']) ?></li>
	  </ol></small>
	<h2><?= esc($lang['label_registration_form']) ?></h2>

  <?php
    $errors = [
        'recaptcha' => 'error_recaptcha',
        'name'      => 'error_name',
        'email'     => 'error_email',
        'pass'      => 'error_password',
        'pass2'     => 'error_passwords',
        'exists'    => 'error_email_exists',
        'toomany'   => 'error_toomany',
    ];
  ?>
  <?php if (isset($errors[$error])): ?>
    <p class="bg-danger" style="width:100%;padding:8px 10px;"><?= esc($lang[$errors[$error]]) ?></p>
  <?php endif; ?>

  <form name="registration-form" action="<?= base_url('action/register') ?>" method="POST" onsubmit="return validateForm()">
    <?= csrf_field() ?>
   	<div class="form-group">
      <label for="InputEmail"><?= esc($lang['form_email']) ?></label>
      <input type="email" class="form-control" id="InputEmail" placeholder="<?= esc($lang['form_email'], 'attr') ?>" name="email" required>
    </div>
    <div class="form-group">
      <label for="InputName"><?= esc($lang['form_full_name']) ?></label>
      <input type="text" class="form-control" id="InputName" placeholder="<?= esc($lang['form_full_name'], 'attr') ?>" name="name" required>
    </div>
    <div class="form-group">
      <label for="InputPassword"><?= esc($lang['form_password']) ?></label>
	  <div id="password-error-show" style="color:red"></div>
      <input type="password" class="form-control" id="InputPassword" placeholder="<?= esc($lang['form_password'], 'attr') ?>" name="password" minlength="6" required>
	</div>
    <div class="form-group">
      <label for="InputPassword2"><?= esc($lang['form_repeat_password']) ?></label>
      <input type="password" class="form-control" id="InputPassword2" placeholder="<?= esc($lang['form_repeat_password'], 'attr') ?>" name="password2" required>
    </div>
  <?php if ($recaptchapublic !== ''): ?>
    <script type="text/javascript" src="https://www.google.com/recaptcha/api.js" async defer></script>
    <div class="g-recaptcha" data-sitekey="<?= esc($recaptchapublic, 'attr') ?>"></div>
  <?php endif; ?>
    <div style="margin-top:10px"><button type="submit" class="btn btn-primary"><?= esc($lang['label_registration']) ?></button></div>
  </form>
</div>

<script>
function validateForm() {
    var pass = document.forms["registration-form"]["password"].value;
	var passVerify = document.forms["registration-form"]["password2"].value;
	var errorShowDiv = document.getElementById("password-error-show");
	var passMatchError = "<?= esc($lang['error_passwords'], 'js') ?>";
    if (pass != passVerify) {
		errorShowDiv.innerHTML = passMatchError;
        return false;
    }
}
</script>
