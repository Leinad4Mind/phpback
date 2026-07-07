<!--  PHPBack - open source feedback system. Licensed under GPLv3. -->
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Admin Panel - <?= esc($title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>" sizes="16x16">

    <link href="<?= base_url('bootstrap/css/bootstrap.css') ?>" rel="stylesheet">
    <link href="<?= base_url('bootstrap/css/prettify.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/flat-ui.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/docs.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/all.css') ?>" rel="stylesheet">

    <script src="<?= base_url('js/jquery-1.8.3.min.js') ?>"></script>
    <script type="text/javascript">
    function showtable(tableid, tablelink){
      ['newideastable','allideastable','commentstable'].forEach(function(id){ var e=document.getElementById(id); if(e) e.style.display='none'; });
      document.getElementById(tableid).style.display = '';
      document.getElementById("table1").className = "";
      document.getElementById("table2").className = "";
      document.getElementById("table3").className = "";
      document.getElementById(tablelink).className = "active";
    }
    function showtable2(tableid, tablelink){
      ['bannedtable','newuserstable','bantable'].forEach(function(id){ var e=document.getElementById(id); if(e) e.style.display='none'; });
      document.getElementById(tableid).style.display = '';
      document.getElementById("table1").className = "";
      document.getElementById("table2").className = "";
      document.getElementById("table3").className = "";
      document.getElementById(tablelink).className = "active";
    }
    function showtable3(tableid, tablelink){
      ['generaltable','admintable','categorytable'].forEach(function(id){ var e=document.getElementById(id); if(e) e.style.display='none'; });
      document.getElementById(tableid).style.display = '';
      document.getElementById("table1").className = "";
      document.getElementById("table2").className = "";
      document.getElementById("table3").className = "";
      document.getElementById(tablelink).className = "active";
    }
    </script>

    <style type="text/css">
    .logosmall{ padding-top: 10px; padding-left: 8px; margin-right: 10px; }
    .navbar{ background-color: #34495E; margin-top: 5px; }
    .dashboard-center{ background-color: #ECF0F1; }
    body{ background-color: #27AE60; }
    </style>
    <script src="<?= base_url('js/bootstrap.min.js') ?>"></script>
    <script src="<?= base_url('js/bootstrap-select.js') ?>"></script>
    <script src="<?= base_url('js/bootstrap-switch.js') ?>"></script>
    <script src="<?= base_url('js/flatui-checkbox.js') ?>"></script>
    <script src="<?= base_url('js/flatui-radio.js') ?>"></script>
    <script src="<?= base_url('bootstrap/js/application.js') ?>"></script>
  </head>
  <body onload="<?php if (! empty($toall)) echo "showtable('allideastable','table2');"; if (isset($idban)) echo "showtable2('bantable','table3');"; ?>">
