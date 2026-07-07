	<div class="col-md-9">
		<div class="breadcrumb-wrapper"><ol class="breadcrumb">
			<li><a href="<?= base_url() ?>">Feedback</a></li>
			<li class="active"><?= esc($lang['label_search']) ?></li>
		</ol></div>
		<?php if (! count($ideas)): ?>
			<h3><small><?= esc($lang['text_nothing_found']) ?></small></h3>
		<?php endif; ?>
		<?php foreach ($ideas as $idea): ?>
			<div class="row">
				<div class="col-xs-4 col-md-2">
					<div class="vote-count-box">
						<span style="color:#3498DB;"><b class="result-idea--votes"><?= esc(number_format((int) $idea->votes)) ?></b></span>
						<br><div style="margin-top:-10px;font-size:14px"><?= esc($lang['label_votes']) ?></div>
					</div>
					<div class="vote-label">
						<span class="label label-<?php
							switch ($idea->status) {
								case 'declined': echo 'danger'; break;
								case 'started': echo 'success'; break;
								case 'planned': echo 'warning'; break;
								case 'completed': echo 'info'; break;
								default: echo 'default'; break;
							} ?> result-idea--status" style="font-size:12px"><?= esc($lang['idea_' . $idea->status] ?? $idea->status) ?></span>
					</div>
				</div>
				<div class="col-xs-8 col-md-10">
					<a class="result-idea--title" href="<?= esc($idea->url, 'attr') ?>"><?= esc($idea->title) ?></a>
					<div style="margin-top:-10px">
					<small class="result-idea--description">
						<?= esc(mb_strlen($idea->content) > 200 ? mb_substr($idea->content, 0, 200) . '...' : $idea->content) ?>
					</small>
					</div>
					<div style="margin-top:-10px">
						<ul class="nav-pills" style="list-style:none;margin-left:-40px">
							<li style="padding-right:5px"><small><?= esc($idea->comments) ?> <?= esc($lang['label_comments']) ?></small></li>
						</ul><br><br>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
