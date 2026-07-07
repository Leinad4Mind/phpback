<?php $isOwner = is_logged_in() && (int) $user->id === current_user_id(); ?>
<?php $err = (int) (session('error') ?? $error); ?>
<div class="col-md-9">
	<small><ol class="breadcrumb">
			  <li><a href="<?= base_url() ?>">Feedback</a></li>
			  <li><?= esc($lang['label_profiles']) ?></li>
			  <li class="active"><?= esc($user->name) ?></li>
	</ol></small>
	<h5><?= esc($user->name) ?><?php if ($user->isadmin): ?><span class="label label-danger" style="margin-left:7px;">Admin</span> <?php endif; ?></h5>
	<?php if ($isOwner): ?>
	<div><?= esc($user->votes) ?> <?= esc($lang['text_votes_left']) ?></div>
	<?php elseif (is_admin(2)): ?>
	<a href="<?= base_url('admin/users/' . $user->id) ?>" target="_blank"><button type="button" class="btn btn-danger btn-sm" style="width:130px"><?= esc($lang['label_ban_user']) ?></button></a>
	<?php endif; ?>
	<hr>
	<?php if ($isOwner): ?>
	<div id="account-settings">
	<?php if ($err > 0): ?>
		<p class="bg-danger" style="padding:8px 20px;">
			<?php if ($err === 1) echo esc($lang['error_passwords']); ?>
			<?php if ($err === 2) echo esc($lang['error_password_old']); ?>
			<?php if ($err === 3) echo esc($lang['error_password']); ?>
		</p>
	<?php endif; ?>
	<?php if (session('message') === 'passwordchanged'): ?>
		<p class="bg-success" style="padding:8px 20px;"><?= esc($lang['message_password_changed']) ?></p>
	<?php endif; ?>
	<small>
		<ul class="nav nav-tabs">
	  		<li id="table4" class="active"><a onclick="showtable4('resetvotestable','table4');"><?= esc($lang['label_reset_votes']) ?></a></li>
	  		<li id="table5"><a onclick="showtable4('changepasswordtable','table5');"><?= esc($lang['label_change_password']) ?></a></li>
	  		<?php if (is_admin(1)): ?><li><a href="<?= base_url('admin') ?>" target="_blank">ADMIN PANEL</a></li><?php endif; ?>
		</ul>
		<table id="resetvotestable" class="table table-striped">
			<thead><th>Idea</th><th>Votes</th><th></th></thead>
			<tbody>
			<?php foreach (($votes ?? []) as $vote): ?>
				<tr>
					<td><a href="<?= esc($vote['idea']->url, 'attr') ?>"><?= esc($vote['idea']->title) ?></a></td>
					<td><?= esc($vote['number']) ?></td>
					<td>
						<form method="post" action="<?= base_url('action/unvote') ?>" style="display:inline">
							<?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $vote['id'] ?>">
							<button type="submit" class="btn btn-warning btn-sm" style="width:130px"><?= esc($lang['label_delete_votes']) ?></button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<div id="changepasswordtable" style="display:none">
			<form role="form" name="password-change-form" method="post" action="<?= base_url('action/changepassword') ?>" onsubmit="return validateForm()">
				<?= csrf_field() ?>
	            <div class="form-group">
	              <label><?= esc($lang['form_password_old']) ?></label>
	              <input type="password" class="form-control" name="old" style="width:150px" required>
	            </div>
	            <div class="form-group">
	              <label><?= esc($lang['form_password_new']) ?></label>
				  <div id="password-error-show" style="color:red"></div>
	              <input type="password" class="form-control" name="new" style="width:150px;" minlength="6" required>
	            </div>
	            <div class="form-group">
	              <label><?= esc($lang['from_password_new_repeat']) ?></label>
	              <input type="password" class="form-control" name="rnew" style="width:150px" required>
	            </div>
	            <div class="form-group">
	              <button type="submit" class="btn btn-primary"><?= esc($lang['label_change_password']) ?></button>
	            </div>
        	</form>
		</div>
	</small>
	</div>
	<?php endif; ?>
	<small>
	<ul class="nav nav-tabs">
  		<li id="table1" class="active"><a onclick="showtable('activitytable','table1');"><?= esc($lang['label_activity']) ?></a></li>
  		<li id="table2"><a onclick="showtable('ideastable','table2');"><?= esc($lang['label_ideas']) ?> <span class="badge"><?= count($ideas) ?></span></a></li>
  		<li id="table3"><a onclick="showtable('commentstable','table3');"><?= esc($lang['label_comments']) ?></a></li>
	</ul>
	<div id="listing">
		<table id="activitytable" class="table table-striped">
			<thead><tr><th><?= esc($lang['label_log']) ?></th><th><?= esc($lang['label_date']) ?></th></tr></thead>
      		<tbody>
      			<?php foreach ($logs as $log): ?>
				<tr><td><?= esc($log->content) ?></td><td><?= esc($log->date) ?></td></tr>
			    <?php endforeach; ?>
			</tbody>
		</table>
		<table id="ideastable" class="table table-condensed" style="display:none">
			<thead><tr>
			  <th><?= esc($lang['label_idea']) ?></th><th><?= esc($lang['label_category']) ?></th>
			  <th><?= esc($lang['label_comments']) ?></th><th><?= esc($lang['label_votes']) ?></th><th><?= esc($lang['label_date']) ?></th>
			</tr></thead>
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
					<td><a href="<?= esc($idea->url, 'attr') ?>"><?= esc($idea->title) ?></a></td>
					<td><?= esc($categories[$idea->categoryid]->name ?? '') ?></td>
					<td><?= esc($idea->comments) ?> <?= esc($lang['label_comments']) ?></td>
					<td><?= esc($idea->votes) ?> <?= esc($lang['label_votes']) ?></td>
					<td><?= esc($idea->date) ?></td>
				</tr>
			    <?php endforeach; ?>
			</tbody>
		</table>
		<table id="commentstable" class="table table-striped" style="display:none">
			<thead><tr><th><?= esc($lang['label_commented']) ?></th><th><?= esc($lang['label_date']) ?></th></tr></thead>
      		<tbody>
      			<?php foreach ($comments as $comment): ?>
				<tr>
					<td><a href="<?= esc($comment['idea']->url, 'attr') ?>"><?= esc($comment['idea']->title) ?></a></td>
					<td><?= esc($comment['date']) ?></td>
				</tr>
			    <?php endforeach; ?>
			</tbody>
		</table>
	</div>
	</small>
</div>

<script>
function validateForm() {
    var pass = document.forms["password-change-form"]["new"].value;
	var passVerify = document.forms["password-change-form"]["rnew"].value;
	var errorShowDiv = document.getElementById("password-error-show");
	var passMatchError = "<?= esc($lang['error_passwords'], 'js') ?>";
    if (pass != passVerify) {
		errorShowDiv.innerHTML = passMatchError;
        return false;
    }
}
</script>
