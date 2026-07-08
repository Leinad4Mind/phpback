<?= $this->extend('templates/layout') ?>
<?= $this->section('content') ?>
<?php
    $statusSeg = ($idea_status !== '' ? $idea_status : 'all');
    $flip      = ($type === 'desc') ? 'asc' : 'desc';
    
    $orderLink = static fn (string $col): string => esc($category->url . '/' . $statusSeg . '/' . $col . '/' . $flip . '/1', 'attr');
    $pageLink  = static fn (int $p): string => esc($category->url . '/' . $statusSeg . '/' . $order . '/' . $type . '/' . $p, 'attr');
?>

<!-- Breadcrumbs -->
<nav class="flex text-sm text-muted-foreground mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="<?= base_url() ?>" class="hover:text-foreground transition-colors"><?= esc($lang['label_feedback']) ?></a>
        </li>
        <li aria-current="page">
            <div class="flex items-center">
                <svg class="w-3 h-3 mx-1 text-muted-foreground" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                </svg>
                <span class="ml-1 font-medium text-foreground md:ml-2"><?= esc($category->name) ?></span>
            </div>
        </li>
    </ol>
</nav>

<div class="mb-8">
    <h2 class="text-3xl font-bold tracking-tight text-foreground"><?= esc($category->name) ?></h2>
    <p class="text-muted-foreground mt-2 text-lg"><?= esc($category->description) ?></p>
</div>

<!-- Status Filter -->
<div class="flex flex-wrap items-center gap-2 mb-6">
    <span class="text-sm font-medium text-muted-foreground mr-2"><?= esc($lang['label_status']) ?>:</span>
    <?php
        $statusOptions = ['all' => 'All', 'considered' => 'idea_considered', 'planned' => 'idea_planned', 'started' => 'idea_started', 'completed' => 'idea_completed', 'declined' => 'idea_declined'];
    ?>
    <?php foreach ($statusOptions as $value => $labelKey): ?>
        <a href="<?= esc($category->url . '/' . $value . '/' . $order . '/' . $type . '/1', 'attr') ?>" 
           class="inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-medium transition-colors border <?= ($statusSeg === $value) ? 'bg-primary text-primary-foreground border-primary' : 'bg-background hover:bg-muted text-muted-foreground border-input' ?>">
            <?= $value === 'all' ? esc($lang['text_all']) : esc($lang[$labelKey]) ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Sorting Controls -->
<div class="flex flex-wrap items-center gap-4 mb-4 text-sm text-muted-foreground pb-2 border-b">
    <span><?= esc($lang['label_sort']) ?>:</span>
    <a href="<?= $orderLink('title') ?>" class="flex items-center hover:text-foreground">
        <?= esc($lang['label_idea']) ?>
        <?php if ($order === 'title'): ?>
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $flip === 'desc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' ?>"></path></svg>
        <?php endif; ?>
    </a>
    <a href="<?= $orderLink('votes') ?>" class="flex items-center hover:text-foreground">
        <?= esc($lang['label_votes']) ?>
        <?php if ($order === 'votes'): ?>
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $flip === 'desc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' ?>"></path></svg>
        <?php endif; ?>
    </a>
    <a href="<?= $orderLink('id') ?>" class="flex items-center hover:text-foreground">
        <?= esc($lang['label_date']) ?>
        <?php if ($order === 'id'): ?>
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $flip === 'desc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' ?>"></path></svg>
        <?php endif; ?>
    </a>
</div>

<!-- Ideas List -->
<div class="space-y-4 mb-8">
    <?php if (empty($ideas)): ?>
        <div class="text-center py-12 text-muted-foreground bg-muted/20 rounded-lg border border-dashed">
            <p><?= esc($lang['text_nothing_found']) ?></p>
        </div>
    <?php else: ?>
        <?php foreach ($ideas as $idea): ?>
            <div class="group relative bg-card text-card-foreground rounded-lg border shadow-sm p-4 flex flex-col sm:flex-row gap-4 transition-colors hover:border-primary/50 cursor-pointer hover:bg-muted/30">
                <!-- Votes & Status -->
                <div class="flex flex-col items-center justify-center sm:w-24 shrink-0 bg-muted/30 rounded-md p-3">
                    <div class="text-2xl font-bold text-primary"><?= esc(number_format((int) $idea->votes)) ?></div>
                    <div class="text-[10px] uppercase tracking-wider text-muted-foreground font-semibold mb-2"><?= esc($lang['label_votes']) ?></div>
                    
                    <?php
                    $statusColor = match($idea->status) {
                        'declined' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300',
                        'started' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
                        'planned' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300',
                        'completed' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300',
                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
                    };
                    ?>
                    <span class="inline-flex text-center rounded-full px-2 py-0.5 text-[10px] font-semibold leading-tight <?= $statusColor ?>">
                        <?= esc($lang['idea_' . $idea->status] ?? $idea->status) ?>
                    </span>
                </div>
                
                <!-- Content -->
                <div class="flex-1 flex flex-col">
                    <a href="<?= esc($idea->url, 'attr') ?>" class="text-lg font-semibold group-hover:underline group-hover:text-primary transition-colors line-clamp-1 mb-1 focus:outline-none">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        <?= esc($idea->title) ?>
                    </a>
                    <p class="text-sm text-muted-foreground line-clamp-2 mb-3 flex-1">
                        <?= esc($idea->content) ?>
                    </p>
                    
                    <div class="flex items-center text-xs text-muted-foreground gap-1 mt-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        <?= esc($idea->comments) ?> <?= esc($lang['label_comments']) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($pages > 1): ?>
<nav class="flex justify-center mt-8">
    <ul class="inline-flex -space-x-px text-sm">
        <li>
            <a href="<?= $page > 1 ? $pageLink($page - 1) : '#' ?>" class="flex items-center justify-center px-3 h-8 ml-0 leading-tight text-muted-foreground bg-background border border-input rounded-l-lg hover:bg-muted hover:text-foreground <?= $page <= 1 ? 'opacity-50 pointer-events-none' : '' ?>">
                <span class="sr-only"><?= esc($lang['label_previous']) ?></span>
                <svg class="w-2.5 h-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 1 1 5l4 4"/></svg>
            </a>
        </li>
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <li>
                <a href="<?= $pageLink($i) ?>" class="flex items-center justify-center px-3 h-8 leading-tight border border-input <?= $i === $page ? 'text-primary-foreground bg-primary border-primary hover:bg-primary/90' : 'text-muted-foreground bg-background hover:bg-muted hover:text-foreground' ?>">
                    <?= $i ?>
                </a>
            </li>
        <?php endfor; ?>
        <li>
            <a href="<?= $page < $pages ? $pageLink($page + 1) : '#' ?>" class="flex items-center justify-center px-3 h-8 leading-tight text-muted-foreground bg-background border border-input rounded-r-lg hover:bg-muted hover:text-foreground <?= $page >= $pages ? 'opacity-50 pointer-events-none' : '' ?>">
                <span class="sr-only"><?= esc($lang['label_next']) ?></span>
                <svg class="w-2.5 h-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/></svg>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<?= $this->endSection() ?>
