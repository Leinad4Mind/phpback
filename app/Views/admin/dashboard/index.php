<?= $this->extend('templates/admin_layout') ?>
<?= $this->section('content') ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold tracking-tight"><?= esc($lang['label_dashboard_overview'] ?? 'Dashboard Overview') ?></h2>
    <p class="text-muted-foreground mt-1 text-sm"><?= esc($lang['text_dashboard_desc'] ?? 'Recent system activity and logs.') ?></p>
</div>

<div class="bg-card text-card-foreground border shadow-sm rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-muted-foreground uppercase bg-muted/50 border-b">
                <tr>
                    <th scope="col" class="px-6 py-4 font-semibold"><?= esc($lang['label_activity_log'] ?? 'Activity Log') ?></th>
                    <th scope="col" class="px-6 py-4 font-semibold w-56"><?= esc($lang['label_date_time'] ?? 'Date & Time') ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border/50">
                <?php foreach ($logs as $log): ?>
                <tr class="bg-background hover:bg-muted/50 transition-colors">
                    <td class="px-6 py-4 font-medium text-foreground"><?= esc($log->content) ?></td>
                    <td class="px-6 py-4 text-muted-foreground text-xs whitespace-nowrap"><?= esc($log->date) ?></td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="2" class="px-6 py-8 text-center text-muted-foreground">
                        <?= esc($lang['text_no_logs'] ?? 'No activity logs found.') ?>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
