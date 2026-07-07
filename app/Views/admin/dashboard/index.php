<div class="row">
    <div class="col-md-10 col-md-offset-1 dashboard-center">
    <?= view('admin/dashboard/_nav', ['active' => 'dashboard']) ?>
    <div class="row">
      <table class="table table-condensed" style="font-size:15px;width:80%;margin-left:10%;">
            <thead><tr><th>Log</th><th>Date</th></tr></thead>
          <tbody>
            <?php foreach ($logs as $log): ?>
            <tr><td><?= esc($log->content) ?></td><td><?= esc($log->date) ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
  </div>
</div>
</div>
  </body>
  </html>
