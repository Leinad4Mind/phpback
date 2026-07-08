<?= $this->extend('templates/layout') ?>
<?= $this->section('content') ?>

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
                <span class="ml-1 font-medium text-foreground md:ml-2"><?= esc($lang['label_search']) ?></span>
            </div>
        </li>
    </ol>
</nav>

<div class="mb-8">
    <h2 class="text-3xl font-bold tracking-tight text-foreground"><?= esc($lang['label_search']) ?></h2>
    <?php if (isset($_POST['query'])): ?>
        <p class="text-muted-foreground mt-2"><?= esc($lang['label_search']) ?>: "<?= esc($_POST['query']) ?>"</p>
    <?php endif; ?>
</div>

<div class="space-y-4 mb-8">
    <?php if (! count($ideas)): ?>
        <div class="text-center py-12 text-muted-foreground bg-muted/20 rounded-lg border border-dashed">
            <svg class="w-12 h-12 mx-auto mb-3 text-muted-foreground/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <p class="text-lg font-medium"><?= esc($lang['text_nothing_found']) ?></p>
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
                    <p class="mt-2 text-sm text-muted-foreground line-clamp-3">
                        <?= esc(strip_tags($idea->content)) ?>
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

<?= $this->endSection() ?>
