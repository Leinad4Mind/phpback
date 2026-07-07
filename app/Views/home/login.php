<div class="col-md-7">
  <small><ol class="breadcrumb">
        <li><a href="<?= base_url() ?>">Feedback</a></li>
        <li class="active"><?= esc($lang['label_log_in']) ?></li>
  </ol></small>
  <?php if ($error === 'errorlogin'): ?>
    <p class="bg-danger" style="width:100%;padding:8px 10px;"><?= esc($lang['error_login']) ?></p>
  <?php elseif ($error === 'toomany'): ?>
    <p class="bg-danger" style="width:100%;padding:8px 10px;"><?= esc($lang['error_toomany']) ?></p>
  <?php elseif ($error === 'register'): ?>
    <p class="bg-success" style="width:100%;padding:8px 10px;"><?= esc($lang['text_registration_success']) ?></p>
  <?php elseif ($error === 'banned'): ?>
    <p class="bg-danger" style="width:100%;padding:8px 10px;"><?= ($ban === -1) ? esc($lang['error_banned_inf']) : esc(str_replace('%s', (string) $ban, $lang['error_banned'])) ?></p>
  <?php endif; ?>
  <form name="login-form" action="<?= base_url('action/login') ?>" method="POST">
    <?= csrf_field() ?>
    <div class="form-group">
      <label for="InputEmail"><?= esc($lang['form_email']) ?></label>
      <input type="email" class="form-control" id="InputEmail" placeholder="<?= esc($lang['form_email'], 'attr') ?>" name="email" required>
    </div>
    <div class="form-group">
      <label for="InputPassword"><?= esc($lang['form_password']) ?></label>
      <input type="password" class="form-control" id="InputPassword" placeholder="<?= esc($lang['form_password'], 'attr') ?>" name="password" required>
    </div>
    <label class="checkbox" for="checkbox1">
      <input type="checkbox" value="1" id="checkbox1" name="rememberme" data-toggle="checkbox">
        <?= esc($lang['form_remember']) ?>
    </label>
    <button type="submit" class="btn btn-primary"><?= esc($lang['label_log_in']) ?></button>
    <a href="<?= base_url('home/register') ?>"><span style="padding-left:10px"><?= esc($lang['text_create_an_account']) ?></span></a>
  </form>
</div>
<div class="col-md-2"></div>
