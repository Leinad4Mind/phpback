<div class="col-md-9">
	<small><ol class="breadcrumb">
        <li><a href="<?php echo base_url();?>">Feedback</a></li>
        <li class="active"><?php echo $lang['label_post_new_idea']; ?></li>
  </ol></small>
	<?php if(@!isset($_SESSION['phpback_userid'])): ?>
	<p class="bg-danger" style="padding-left:20px;padding-top:5px;padding-bottom:5px;"><?php echo $lang['error_login_to_post']; ?></p>
	<?php else: ?>
	<?php if($error != "none"): ?>
	<p class="bg-danger" style="padding-left:20px;padding-top:5px;padding-bottom:5px;"><?php
	switch ($error) {
		case 'errortitle':
			echo $lang['error_title'];
			break;
		case 'errorcat':
			echo $lang['error_category'];
			break;
		case 'errordesc':
			echo $lang['error_description'];
			break;
	}?></p>
	<?php endif; ?>
	<form name="post-idea-form" method="post" action="<?php echo base_url() . 'action/newidea'?>" enctype="multipart/form-data">
	  <div class="form-group">
	    <label for="exampleInputEmail1"><?php echo $lang['label_idea_title']; ?></label>
	    <input type="text" class="form-control" name="title" value="<?php if(@isset($POST['title'])) echo $POST['title'];?>" minlength="9" maxlength="100" required>
	  </div>
	  <div class="form-group">
	  <label><?php echo $lang['label_category']; ?></label>
	    <select class="form-control" name="category" required>
		  <option value=""><?php echo $lang['text_select_category']; ?></option>
		  <?php foreach ($categories as $cat):?>
		  <option value="<?php echo $cat->id;?>" <?php if(@isset($POST['catid']) && $POST['catid'] == $cat->id) echo 'selected="selected"';?>><?php echo $cat->name;?></option>
		  <?php endforeach; ?>
		</select>
	  </div>
	  <div class="form-group">
	  <label><?php echo $lang['label_description'];?></label>
	    <textarea class="form-control" rows="4" name="description" minlength="20" maxlength="1500" required><?php if(@isset($POST['desc'])) echo $POST['desc'];?></textarea>
	  </div><div class="form-group">
        <label for="tags">Tags (separated by commas):</label>
        <input type="text" class="form-control" name="tags" id="tags" placeholder="e.g. ui, performance, api">
      </div>

	  <label for="attachment">Upload Attachment (image or document only):</label>
    <input type="file" name="attachment" id="attachment" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx">

	  <button type="submit" class="btn btn-primary"><?php echo $lang['label_submit'];?></button>
	</form>
	<?php endif; ?>
</div>
