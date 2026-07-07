<div class="row">
    <div class="col-md-10 col-md-offset-1 dashboard-center">
    <?= view('admin/dashboard/_nav', ['active' => 'system']) ?>
  <div>
      <h5>System Settings <small>(PHPBack v<?= esc($version) ?>)</small></h5>
      <ul class="nav nav-tabs">
        <li id="table1" class="active"><a onclick="showtable3('generaltable','table1');">General Settings</a></li>
        <li id="table2"><a onclick="showtable3('admintable','table2');">Create Admin</a></li>
        <li id="table3"><a onclick="showtable3('categorytable','table3');">Categories</a></li>
      </ul>
      <div id="generaltable">
          <form role="form" method="post" action="<?= base_url('adminaction/editsettings') ?>">
            <?= csrf_field() ?>
            <?php foreach ($settings as $setting): ?>
            <div class="form-group">
              <label><?= esc($setting->name) ?></label>
              <input type="text" class="form-control" name="setting-<?= (int) $setting->id ?>" value="<?= esc($setting->value, 'attr') ?>" style="width:300px">
            </div>
          <?php endforeach; ?>
            <div class="form-group">
              <button name="submit-changes" type="submit" class="btn btn-primary">Submit changes</button>
            </div>
          </form>
      </div>
      <div id="admintable" style="display:none">
      <table class="table table-condensed">
          <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Admin Level</th></tr></thead>
              <tbody>
            <?php foreach ($adminusers as $user): ?>
            <tr>
              <td><a href="<?= base_url('home/profile/' . $user->id . '/' . url_title((string) $user->name, '-', true)) ?>" target="_blank">#<?= (int) $user->id ?></a></td>
              <td><?= esc($user->name) ?></td>
              <td><?= esc($user->email) ?></td>
              <td><?= esc($user->isadmin) ?></td>
            </tr>
              <?php endforeach; ?>
          </tbody>
        </table>
        <form role="form" method="post" action="<?= base_url('adminaction/editadmin') ?>">
            <?= csrf_field() ?>
            <div class="form-group">
              <label>User ID</label>
              <input type="number" class="form-control" name="id" style="width:300px">
            </div>
            <div class="form-group">
              <label>Admin Level</label>
              <select class="form-control" name="level" style="width:300px">
                <option value="0">No Administration Rights</option>
                <option value="1">Ideas and Comments (Level 1)</option>
                <option value="2">Level 1 + User Management (Level 2)</option>
                <option value="3">Full Administration Rights (Level 3)</option>
              </select>
            </div>
            <div class="form-group">
              <button name="submit-create-admin" type="submit" class="btn btn-primary">Submit changes</button>
            </div>
          </form>
      </div>
      <div id="categorytable" style="display:none">
        <h4>Add a new Category</h4>
        <form role="form" method="post" action="<?= base_url('adminaction/addcategory') ?>">
            <?= csrf_field() ?>
            <div class="form-group">
              <label>Category name</label>
              <input type="text" class="form-control" name="name" style="width:300px">
              <small>(put an existing category name to change its description)</small>
            </div>
            <div class="form-group">
              <label>Category description</label>
              <textarea class="form-control" name="description" style="width:300px"></textarea>
            </div>
            <div class="form-group">
              <button name="add-category" type="submit" class="btn btn-primary">Add Category</button>
            </div>
        </form>
        <h4>Delete a category</h4>
        <form role="form" method="post" action="<?= base_url('adminaction/deletecategory') ?>">
          <?= csrf_field() ?>
          <div class="form-group">
              <label>Select category to delete</label>
              <select class="form-control" name="catid" style="width:300px">
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= (int) $cat->id ?>"><?= esc($cat->name) ?></option>
                <?php endforeach; ?>
              </select>
              <label class="checkbox" for="checkbox1">
                <input type="checkbox" value="1" id="checkbox1" name="ideas" data-toggle="checkbox">
                Delete category's ideas
              </label>
          </div>
          <div class="form-group">
              <button name="delete-category" type="submit" class="btn btn-primary">Delete category</button>
          </div>
        </form>
        <h4>Change names</h4>
        <form role="form" name="update-form" method="post" action="<?= base_url('adminaction/updatecategories') ?>">
            <?= csrf_field() ?>
            <?php foreach ($categories as $cat): ?>
              <div class="form-group">
                <input type="text" class="form-control" name="category-<?= (int) $cat->id ?>" style="width:300px" value="<?= esc($cat->name, 'attr') ?>">
              </div>
            <?php endforeach; ?>
            <div class="form-group">
              <button name="update-names" type="submit" class="btn btn-primary">Update names</button>
            </div>
        </form>
      </div>
  </div>
</div>
</div>
</body>
</html>
