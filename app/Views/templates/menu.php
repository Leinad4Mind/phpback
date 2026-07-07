	<div class="col-md-3">
		<div class="sidemenu">
			<div id="search">
				<form action="<?= base_url('home/search') ?>" method="POST">
				  <?= csrf_field() ?>
				  <div class="form-group">
					<div class="input-group">
					  <input class="form-control" name="query" id="search--input" type="search" placeholder="<?= esc($lang['label_search']) ?>">
					  <span class="input-group-btn">
						<button type="submit" class="btn" id="search--button"><span class="fui-search"></span></button>
					  </span>
					</div>
				  </div>
				</form>
			</div>
			<div id="postidea">
				<a href="<?= base_url('home/postidea') ?>"><button type="button" class="btn btn-primary btn-xs" id="post-new-idea-button">
					<?= esc($lang['label_post_new_idea']) ?> <span class="glyphicon glyphicon-plus" style="margin-left:5px;"></span>
				</button></a>
			</div>
			<div id="categories">
				<h6><?= esc($lang['label_categories']) ?></h6>
				<ul class="nav nav-pills nav-stacked">
				 <?php foreach ($categories as $cat): ?>
					<li <?= ! $cat->ideas ? 'class="disabled"' : '' ?>><a href="<?= esc($cat->url, 'attr') ?>"><?= esc($cat->name) ?><span class="badge"><?= esc($cat->ideas) ?></span></a></li>
				 <?php endforeach; ?>
				</ul>
				<br>
			</div>
		</div>
	</div>
