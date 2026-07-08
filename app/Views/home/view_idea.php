<?= $this->extend('templates/layout') ?>
<?= $this->section('content') ?>

<!-- Breadcrumbs -->
<nav class="flex text-sm text-muted-foreground mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="<?= base_url() ?>" class="hover:text-foreground transition-colors"><?= esc($lang['label_feedback']) ?></a>
        </li>
        <li>
            <div class="flex items-center">
                <svg class="w-3 h-3 mx-1 text-muted-foreground" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                </svg>
                <a href="<?= esc(base_url('category/' . $idea->categoryid . '/' . url_title($categories[$idea->categoryid]->name, '-', true)), 'attr') ?>" class="ml-1 hover:text-foreground transition-colors md:ml-2"><?= esc($categories[$idea->categoryid]->name) ?></a>
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
        <?php if (is_logged_in()): ?>
        <div class="shrink-0" data-vue-component="VoteButton" data-props="<?= esc(json_encode([
            'ideaId' => (int) $idea->id,
            'initialTotalVotes' => (int) $idea->votes,
            'initialUserVoteId' => $userVote ? (int) $userVote->id : null,
            'initialUserVoteAmount' => $userVote ? (int) $userVote->number : 0,
            'csrfTokenName' => csrf_token(),
            'initialCsrfHash' => csrf_hash(),
            'voteUrl' => base_url('action/vote'),
            'unvoteUrl' => base_url('action/unvote'),
            'labels' => [
                'votes' => $lang['label_votes'],
                'vote' => $lang['label_vote'],
                'removeVote' => $lang['label_remove_vote'],
                'error' => $lang['error_request_failed'],
            ]
        ]), 'attr') ?>">
            <!-- Static fallback shown until the island mounts -->
            <div class="flex flex-col items-center sm:w-24 shrink-0 bg-muted/30 rounded-lg p-4 border border-dashed">
                <div class="text-3xl font-bold text-primary mb-1"><?= esc(number_format((int) $idea->votes)) ?></div>
                <div class="text-xs uppercase tracking-wider text-muted-foreground font-semibold"><?= esc($lang['label_votes']) ?></div>
            </div>
        </div>
        <?php else: ?>
        <div class="flex flex-col items-center sm:w-24 shrink-0 bg-muted/30 rounded-lg p-4 border border-dashed">
            <div class="text-3xl font-bold text-primary mb-1"><?= esc(number_format((int) $idea->votes)) ?></div>
            <div class="text-xs uppercase tracking-wider text-muted-foreground font-semibold mb-4"><?= esc($lang['label_votes']) ?></div>
            <div class="flex flex-col items-center w-full">
                <a href="<?= base_url('login') ?>" class="text-[10px] uppercase font-semibold text-primary hover:underline"><?= esc($lang['text_login_to_vote']) ?></a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Right Column: Idea Details -->
        <div class="flex-1">
            <h1 class="text-2xl font-bold mb-4"><?= esc($idea->title) ?></h1>
            
            <div class="prose max-w-none text-muted-foreground whitespace-pre-wrap">
                <?= purify_html($idea->content) ?>
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
                
                <a href="<?= esc(base_url('category/' . $idea->categoryid . '/' . url_title($categories[$idea->categoryid]->name, '-', true)), 'attr') ?>" class="text-muted-foreground hover:text-foreground flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                    <?= esc($categories[$idea->categoryid]->name) ?>
                </a>
            </div>

            <div class="flex items-center text-sm text-muted-foreground bg-muted/30 p-3 rounded-lg border border-dashed">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <a href="<?= base_url('profile/' . $idea->authorid . '/' . url_title((string) $idea->user, '-', true)) ?>" class="font-medium text-foreground hover:underline mr-1"><?= esc($idea->user) ?></a>
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
    <h4 class="text-sm font-semibold text-destructive mb-3 uppercase tracking-wider"><?= esc($lang['label_admin_actions']) ?></h4>
    <div class="flex flex-wrap gap-3 items-center">
        <?php if ($idea->status === 'new'): ?>
            <form method="post" action="<?= base_url('adminaction/approveidea') ?>">
                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $idea->id ?>">
                <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-green-600 text-white hover:bg-green-700 h-9 px-4">
                    <?= esc($lang['label_idea_approve']) ?>
                </button>
            </form>
        <?php else: ?>
            <div data-vue-component="AdminStatusSelect" data-props="<?= esc(json_encode([
                'ideaId' => (int) $idea->id,
                'initialStatus' => $idea->status,
                'csrfTokenName' => csrf_token(),
                'initialCsrfHash' => csrf_hash(),
                'updateUrl' => base_url('adminaction/ideastatus'),
                'statuses' => [
                    'declined' => $lang['idea_declined'],
                    'considered' => $lang['idea_considered'],
                    'planned' => $lang['idea_planned'],
                    'started' => $lang['idea_started'],
                    'completed' => $lang['idea_completed']
                ],
                'errorLabel' => $lang['error_request_failed']
            ]), 'attr') ?>"></div>
        <?php endif; ?>
        
        <div data-vue-component="ConfirmActionIsland" data-props="<?= esc(json_encode([
            'triggerText' => $lang['label_delete_idea'],
            'title' => $lang['label_delete_idea'],
            'description' => $lang['text_sure_delete_idea'],
            'confirmText' => $lang['label_delete_idea'],
            'actionUrl' => base_url('adminaction/deleteidea'),
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
            'payload' => ['id' => (int) $idea->id]
        ]), 'attr') ?>"></div>
        
        <a href="<?= base_url('admin/users/' . $idea->authorid) ?>" target="_blank" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-9 px-4">
            <?= esc($lang['label_ban_user']) ?>
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Comments Section -->
<div data-vue-component="CommentSection" data-props="<?= esc(json_encode([
    'ideaId' => (int) $idea->id,
    'isLoggedIn' => is_logged_in(),
    'isAdmin' => is_admin(1),
    'initialComments' => array_map(function($c) {
        return [
            'id' => (int) $c->id,
            'user' => $c->user,
            'userid' => (int) $c->userid,
            'date' => $c->date,
            'content' => purify_html($c->content)
        ];
    }, $comments),
    'csrfTokenName' => csrf_token(),
    'initialCsrfHash' => csrf_hash(),
    'submitUrl' => base_url('action/comment'),
    'deleteUrl' => base_url('adminaction/deletecomment'),
    'flagUrl' => base_url('action/flag'),
    'baseUrl' => base_url(),
    'labels' => [
        'comments' => $lang['label_comments'],
        'leaveComment' => $lang['text_leave_comment'],
        'submit' => $lang['label_submit'],
        'submitting' => $lang['text_submitting'],
        'delete' => $lang['label_delete'],
        'flag' => $lang['label_flag'],
        'noComments' => $lang['text_no_comments'],
        'sureDelete' => $lang['text_sure_delete_comment'],
        'sureFlag' => $lang['text_sure_flag_comment'],
        'flagged' => $lang['text_comment_flagged'],
        'error' => $lang['error_request_failed'],
    ]
]), 'attr') ?>"></div>

<?= $this->endSection() ?>
