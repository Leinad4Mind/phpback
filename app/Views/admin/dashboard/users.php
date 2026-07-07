<div class="row">
    <div class="col-md-10 col-md-offset-1 dashboard-center">
    <?= view('admin/dashboard/_nav', ['active' => 'users']) ?>
    <div>
      <h5>Users Management</h5>
      <ul class="nav nav-tabs">
        <li id="table1" class="active"><a onclick="showtable2('newuserstable','table1');">New Users</a></li>
        <li id="table2"><a onclick="showtable2('bannedtable','table2');">Banned List</a></li>
        <li id="table3"><a onclick="showtable2('bantable','table3');">Ban User</a></li>
      </ul>
        <table id="newuserstable" class="table table-condensed">
          <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Votes</th><th></th></tr></thead>
              <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
              <td><a href="<?= base_url('home/profile/' . $user->id . '/' . url_title((string) $user->name, '-', true)) ?>" target="_blank">#<?= (int) $user->id ?></a></td>
              <td><?= esc($user->name) ?></td>
              <td><?= esc($user->email) ?></td>
              <td><?= esc($user->votes) ?></td>
              <td><div class="pull-right">
                <a href="<?= base_url('admin/users/' . $user->id) ?>"><button type="button" class="btn btn-danger btn-sm" style="width:130px">Ban User</button></a>
              </div></td>
            </tr>
              <?php endforeach; ?>
          </tbody>
        </table>
        <table id="bannedtable" class="table table-condensed" style="display:none">
          <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Until (d/m/y)</th><th></th></tr></thead>
              <tbody>
            <?php foreach ($banned as $user): ?>
            <tr>
              <td><a href="<?= base_url('home/profile/' . $user->id . '/' . url_title((string) $user->name, '-', true)) ?>" target="_blank">#<?= (int) $user->id ?></a></td>
              <td><?= esc($user->name) ?></td>
              <td><?= esc($user->email) ?></td>
              <td><?php
                if ((int) $user->banned === -1) {
                    echo 'Banned indefinitely.';
                } else {
                    $d = $user->banned % 100;
                    $m = ((int) ($user->banned / 100)) % 100;
                    $y = (int) ($user->banned / 10000);
                    echo 'Banned until ' . esc("$d/$m/$y");
                }
              ?></td>
              <td><div class="pull-right">
                <form method="post" action="<?= base_url('adminaction/unban') ?>" style="display:inline">
                  <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $user->id ?>">
                  <button type="submit" class="btn btn-warning btn-sm" style="width:130px">Disable ban</button>
                </form>
              </div></td>
            </tr>
              <?php endforeach; ?>
          </tbody>
        </table>
      <div id="bantable" style="display:none">
          <form role="form" method="post" action="<?= base_url('adminaction/banuser') ?>">
            <?= csrf_field() ?>
            <div class="form-group">
              <label>User ID</label>
              <input type="number" class="form-control" name="id" value="<?= esc($idban ?? '', 'attr') ?>" style="width:130px" maxlength="9">
            </div>
            <div class="form-group">
              <label>Ban length in days</label>
              <input type="number" class="form-control" name="days" style="width:100px" maxlength="4"> (0 for indefinite ban)
            </div>
            <div class="form-group">
              <button name="banuser" type="submit" class="btn btn-primary">Ban User</button>
            </div>
          </form>
      </div>
</div>
</div>
</div>
  </body>
  </html>
