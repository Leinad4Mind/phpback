<nav class="navbar navbar-inverse" role="navigation">
  <div class="navbar-header">
    <div class="logosmall"><img src="<?= base_url('img/logo_small_free.png') ?>" alt="PHPBack"></div>
  </div>
  <div class="collapse navbar-collapse" id="navbar-collapse-01">
    <ul class="nav navbar-nav">
      <li class="<?= ($active === 'dashboard') ? 'active' : '' ?>"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
      <li class="<?= ($active === 'ideas') ? 'active' : '' ?>"><a href="<?= base_url('admin/ideas') ?>">Ideas and Comments</a></li>
      <?php if (is_admin(2)): ?>
        <li class="<?= ($active === 'users') ? 'active' : '' ?>"><a href="<?= base_url('admin/users') ?>">Users Management</a></li>
      <?php endif; ?>
      <?php if (is_admin(3)): ?>
        <li class="<?= ($active === 'system') ? 'active' : '' ?>"><a href="<?= base_url('admin/system') ?>">System Settings</a></li>
      <?php endif; ?>
    </ul>
    <p class="navbar-text navbar-right">Signed in as <span style="color:#27AE60"><?= esc(session('username')) ?></span>
      <form action="<?= base_url('action/logout') ?>" method="post" style="display:inline;margin-left:10px;">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-danger btn-xs">Log out</button>
      </form>
    </p>
  </div>
</nav>
