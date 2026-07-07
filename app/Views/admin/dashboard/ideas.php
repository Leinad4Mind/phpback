<div class="row">
    <div class="col-md-10 col-md-offset-1 dashboard-center">
    <?= view('admin/dashboard/_nav', ['active' => 'ideas']) ?>
    <div>
      <h5>Ideas and comments</h5>
      <ul class="nav nav-tabs">
        <li id="table1" class="active"><a onclick="showtable('newideastable','table1');">New Ideas <span class="badge"><?= (int) $newideas_num ?></span></a></li>
        <li id="table2"><a onclick="showtable('allideastable','table2');">All Ideas</a></li>
        <li id="table3"><a onclick="showtable('commentstable','table3');">Flagged comments</a></li>
      </ul>
      <div id="listing">
        <table id="newideastable" class="table table-condensed">
          <thead><tr><th>Idea</th><th>Category</th><th>Comments</th><th>Votes</th><th>Date</th></tr></thead>
              <tbody>
                <?php foreach ($newideas as $idea): ?>
            <tr class="<?= $idea->status === 'new' ? 'active' : '' ?>">
              <td><a href="<?= base_url('home/idea/' . $idea->id . '/' . url_title((string) $idea->title, '-', true)) ?>" target="_blank"><?= esc($idea->title) ?></a></td>
              <td><?= esc($categories[$idea->categoryid]->name ?? '') ?></td>
              <td><?= esc($idea->comments) ?> Comments</td>
              <td><?= esc($idea->votes) ?> Votes</td>
              <td><?= esc($idea->date) ?></td>
            </tr>
              <?php endforeach; ?>
          </tbody>
        </table>
        <div id="allideastable" class="row" style="display:none">
    <div class="col-md-4">
            <form role="form" method="post" action="<?= base_url('admin/ideas') ?>">
            <?= csrf_field() ?>
            <table>
            <thead><tr><td><label>Status</label></td><td><label>Categories</label></td></tr></thead>
            <tbody>
              <tr>
               <td>
              <div class="form-group">
                <?php foreach (['completed' => 'Completed', 'started' => 'Started', 'planned' => 'Planned', 'considered' => 'Under Consideration', 'declined' => 'Declined'] as $key => $label): ?>
                <label class="checkbox">
                  <input type="checkbox" <?= ! empty($form['status-' . $key]) ? 'checked="checked"' : '' ?> name="status-<?= $key ?>" data-toggle="checkbox"> <?= $label ?>
                </label>
                <?php endforeach; ?>
              </div>
              </td>
              <td style="padding-left:10px;width:250px;vertical-align:top">
              <div class="form-group">
                <?php foreach ($categories as $category): ?>
                  <label class="checkbox">
                    <input type="checkbox" <?= ! empty($form['category-' . $category->id]) ? 'checked="checked"' : '' ?> name="category-<?= (int) $category->id ?>" data-toggle="checkbox"> <?= esc($category->name) ?>
                  </label>
                <?php endforeach; ?>
              </div>
              </td>
              </tr>
              <tr>
              <td>
                <select class="form-control" name="orderby">
                  <option value="votes">Order by Votes</option>
                  <option value="id" <?= ($form['orderby'] === 'id') ? 'selected' : '' ?>>Order by Date</option>
                  <option value="title" <?= ($form['orderby'] === 'title') ? 'selected' : '' ?>>Order by Title</option>
                </select>
               </td>
               <td style="padding-left:10px;">
                 <label class="checkbox">
                    <input type="checkbox" <?= ! empty($form['isdesc']) ? 'checked' : '' ?> name="isdesc" data-toggle="checkbox"> Decreasing order
                  </label>
               </td>
              </tr>
              <tr><td colspan="2" style="padding-top:5px;padding-bottom:10px">
                <button type="submit" class="btn btn-primary" style="width:160px">Search</button>
              </td></tr>
              </tbody>
              </table>
            </form>
          </div>
    <div class="col-md-7">
          <table class="table table-condensed" style="font-size:15px;width:100%">
            <thead><tr><th>Idea</th><th>Category</th><th>Votes</th><th>Date</th></tr></thead>
          <tbody>
            <?php foreach ($ideas as $idea): ?>
        <tr class="<?php
        switch ($idea->status) {
          case 'considered': echo 'active'; break;
          case 'declined': echo 'danger'; break;
          case 'started': echo 'success'; break;
          case 'planned': echo 'warning'; break;
          case 'completed': echo 'info'; break;
        } ?>">
          <td><a href="<?= base_url('home/idea/' . $idea->id . '/' . url_title((string) $idea->title, '-', true)) ?>" target="_blank"><?= esc($idea->title) ?></a></td>
          <td><?= esc($categories[$idea->categoryid]->name ?? '') ?></td>
          <td><?= esc($idea->votes) ?> Votes</td>
          <td><?= esc($idea->date) ?></td>
        </tr>
          <?php endforeach; ?>
      </tbody>
          </table>
          </div>
      </div>
      <table id="commentstable" class="table table-condensed" style="display:none;font-size:15px;">
          <thead><tr><th>ID</th><th>Comment</th><th>Flags</th></tr></thead>
          <tbody>
                <?php foreach ($flags as $comment): ?>
                  <tr>
                  <td>
                    Comment: #<?= (int) $comment->id ?>
                    <br>User: <a href="<?= base_url('admin/users/' . $comment->userid) ?>">#<?= (int) $comment->userid ?></a>
                    <br>Idea: <a href="<?= base_url('home/idea/' . $comment->ideaid) ?>" target="_blank">#<?= (int) $comment->ideaid ?></a>
                  </td>
                  <td><samp><?= esc($comment->content) ?></samp></td>
                  <td>
                    <span style="font-size:17px;">Flagged <span class="badge"><?= (int) $comment->votes ?></span> times</span>
                    <div class="pull-right">
                      <form method="post" action="<?= base_url('adminaction/deletecomment') ?>" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this comment?');">
                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $comment->id ?>">
                        <button type="submit" class="btn btn-warning btn-sm" style="width:130px">Delete Comment</button>
                      </form>
                      <?php if (is_admin(2)): ?><a href="<?= base_url('admin/users/' . $comment->userid) ?>"><button type="button" class="btn btn-danger btn-sm" style="width:130px">Ban User</button></a><?php endif; ?>
                    </div>
                  </td>
                  </tr>
                <?php endforeach; ?>
          </tbody>
        </table>
    </div>
</div>
</div>
</div>
  </body>
  </html>
