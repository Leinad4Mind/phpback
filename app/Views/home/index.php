	<div class="col-md-9">
	<?php if (is_admin(1)): ?>
      <p>You are an Admin 🎉
		<a href="<?= base_url('admin/dashboard') ?>" class="btn btn-primary btn-xs">Admin Panel</a></p>
    <?php endif; ?>
		<small>
		<ol class="breadcrumb">
		  <li class="active">Feedback</li>
		</ol>
		</small>
		<div>
			<h4 id="welcome-mesagge--title"><?= esc($welcomeTitle) ?></h4>
			<div id="welcome-mesagge--text"><?= esc($welcomeDescription) ?></div>
		</div>

		<br/>

		<!-- Filter form -->
		<form method="GET" action="<?= base_url('home') ?>" class="form-inline" style="margin-bottom: 20px;">
			<div class="form-group">
				<label>Category:</label>
				<input type="number" name="category" class="form-control input-sm" value="<?= esc($filters['category'] ?? '', 'attr') ?>">
			</div>
			<div class="form-group">
				<label><?= esc($lang['label_status']) ?>:</label>
				<select name="status" class="form-control input-sm">
					<option value="">-- All --</option>
					<?php foreach (['completed', 'started', 'planned', 'considered', 'declined'] as $st): ?>
						<option value="<?= $st ?>" <?= (($filters['status'] ?? '') === $st) ? 'selected' : '' ?>><?= esc($lang['idea_' . $st]) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="form-group">
				<label>Tag ID:</label>
				<input type="number" name="tag" class="form-control input-sm" value="<?= esc($filters['tag'] ?? '', 'attr') ?>">
			</div>
			<div class="form-group">
				<label>Sort:</label>
				<select name="sort" class="form-control input-sm">
					<option value="">Default</option>
					<option value="votes" <?= (($filters['sort'] ?? '') === 'votes') ? 'selected' : '' ?>>Votes</option>
					<option value="date" <?= (($filters['sort'] ?? '') === 'date') ? 'selected' : '' ?>>Date</option>
				</select>
			</div>
			<button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
		</form>

		<?php if ($hasFilter): ?>
			<h4>Filtered Ideas</h4>
			<?php if (empty($ideas_filtered)): ?>
				<p><small><?= esc($lang['text_nothing_found']) ?></small></p>
			<?php else: ?>
			<table class="table table-striped">
				<?php foreach ($ideas_filtered as $idea): ?>
					<tr>
						<td>
							<span class="label label-default"><?= esc($idea->status) ?></span>
							<a href="<?= esc($idea->url, 'attr') ?>"><?= esc($idea->title) ?></a>
							<small style="color: #888"> - <?= esc($lang['label_votes']) ?>: <?= esc($idea->votes) ?></small>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
			<?php endif; ?>
			<hr>
		<?php endif; ?>

		<div class="col-md-6">
			<div class="ideas-completed">
				<h6><?= esc($lang['last_completed_ideas']) ?></h6>
				<small>
				<table class="table table-hover">
					<?php foreach ($ideas['completed'] as $idea): ?>
						<tr><td>
							<span class="label label-info completed-idea--tag" style="margin-right:5px"><?= esc($lang['idea_completed']) ?></span>
							<a href="<?= esc($idea->url, 'attr') ?>"><?= esc($idea->title) ?></a>
						</td></tr>
					<?php endforeach; ?>
				</table>
				</small>
			</div>
			<div class="ideas-planned">
				<h6><?= esc($lang['last_planned_ideas']) ?></h6>
				<small>
				<table class="table table-hover">
					<?php foreach ($ideas['planned'] as $idea): ?>
						<tr><td>
							<span class="label label-warning planned-idea--tag" style="margin-right:5px"><?= esc($lang['idea_planned']) ?></span>
							<a href="<?= esc($idea->url, 'attr') ?>"><?= esc($idea->title) ?></a>
						</td></tr>
					<?php endforeach; ?>
				</table>
				</small>
			</div>
		</div>

		<div class="col-md-6">
			<div class="ideas-started">
				<h6><?= esc($lang['last_started_ideas']) ?></h6>
				<small>
					<table class="table table-hover">
						<?php foreach ($ideas['started'] as $idea): ?>
							<tr><td>
								<span class="label label-success started-idea--tag" style="margin-right:5px"><?= esc($lang['idea_started']) ?></span>
								<a href="<?= esc($idea->url, 'attr') ?>"><?= esc($idea->title) ?></a>
							</td></tr>
						<?php endforeach; ?>
					</table>
				</small>
			</div>
			<div class="ideas-considered">
				<h6><?= esc($lang['last_considered_ideas']) ?></h6>
				<small>
				<table class="table table-hover">
					<?php foreach ($ideas['considered'] as $idea): ?>
						<tr><td>
							<span class="label label-default considered-idea--tag" style="margin-right:5px"><?= esc($lang['idea_considered']) ?></span>
							<a href="<?= esc($idea->url, 'attr') ?>"><?= esc($idea->title) ?></a>
						</td></tr>
					<?php endforeach; ?>
				</table>
				</small>
			</div>
		</div>
	</div>
