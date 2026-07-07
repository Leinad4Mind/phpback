<?= $this->extend('templates/layout') ?>
<?= $this->section('content') ?>

<!-- Breadcrumbs -->
<nav class="flex text-sm text-muted-foreground mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="<?= base_url() ?>" class="hover:text-foreground transition-colors">Feedback</a>
        </li>
        <li>
            <div class="flex items-center">
                <svg class="w-3 h-3 mx-1 text-muted-foreground" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                </svg>
                <a href="<?= esc(base_url('home/category/' . $idea->categoryid . '/' . url_title($categories[$idea->categoryid]->name, '-', true)), 'attr') ?>" class="ml-1 hover:text-foreground transition-colors md:ml-2"><?= esc($categories[$idea->categoryid]->name) ?></a>
            </div>
        </li>
        <li aria-current="page">
            <div class="flex items-center">
                <svg class="w-3 h-3 mx-1 text-muted-foreground" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                </svg>
                <span class="ml-1 font-medium text-foreground md:ml-2 truncate max-w-[200px] sm:max-w-md"><?= esc($idea->title) ?></span>
            </div>
        </li>
    </ol>
</nav>

<div class="bg-card text-card-foreground rounded-lg border shadow-sm p-6 mb-8">
    <div class="flex flex-col sm:flex-row gap-6">
        
        <!-- Left Column: Voting -->
        <div class="flex flex-col items-center sm:w-24 shrink-0 bg-muted/30 rounded-lg p-4 border border-dashed">
            <div class="text-3xl font-bold text-primary mb-1"><?= esc(number_format((int) $idea->votes)) ?></div>
            <div class="text-xs uppercase tracking-wider text-muted-foreground font-semibold mb-4"><?= esc($lang['label_votes']) ?></div>
            
            <?php if (is_logged_in()): ?>
            <!-- This form will be replaced by the VoteButton Island in Phase 3.3 -->
            <form method="post" action="<?= base_url('action/vote') ?>" class="flex flex-col items-center w-full">
                <?= csrf_field() ?>
                <input type="hidden" name="ideaid" value="<?= (int) $idea->id ?>">
                <div class="flex w-full overflow-hidden rounded-md border shadow-sm">
                    <button type="submit" name="votes" value="1" class="flex-1 bg-background hover:bg-muted py-1.5 text-sm font-medium transition-colors border-r">1</button>
                    <button type="submit" name="votes" value="2" class="flex-1 bg-background hover:bg-muted py-1.5 text-sm font-medium transition-colors border-r">2</button>
                    <button type="submit" name="votes" value="3" class="flex-1 bg-background hover:bg-muted py-1.5 text-sm font-medium transition-colors">3</button>
                </div>
                <div class="text-[10px] uppercase text-muted-foreground mt-2"><?= esc($lang['label_vote']) ?></div>
            </form>
            <?php endif; ?>
        </div>

        <!-- Right Column: Idea Details -->
        <div class="flex-1">
            <h1 class="text-2xl font-bold mb-4"><?= esc($idea->title) ?></h1>
            
            <div class="prose prose-sm dark:prose-invert max-w-none mb-6">
                <?= nl2br(esc($idea->content)) ?>
            </div>
            
            <div class="flex flex-wrap items-center gap-4 text-sm mb-6">
                <?php
                $statusColor = match($idea->status) {
                    'declined' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300',
                    'started' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
                    'planned' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300',
                    'completed' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300',
                    default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
                };
                ?>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold <?= $statusColor ?>">
                    <?= esc($lang['idea_' . $idea->status] ?? $idea->status) ?>
                </span>
                
                <span class="text-muted-foreground flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    <?= esc($idea->comments) ?> <?= esc($lang['label_comments']) ?>
                </span>
                
                <a href="<?= esc(base_url('home/category/' . $idea->categoryid . '/' . url_title($categories[$idea->categoryid]->name, '-', true)), 'attr') ?>" class="text-muted-foreground hover:text-foreground flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                    <?= esc($categories[$idea->categoryid]->name) ?>
                </a>
            </div>

            <div class="flex items-center text-sm text-muted-foreground bg-muted/30 p-3 rounded-lg border border-dashed">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <a href="<?= base_url('home/profile/' . $idea->authorid . '/' . url_title((string) $idea->user, '-', true)) ?>" class="font-medium text-foreground hover:underline mr-1"><?= esc($idea->user) ?></a>
                <span><?= esc($lang['text_shared_this_idea']) ?></span>
                <span class="mx-2">&bull;</span>
                <span><?= esc($idea->date) ?></span>
            </div>

            <?php if (! empty($tags)): ?>
            <div class="mt-4 flex flex-wrap gap-2">
                <?php foreach ($tags as $tag): ?>
                    <span class="inline-flex items-center rounded-md bg-secondary px-2 py-1 text-xs font-medium text-secondary-foreground ring-1 ring-inset ring-secondary-foreground/10">
                        <?= esc($tag->name) ?>
                    </span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (! empty($attachments)): ?>
            <div class="mt-6 border-t pt-4">
                <h4 class="text-sm font-semibold mb-2"><?= esc($lang['label_attachments']) ?>:</h4>
                <ul class="space-y-1">
                <?php foreach ($attachments as $attachment): ?>
                    <li>
                        <a href="<?= base_url('download/attachment/' . $attachment->id) ?>" class="text-sm text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            <?= esc($attachment->file_name) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Admin Actions -->
