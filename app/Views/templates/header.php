<!--  PHPBack - open source feedback system. Licensed under GPLv3. -->
<!DOCTYPE html>
<html>
<head>
    <title><?= esc($title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>" sizes="16x16">

    <!-- Loading Bootstrap -->
    <link href="<?= base_url('bootstrap/css/bootstrap.css') ?>" rel="stylesheet">
    <link href="<?= base_url('bootstrap/css/prettify.css') ?>" rel="stylesheet">

    <!-- Loading Flat UI -->
    <link href="<?= base_url('css/flat-ui.css') ?>" rel="stylesheet">

    <!-- Loading custom styles-->
    <link href="<?= base_url('css/all.css') ?>" rel="stylesheet">

  <script type="text/javascript">
  function showtable(tableid, tablelink){
      document.getElementById('activitytable').style.display = 'none';
      document.getElementById('ideastable').style.display = 'none';
      document.getElementById('commentstable').style.display = 'none';
      document.getElementById(tableid).style.display = '';
      document.getElementById("table1").className = "";
      document.getElementById("table2").className = "";
      document.getElementById("table3").className = "";
      document.getElementById(tablelink).className = "active";
  }
  function showtable4(tableid, tablelink){
      document.getElementById('resetvotestable').style.display = 'none';
      document.getElementById('changepasswordtable').style.display = 'none';
      document.getElementById(tableid).style.display = '';
      document.getElementById("table4").className = "";
      document.getElementById("table5").className = "";
      document.getElementById(tablelink).className = "active";
  }
  </script>
</head>
<body>
  <div class="row header">
    <div class="pull-left header--title-container">
      <h4 id="header--title"><?= esc($title) ?></h4>
    </div>
    <?php if (is_logged_in()): ?>
    <div class="pull-right" style="padding-top:15px;padding-right:40px;">
      <small><span class="logged-as-label"><?= esc($lang['label_logged_as']) ?></span>
          <span style='color:#999;margin-left:5px;'>
            <a href="<?= base_url('home/profile/' . current_user_id() . '/' . url_title((string) current_username(), '-', true)) ?>"><?= esc(current_username()) ?></a>
          </span>
      <form action="<?= base_url('action/logout') ?>" method="post" style="display:inline;margin-left:10px;">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-danger btn-xs"><?= esc($lang['label_log_out']) ?></button>
      </form></small>
    </div>
    <?php else: ?>
    <div class="pull-right" style="padding-top:12px;padding-right:40px;">
      <a href="<?= base_url('home/login') ?>"><button type="button" class="btn btn-success btn-sm btn-block" style="width:250px"><?= esc($lang['label_log_in']) ?></button></a>
    </div>
    <?php endif; ?>

  </div>

  <div class="container">
  <div class="row">
