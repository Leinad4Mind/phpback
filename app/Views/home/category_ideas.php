<?php
	$statusSeg = ($idea_status !== '' ? $idea_status : 'all');
	$flip      = ($type === 'desc') ? 'asc' : 'desc';
	$chevron   = ($type === 'desc') ? 'down' : 'up';
	$orderLink = static fn (string $col): string => esc($category->url . '/' . $statusSeg . '/' . $col . '/' . $flip . '/1', 'attr');
	$pageLink  = static fn (int $p): string => esc($category->url . '/' . $statusSeg . '/' . $order . '/' . $type . '/' . $p, 'attr');
?>
	<div class="col-md-9">
		<div class="breadcrumb-wrapper"><ol class="breadcrumb">
			<li><a href="<?= base_url() ?>">Feedback</a></li>
			<li class="active"><?= esc($category->name) ?></li>
		</ol></div>
		<div>
			<h5 style="color:#2C3E50;"><?= esc($category->name) ?></h5>
			<span style="color:#34495E"><small><?= esc($category->description) ?></small></span>
		</div>

		<!-- Status filter (PHPBack upstream PR #164) -->
		<div style="margin:10px 0">
			<small><?= esc($lang['label_status']) ?>:</small>
			<?php
				$statusOptions = ['all' => 'All', 'considered' => 'idea_considered', 'planned' => 'idea_planned', 'started' => 'idea_started', 'completed' => 'idea_completed', 'declined' => 'idea_declined'];
			?>
			<?php foreach ($statusOptions as $value => $labelKey): ?>
				<a class="btn btn-xs <?= ($statusSeg === $value) ? 'btn-primary' : 'btn-default' ?>"
				   href="<?= esc($category->url . '/' . $value . '/' . $order . '/' . $type . '/1', 'attr') ?>">
					<?= $value === 'all' ? 'All' : esc($lang[$labelKey]) ?>
				</a>
			<?php endforeach; ?>
		</div>

		<table id="ideastable" class="table table-condensed">
		<thead>
			<tr>
			  <th><small><?= esc($lang['label_idea']) ?> <a href="<?= $orderLink('title') ?>"><span class="glyphicon glyphicon-chevron-<?= $chevron ?>" style="margin-left:4px"></span></a></small></th>
			  <th><small><?= esc($lang['label_votes']) ?> <a href="<?= $orderLink('votes') ?>"><span class="glyphicon glyphicon-chevron-<?= $chevron ?>" style="margin-left:4px"></span></a></small></th>
			  <th><small><?= esc($lang['label_comments']) ?></small></th>
			  <th><small><?= esc($lang['label_date']) ?> <a href="<?= $orderLink('id') ?>"><span class="glyphicon glyphicon-chevron-<?= $chevron ?>" style="margin-left:4px"></span></a></small></th>
			</tr>
		</thead>
		</table>

		<?php foreach ($ideas as $idea): ?>
			<div class="row">
				<div class="col-xs-4 col-sm-2">
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
				<div class="col-xs-8 col-sm-10">
					<a class="result-idea--title" href="<?= esc($idea->url, 'attr') ?>"><?= esc($idea->title) ?></a>
					<div style="margin-top:-10px">
					<small class="result-idea--description">
						<?= esc(mb_strlen($idea->content) > 200 ? mb_substr($idea->content, 0, 200) . '...' : $idea->content) ?>
					</small>
					</div>
					<div style="margin-top:-10px">
						<ul class="nav-pills" style="list-style:none;margin-left:-40px">
							<li><small class="result-idea--comments"><?= esc($idea->comments) ?> <?= esc($lang['label_comments']) ?></small></li>
						</ul><br><br>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
			<ul class="pagination">
			  <li><a class="pagination--previous" href="<?= $page > 1 ? $pageLink($page - 1) : '#' ?>">&laquo;</a></li>
			  <?php for ($i = 1; $i <= $pages; $i++): ?>
			  	 <?php if ($i === $page): ?>
			  	 	<li class="active"><a class="pagination--current" href="#"><?= $i ?></a></li>
			  	 <?php else: ?>
			  	 	<li><a class="pagination--page" href="<?= $pageLink($i) ?>"><?= $i ?></a></li>
			  	 <?php endif; ?>
			  <?php endfor; ?>
			  <li><a class="pagination--next" href="<?= $page < $pages ? $pageLink($page + 1) : '#' ?>">&raquo;</a></li>
			</ul>
</div>