<?php if (is_admin(1)): ?>
<div class="bg-destructive/10 border-destructive/20 text-destructive-foreground rounded-lg border p-4 mb-8 sm:ml-32">
    <h4 class="text-sm font-semibold text-destructive mb-3 uppercase tracking-wider">Admin Actions</h4>
    <div class="flex flex-wrap gap-3 items-center">
        <?php if ($idea->status === 'new'): ?>
            <form method="post" action="<?= base_url('adminaction/approveidea') ?>">
                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $idea->id ?>">
                <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-green-600 text-white hover:bg-green-700 h-9 px-4">
                    <?= esc($lang['label_idea_approve']) ?>
                </button>
            </form>
        <?php elseif ($idea->status !== 'completed' && $idea->status !== 'declined'): ?>
            <form method="post" action="<?= base_url('adminaction/ideastatus') ?>" class="flex items-center gap-2">
                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $idea->id ?>">
                <select name="status" class="flex h-9 w-[150px] rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                    <option value="declined"><?= esc($lang['idea_declined']) ?></option>
                    <option value="considered"><?= esc($lang['idea_considered']) ?></option>
                    <option value="planned"><?= esc($lang['idea_planned']) ?></option>
                    <option value="started"><?= esc($lang['idea_started']) ?></option>
                    <option value="completed"><?= esc($lang['idea_completed']) ?></option>
                </select>
                <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-secondary text-secondary-foreground hover:bg-secondary/80 h-9 px-4">
                    <?= esc($lang['label_change_status']) ?>
                </button>
            </form>
        <?php endif; ?>
        
        <form method="post" action="<?= base_url('adminaction/deleteidea') ?>" onsubmit="return confirm('<?= esc($lang['text_sure_delete_idea'], 'js') ?>');">
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $idea->id ?>">
            <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-9 px-4">
                <?= esc($lang['label_delete_idea']) ?>
            </button>
        </form>
        
        <a href="<?= base_url('admin/users/' . $idea->authorid) ?>" target="_blank" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-9 px-4">
            <?= esc($lang['label_ban_user']) ?>
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Comments Section -->
<div class="sm:ml-32">
    <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
        <svg class="w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
        Comments (<?= count($comments) ?>)
    </h3>

    <?php if (is_logged_in()): ?>
    <div class="bg-card text-card-foreground border rounded-lg p-4 mb-8 shadow-sm">
        <!-- This form will be replaced by the CommentForm Island in Phase 3.3 -->
        <form method="post" action="<?= base_url('action/comment') ?>" class="space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="idea_id" value="<?= (int) $idea->id ?>">
            <div>
                <label class="block text-sm font-medium mb-2">Leave a comment</label>
                <textarea name="content" required rows="4" class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"></textarea>
            </div>
            <button type="submit" name="commentbutton" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                <?= esc($lang['label_submit']) ?>
            </button>
        </form>
    </div>
    <?php endif; ?>

    <div class="space-y-4">
        <?php foreach ($comments as $comment): ?>
        <div class="bg-background border rounded-lg p-4 shadow-sm relative group">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm uppercase">
                    <?= substr(esc($comment->user), 0, 1) ?>
                </div>
                <div>
                    <a href="<?= base_url('home/profile/' . $comment->userid . '/' . url_title((string) $comment->user, '-', true)) ?>" class="font-semibold text-sm hover:underline"><?= esc($comment->user) ?></a>
                    <div class="text-xs text-muted-foreground"><?= esc($comment->date) ?></div>
                </div>
                
                <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                    <?php if (is_admin(1)): ?>
                        <form method="post" action="<?= base_url('adminaction/deletecomment') ?>" onsubmit="return confirm('<?= esc($lang['text_sure_delete_comment'], 'js') ?>');">
                            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $comment->id ?>">
                            <button type="submit" class="text-xs text-destructive hover:underline p-1">
                                <?= esc($lang['label_delete_comment']) ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="post" action="<?= base_url('action/flag') ?>">
                            <?= csrf_field() ?><input type="hidden" name="cid" value="<?= (int) $comment->id ?>"><input type="hidden" name="idea_id" value="<?= (int) $idea->id ?>">
                            <button type="submit" class="text-xs text-muted-foreground hover:text-destructive hover:underline p-1 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path></svg>
                                <?= esc($lang['text_flag_comment']) ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <div class="text-sm whitespace-pre-wrap text-foreground/90 pl-11">
                <?= nl2br(esc($comment->content)) ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($comments)): ?>
            <div class="text-center py-12 text-muted-foreground bg-muted/20 rounded-lg border border-dashed">
                <svg class="w-12 h-12 mx-auto mb-3 text-muted-foreground/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <p>No comments yet. Be the first to share your thoughts!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
