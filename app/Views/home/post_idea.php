<div class="col-md-9">
	<small><ol class="breadcrumb">
        <li><a href="<?= base_url() ?>">Feedback</a></li>
        <li class="active"><?= esc($lang['label_post_new_idea']) ?></li>
  </ol></small>
	<?php if (! is_logged_in()): ?>
	<p class="bg-danger" style="padding:8px 20px;"><?= esc($lang['error_login_to_post']) ?></p>
	<?php else: ?>
	<?php if ($error !== 'none'): ?>
	<p class="bg-danger" style="padding:8px 20px;"><?php
		switch ($error) {
			case 'errortitle': echo esc($lang['error_title']); break;
			case 'errorcat':   echo esc($lang['error_category']); break;
			case 'errordesc':  echo esc($lang['error_description']); break;
			case 'errorfile':
			case 'errorsize':  echo esc($lang['error_upload']); break;
		}?></p>
	<?php endif; ?>
	<form name="post-idea-form" method="post" action="<?= base_url('action/newidea') ?>" enctype="multipart/form-data">
	  <?= csrf_field() ?>
	  <div class="form-group">
	    <label><?= esc($lang['label_idea_title']) ?></label>
	    <input type="text" class="form-control" name="title" value="<?= esc($POST['title'] ?? '', 'attr') ?>" minlength="9" maxlength="100" required>
	  </div>
	  <div class="form-group">
	  <label><?= esc($lang['label_category']) ?></label>
	    <select class="form-control" name="category" required>
		  <option value=""><?= esc($lang['text_select_category']) ?></option>
		  <?php foreach ($categories as $cat): ?>
		  <option value="<?= (int) $cat->id ?>" <?= ((string) ($POST['catid'] ?? '') === (string) $cat->id) ? 'selected="selected"' : '' ?>><?= esc($cat->name) ?></option>
		  <?php endforeach; ?>
		</select>
	  </div>
	  <div class="form-group">
	  <label><?= esc($lang['label_description']) ?></label>
	    <textarea class="form-control" rows="4" name="description" minlength="20" maxlength="1500" required><?= esc($POST['desc'] ?? '') ?></textarea>
	  </div>
	  <div class="form-group">
        <label for="tags"><?= esc($lang['label_tags']) ?> (separated by commas):</label>
        <input type="text" class="form-control" name="tags" id="tags" placeholder="e.g. ui, performance, api">
      </div>
	  <div class="form-group">
	  <label for="attachment"><?= esc($lang['label_attachment']) ?> (image or document, max 5&nbsp;MB):</label>
      <input type="file" name="attachment" id="attachment" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx">
	  </div>
	  <button type="submit" class="btn btn-primary"><?= esc($lang['label_submit']) ?></button>
	</form>
	<?php endif; ?>
</div>
