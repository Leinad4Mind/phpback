<?= $this->extend('templates/layout') ?>
<?= $this->section('content') ?>

<?php if (is_admin(1)): ?>
    <div id="admin-banner" class="mb-6 p-4 bg-primary/10 text-primary border border-primary/20 rounded-lg flex items-center justify-between hidden">
        <span><?= esc($lang['text_admin_greeting']) ?> 🎉</span>
        <div class="flex items-center gap-3">
            <a href="<?= base_url('admin/dashboard') ?>" class="text-sm font-medium bg-primary text-primary-foreground px-3 py-1.5 rounded-md hover:bg-primary/90">
                <?= esc($lang['label_admin_panel']) ?>
            </a>
            <button onclick="dismissAdminBanner()" class="text-primary hover:text-primary/70 focus:outline-none" aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>
    <script>
        if (localStorage.getItem('hide_admin_banner') !== 'true') {
            document.getElementById('admin-banner').classList.remove('hidden');
        }
        function dismissAdminBanner() {
            localStorage.setItem('hide_admin_banner', 'true');
            document.getElementById('admin-banner').style.display = 'none';
        }
    </script>
<?php endif; ?>

<div class="mb-6">
    <small class="text-muted-foreground uppercase tracking-wider font-semibold"><?= esc($lang['label_feedback']) ?></small>
    <h2 class="text-3xl font-bold tracking-tight mt-1"><?= esc($welcomeTitle) ?></h2>
    <div class="text-muted-foreground mt-2 text-lg"><?= esc($welcomeDescription) ?></div>
</div>

<!-- Filter form -->
<form method="GET" action="<?= base_url('home') ?>" class="bg-card text-card-foreground border rounded-lg p-4 mb-8 flex flex-wrap gap-4 items-end">
    <div class="space-y-1 flex-1 min-w-[150px]">
        <label class="text-sm font-medium"><?= esc($lang['label_category']) ?>:</label>
        <input type="number" name="category" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" value="<?= esc($filters['category'] ?? '', 'attr') ?>">
    </div>
    <div class="space-y-1 flex-1 min-w-[150px]">
        <label class="text-sm font-medium"><?= esc($lang['label_status']) ?>:</label>
        <select name="status" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
            <option value="">-- <?= esc($lang['text_all']) ?> --</option>
            <?php foreach (['completed', 'started', 'planned', 'considered', 'declined'] as $st): ?>
                <option value="<?= $st ?>" <?= (($filters['status'] ?? '') === $st) ? 'selected' : '' ?>><?= esc($lang['idea_' . $st]) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="space-y-1 flex-1 min-w-[150px]">
        <label class="text-sm font-medium"><?= esc($lang['label_tag']) ?> ID:</label>
        <input type="number" name="tag" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" value="<?= esc($filters['tag'] ?? '', 'attr') ?>">
    </div>
    <div class="space-y-1 flex-1 min-w-[150px]">
        <label class="text-sm font-medium"><?= esc($lang['label_sort']) ?>:</label>
        <select name="sort" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
            <option value=""><?= esc($lang['text_default']) ?></option>
            <option value="votes" <?= (($filters['sort'] ?? '') === 'votes') ? 'selected' : '' ?>><?= esc($lang['label_votes']) ?></option>
            <option value="date" <?= (($filters['sort'] ?? '') === 'date') ? 'selected' : '' ?>><?= esc($lang['label_date']) ?></option>
        </select>
    </div>
    <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-secondary text-secondary-foreground hover:bg-secondary/80 h-10 px-4 py-2">
        <?= esc($lang['label_apply_filters']) ?>
    </button>
</form>

<?php if ($hasFilter): ?>
    <div class="mb-8">
        <h4 class="text-lg font-semibold mb-3"><?= esc($lang['label_filtered_ideas']) ?></h4>
        <?php if (empty($ideas_filtered)): ?>
            <p class="text-muted-foreground text-sm"><?= esc($lang['text_nothing_found']) ?></p>
        <?php else: ?>
            <div class="space-y-2 border rounded-lg divide-y bg-card">
                <?php foreach ($ideas_filtered as $idea): ?>
                    <div class="p-3 hover:bg-muted/50 flex items-center gap-3 transition-colors">
                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold bg-secondary text-secondary-foreground">
                            <?= esc($idea->status) ?>
                        </span>
                        <a href="<?= esc($idea->url, 'attr') ?>" class="font-medium hover:underline text-foreground"><?= esc($idea->title) ?></a>
                        <span class="text-muted-foreground text-sm ml-auto">
                            <?= esc($lang['label_votes']) ?>: <?= esc($idea->votes) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
    // Same badge palette as the status buckets below, keyed by idea status.
    $statusBadgeClasses = [
        'completed'  => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300',
        'started'    => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
        'planned'    => 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300',
        'considered' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
    ];
?>
<?php if (! empty($ideas['recent'])): ?>
    <div class="mb-8">
        <h6 class="text-sm font-semibold mb-3 uppercase tracking-wider text-muted-foreground"><?= esc($lang['last_added_ideas']) ?></h6>
        <div class="space-y-2">
            <?php foreach ($ideas['recent'] as $idea): ?>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold <?= $statusBadgeClasses[$idea->status] ?? 'bg-secondary text-secondary-foreground' ?>">
                        <?= esc($lang['idea_' . $idea->status]) ?>
                    </span>
                    <a href="<?= esc($idea->url, 'attr') ?>" class="text-sm hover:underline text-foreground truncate"><?= esc($idea->title) ?></a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <div class="space-y-8">
        <div>
            <h6 class="text-sm font-semibold mb-3 uppercase tracking-wider text-muted-foreground"><?= esc($lang['last_completed_ideas']) ?></h6>
            <div class="space-y-2">
                <?php foreach ($ideas['completed'] as $idea): ?>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                            <?= esc($lang['idea_completed']) ?>
                        </span>
                        <a href="<?= esc($idea->url, 'attr') ?>" class="text-sm hover:underline text-foreground truncate"><?= esc($idea->title) ?></a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div>
            <h6 class="text-sm font-semibold mb-3 uppercase tracking-wider text-muted-foreground"><?= esc($lang['last_planned_ideas']) ?></h6>
            <div class="space-y-2">
                <?php foreach ($ideas['planned'] as $idea): ?>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300">
                            <?= esc($lang['idea_planned']) ?>
                        </span>
                        <a href="<?= esc($idea->url, 'attr') ?>" class="text-sm hover:underline text-foreground truncate"><?= esc($idea->title) ?></a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="space-y-8">
        <div>
            <h6 class="text-sm font-semibold mb-3 uppercase tracking-wider text-muted-foreground"><?= esc($lang['last_started_ideas']) ?></h6>
            <div class="space-y-2">
                <?php foreach ($ideas['started'] as $idea): ?>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                            <?= esc($lang['idea_started']) ?>
                        </span>
                        <a href="<?= esc($idea->url, 'attr') ?>" class="text-sm hover:underline text-foreground truncate"><?= esc($idea->title) ?></a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div>
            <h6 class="text-sm font-semibold mb-3 uppercase tracking-wider text-muted-foreground"><?= esc($lang['last_considered_ideas']) ?></h6>
            <div class="space-y-2">
                <?php foreach ($ideas['considered'] as $idea): ?>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                            <?= esc($lang['idea_considered']) ?>
                        </span>
                        <a href="<?= esc($idea->url, 'attr') ?>" class="text-sm hover:underline text-foreground truncate"><?= esc($idea->title) ?></a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
