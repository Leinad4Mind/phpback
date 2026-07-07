<div class="col-md-9">
			<div class="breadcrumb-wrapper"><ol class="breadcrumb">
			  <li><a href="<?= base_url() ?>">Feedback</a></li>
			  <li><a href="<?= esc(base_url('home/category/' . $idea->categoryid . '/' . url_title($categories[$idea->categoryid]->name, '-', true)), 'attr') ?>"><?= esc($categories[$idea->categoryid]->name) ?></a></li>
			  <li class="active"><?= esc($idea->title) ?></li>
			</ol></div>

			<div class="row">
				<div class="col-xs-12 col-sm-2">
					<div class="vote-count-box view-idea-vote">
						<span style="color:#3498DB;margin-top:-10px"><b><?= esc(number_format((int) $idea->votes)) ?></b></span><br>
						<div style="margin-top:-10px"><small><?= esc($lang['label_votes']) ?></small></div>
					</div>
					<?php if (is_logged_in()): ?>
					<form method="post" action="<?= base_url('action/vote') ?>" class="view-idea-vote">
						<?= csrf_field() ?>
						<input type="hidden" name="ideaid" value="<?= (int) $idea->id ?>">
						<div class="btn-group">
							<button type="submit" name="votes" value="1" class="btn btn-primary btn-sm">1</button>
							<button type="submit" name="votes" value="2" class="btn btn-primary btn-sm">2</button>
							<button type="submit" name="votes" value="3" class="btn btn-primary btn-sm">3</button>
						</div>
						<div><small><?= esc($lang['label_vote']) ?></small></div>
					</form>
					<?php endif; ?>
				</div>
				<div class="col-xs-12 col-sm-10">
					<h6><?= esc($idea->title) ?></h6>
					<span style="color:#34495E"><small><?= nl2br(esc($idea->content)) ?></small></span>
					<div>
					<ul class="nav-pills" style="list-style:none;margin-left:-40px">
					<li style="padding-right:10px"><span class="label label-<?php
				switch ($idea->status) {
					case 'declined': echo 'danger'; break;
					case 'started': echo 'success'; break;
					case 'planned': echo 'warning'; break;
					case 'completed': echo 'info'; break;
					default: echo 'default'; break;
				}
				?>"><small><?= esc($lang['idea_' . $idea->status] ?? $idea->status) ?></small></span></li>
					<li style="padding-right:10px"><small><?= esc($idea->comments) ?> <?= esc($lang['label_comments']) ?></small></li>
					<li style="padding-right:10px"><a href="<?= esc(base_url('home/category/' . $idea->categoryid . '/' . url_title($categories[$idea->categoryid]->name, '-', true)), 'attr') ?>"><small><?= esc($categories[$idea->categoryid]->name) ?></small></a></li>
					</ul><br><br>
					<small><span class="glyphicon glyphicon-user"></span> <a href="<?= base_url('home/profile/' . $idea->authorid . '/' . url_title((string) $idea->user, '-', true)) ?>"><?= esc($idea->user) ?></a> <i><?= esc($lang['text_shared_this_idea']) ?></i> <span style='color:#555;margin-left:30px;'><?= esc($idea->date) ?></span></small>
					</div>

					<?php if (! empty($tags)): ?>
					<div style="margin-top:8px">
						<?php foreach ($tags as $tag): ?>
							<span class="label label-info"><?= esc($tag->name) ?></span>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>

					<?php if (! empty($attachments)): ?>
					<div style="margin-top:8px">
						<small><b><?= esc($lang['label_attachments']) ?>:</b></small>
						<ul>
						<?php foreach ($attachments as $attachment): ?>
							<li><a href="<?= base_url('download/attachment/' . $attachment->id) ?>"><?= esc($attachment->file_name) ?></a></li>
						<?php endforeach; ?>
						</ul>
					</div>
					<?php endif; ?>
				</div>
			</div>

			<?php if (is_admin(1)): ?>
			<div class="row">
				<div class="col-md-10 col-md-offset-2">
				<ul class="nav-pills" style="list-style:none;margin-left:-40px;">
				<li>
					<?php if ($idea->status === 'new'): ?>
						<form method="post" action="<?= base_url('adminaction/approveidea') ?>" style="display:inline">
							<?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $idea->id ?>">
							<button type="submit" class="btn btn-success btn-sm" style="width:130px"><?= esc($lang['label_idea_approve']) ?></button>
						</form>
					<?php elseif ($idea->status !== 'completed' && $idea->status !== 'declined'): ?>
						<form method="post" action="<?= base_url('adminaction/ideastatus') ?>" class="form-inline" style="display:inline">
							<?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $idea->id ?>">
							<select name="status" class="form-control input-sm">
								<option value="declined"><?= esc($lang['idea_declined']) ?></option>
								<option value="considered"><?= esc($lang['idea_considered']) ?></option>
								<option value="planned"><?= esc($lang['idea_planned']) ?></option>
								<option value="started"><?= esc($lang['idea_started']) ?></option>
								<option value="completed"><?= esc($lang['idea_completed']) ?></option>
							</select>
							<button type="submit" class="btn btn-info btn-sm"><?= esc($lang['label_change_status']) ?></button>
						</form>
					<?php endif; ?>
				</li>
				<li>
					<form method="post" action="<?= base_url('adminaction/deleteidea') ?>" style="display:inline" onsubmit="return confirm('<?= esc($lang['text_sure_delete_idea'], 'js') ?>');">
						<?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $idea->id ?>">
						<button type="submit" class="btn btn-danger btn-sm" style="width:130px"><?= esc($lang['label_delete_idea']) ?></button>
					</form>
				</li>
				<li>
					<a href="<?= base_url('admin/users/' . $idea->authorid) ?>" target="_blank"><button type="button" class="btn btn-danger btn-sm" style="width:130px"><?= esc($lang['label_ban_user']) ?></button></a>
				</li>
				</ul>
				</div>
			</div>
			<?php endif; ?>

			<?php if (is_logged_in()): ?>
			<div class="row">
				<div class="col-md-10 col-md-offset-2" style="margin-top:10px">
					<form role="form" method="post" action="<?= base_url('action/comment') ?>">
						<?= csrf_field() ?>
						<input type="hidden" name="idea_id" value="<?= (int) $idea->id ?>">
						<div class="form-group">
						  <label>Comment</label>
						    <textarea class="form-control" rows="4" name="content" required></textarea>
						  </div>
						  <button type="submit" name="commentbutton" class="btn btn-default"><?= esc($lang['label_submit']) ?></button>
					</form>
				</div>
			</div>
			<?php endif; ?>

			<?php foreach ($comments as $comment): ?>
			<div class="row">
				<div class="col-md-10 col-md-offset-2">
					<div class="comment-box">
						<span class="glyphicon glyphicon-comment" style="margin-right:5px"></span>
						<a href="<?= base_url('home/profile/' . $comment->userid . '/' . url_title((string) $comment->user, '-', true)) ?>"><?= esc($comment->user) ?></a>
						<span style="margin-left:15px;color:#555"><?= esc($comment->date) ?></span>
							<span style="margin-left:15px;margin-right:5px">
							<?php if (is_admin(1)): ?>
								<form method="post" action="<?= base_url('adminaction/deletecomment') ?>" style="display:inline" onsubmit="return confirm('<?= esc($lang['text_sure_delete_comment'], 'js') ?>');">
									<?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $comment->id ?>">
									<button type="submit" class="btn btn-link btn-xs" style="color:#E25F5F;padding:0"><i><small><?= esc($lang['label_delete_comment']) ?></small></i></button>
								</form>
							<?php else: ?>
								<form method="post" action="<?= base_url('action/flag') ?>" style="display:inline">
									<?= csrf_field() ?><input type="hidden" name="cid" value="<?= (int) $comment->id ?>"><input type="hidden" name="idea_id" value="<?= (int) $idea->id ?>">
									<button type="submit" class="btn btn-link btn-xs" style="color:#E25F5F;padding:0"><i><small><?= esc($lang['text_flag_comment']) ?></small></i></button>
								</form>
							<?php endif; ?>
							</span>
						<div class="comment-text">
							<?= nl2br(esc($comment->content)) ?>
						</div>
					 </div>
				</div>
			</div>
			<?php endforeach; ?>
</div>
