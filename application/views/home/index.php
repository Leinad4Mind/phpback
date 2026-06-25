	
	<div class="col-md-9">
	<?php if (is_admin()): ?>
      <p>You are an Admin 🎉</p>
	    <!-- this button for admins-only -->
		<a href="/admin/settings" class="btn btn-primary">Admin Settings</a>
    <?php endif; ?>
		<small>
		<ol class="breadcrumb">
		  <li class="active">Feedback</li>
		</ol>
		</small>
		<div>
			<h4 id="welcome-mesagge--title"><?= $welcomeTitle; ?></h4>
			<div id="welcome-mesagge--text"><?= $welcomeDescription; ?></div>
		</div>
		
		<br/>
		

		<!-- 🟡 Filter Form -->
		<form method="GET" action="" class="form-inline" style="margin-bottom: 20px;">
			<div class="form-group">
				<label>Category:</label>
				<input type="number" name="category" class="form-control input-sm" value="<?= htmlspecialchars($filters['category'] ?? '') ?>">
			</div>

			<div class="form-group">
				<label>Status:</label>
				<select name="status" class="form-control input-sm">
					<option value="">-- All --</option>
					<option value="completed" <?= ($filters['status'] == 'completed') ? 'selected' : '' ?>>Completed</option>
					<option value="started" <?= ($filters['status'] == 'started') ? 'selected' : '' ?>>Started</option>
					<option value="planned" <?= ($filters['status'] == 'planned') ? 'selected' : '' ?>>Planned</option>
					<option value="considered" <?= ($filters['status'] == 'considered') ? 'selected' : '' ?>>Considered</option>
				</select>
			</div>

			<div class="form-group">
				<label>Tag ID:</label>
				<input type="number" name="tag" class="form-control input-sm" value="<?= htmlspecialchars($filters['tag'] ?? '') ?>">
			</div>

			<div class="form-group">
				<label>Sort:</label>
				<select name="sort" class="form-control input-sm">
					<option value="">Default</option>
					<option value="votes" <?= ($filters['sort'] == 'votes') ? 'selected' : '' ?>>Votes</option>
					<option value="date" <?= ($filters['sort'] == 'date') ? 'selected' : '' ?>>Date</option>
				</select>
			</div>

			<button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
		</form>

<!-- //Show Filtered Results  -->
		<?php if (!empty($ideas_filtered)): ?>
			<h4>Filtered Ideas</h4>
			<table class="table table-striped">
				<?php foreach ($ideas_filtered as $idea): ?>
					<tr>
						<td>
							<span class="label label-default"><?= htmlspecialchars($idea->status) ?></span>
							<a href="<?= $idea->url; ?>">
								<?= htmlspecialchars($idea->title) ?>
							</a>
							<small style="color: #888"> - Votes: <?= htmlspecialchars($idea->votes) ?> | <?= htmlspecialchars($idea->created_at) ?></small>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
		<?php endif; ?>

<!-- //Show Filtered Results  -->

		<div class="col-md-6">
			<div class="ideas-completed">
				<h6><?= $lang['last_completed_ideas']; ?></h6>
				<small>
				<table class="table table-hover">
					<?php foreach ($ideas['completed'] as $idea): ?>
						<tr>
							<td>
								<span class="label label-info completed-idea--tag" style="margin-right:5px">
									<?= $lang['idea_completed']; ?>
								</span>
								<a href="<?= $idea->url; ?>">
									<?= $idea->title; ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				</small>
			</div>
			<div class="ideas-planned">
				<h6><?= $lang['last_planned_ideas']; ?></h6>
				<small>
				<table class="table table-hover">
					<?php foreach ($ideas['planned'] as $idea): ?>
						<tr>
							<td>
								<span class="label label-warning planned-idea--tag" style="margin-right:5px">
									<?= $lang['idea_planned']; ?>
								</span>
								<a href="<?= $idea->url; ?>">
									<?= $idea->title; ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				</small>
			</div>
		</div>
		
		<div class="col-md-6">
			<div class="ideas-started">
				<h6><?= $lang['last_started_ideas']; ?></h6>
				<small>
					<table class="table table-hover">
						<?php foreach ($ideas['started'] as $idea): ?>
							<tr>
								<td>
									<span class="label label-success started-idea--tag" style="margin-right:5px">
										<?= $lang['idea_started']; ?>
									</span>
									<a href="<?= $idea->url; ?>">
										<?= $idea->title; ?>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</table>
				</small>
			</div>
			<div class="ideas-considered">
				<h6><?= $lang['last_considered_ideas']; ?></h6>
				<small>
				<table class="table table-hover">
					<?php foreach ($ideas['considered'] as $idea): ?>
						<tr>
							<td>
								<span class="label label-default considered-idea--tag" style="margin-right:5px">
									<?= $lang['idea_considered']; ?>
								</span>
								<a href="<?= $idea->url; ?>">
									<?= $idea->title; ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				</small>
			</div>
		</div>
	</div>
