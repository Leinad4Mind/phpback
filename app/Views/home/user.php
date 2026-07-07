<?= $this->extend('templates/layout') ?>
<?= $this->section('content') ?>

<?php $isOwner = is_logged_in() && (int) $user->id === current_user_id(); ?>
<?php $err = (int) (session('error') ?? $error); ?>

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
                <span class="ml-1 font-medium text-foreground md:ml-2"><?= esc($lang['label_profiles']) ?></span>
            </div>
        </li>
        <li aria-current="page">
            <div class="flex items-center">
                <svg class="w-3 h-3 mx-1 text-muted-foreground" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                </svg>
                <span class="ml-1 font-medium text-foreground md:ml-2"><?= esc($user->name) ?></span>
            </div>
        </li>
    </ol>
</nav>

<div class="mb-8 pb-6 border-b flex flex-wrap items-center justify-between gap-4">
    <div>
        <h2 class="text-3xl font-bold tracking-tight text-foreground flex items-center gap-3">
            <?= esc($user->name) ?>
            <?php if ($user->isadmin): ?>
                <span class="inline-flex items-center rounded-full bg-destructive/10 px-2.5 py-0.5 text-xs font-semibold text-destructive">
                    Admin
                </span>
            <?php endif; ?>
        </h2>
        <?php if ($isOwner): ?>
            <p class="text-muted-foreground mt-2">
                <?= esc($user->votes) ?> <?= esc($lang['text_votes_left']) ?>
            </p>
        <?php endif; ?>
    </div>
    
    <?php if (! $isOwner && is_admin(2)): ?>
        <a href="<?= base_url('admin/users/' . $user->id) ?>" target="_blank" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-9 px-4">
            <?= esc($lang['label_ban_user']) ?>
        </a>
    <?php endif; ?>
</div>

<?php if ($isOwner): ?>
<div class="bg-card text-card-foreground rounded-lg border shadow-sm p-6 mb-8">
    <h3 class="text-xl font-bold mb-4">Account Settings</h3>
    
    <?php if ($err > 0): ?>
        <div class="bg-destructive/10 text-destructive border border-destructive/20 p-3 rounded-md mb-6 text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <?php if ($err === 1) echo esc($lang['error_passwords']); ?>
            <?php if ($err === 2) echo esc($lang['error_password_old']); ?>
            <?php if ($err === 3) echo esc($lang['error_password']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (session('message') === 'passwordchanged'): ?>
        <div class="bg-green-50 text-green-700 border border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-900 p-3 rounded-md mb-6 text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <?= esc($lang['message_password_changed']) ?>
        </div>
    <?php endif; ?>

    <div data-vue-component="TabNav" data-props="<?= esc(json_encode([
        'tabs' => [
            ['id' => 'reset-votes', 'label' => $lang['label_reset_votes']],
            ['id' => 'change-password', 'label' => $lang['label_change_password']],
        ],
        'links' => is_admin(1) ? [
            ['label' => mb_strtoupper($lang['label_admin_panel']), 'href' => base_url('admin'), 'external' => true],
        ] : [],
    ]), 'attr') ?>"></div>

    <div id="reset-votes-panel" class="settings-panel block">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-muted-foreground uppercase bg-muted/50 border-b">
                    <tr>
                        <th scope="col" class="px-4 py-3"><?= esc($lang['label_idea']) ?></th>
                        <th scope="col" class="px-4 py-3 w-24"><?= esc($lang['label_votes']) ?></th>
                        <th scope="col" class="px-4 py-3 w-32 text-right"><?= esc($lang['label_actions']) ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach (($votes ?? []) as $vote): ?>
                        <tr class="bg-background hover:bg-muted/50 transition-colors">
                            <td class="px-4 py-3 font-medium text-foreground">
                                <a href="<?= esc($vote['idea']->url, 'attr') ?>" class="hover:underline"><?= esc($vote['idea']->title) ?></a>
                            </td>
                            <td class="px-4 py-3"><?= esc($vote['number']) ?></td>
                            <td class="px-4 py-3 text-right">
                                <form method="post" action="<?= base_url('action/unvote') ?>">
                                    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $vote['id'] ?>">
                                    <button type="submit" class="text-orange-600 dark:text-orange-400 hover:underline text-xs font-medium">
                                        <?= esc($lang['label_delete_votes']) ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($votes)): ?>
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-muted-foreground"><?= esc($lang['text_no_votes_cast']) ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="change-password-panel" class="settings-panel hidden max-w-sm">
        <form name="password-change-form" method="post" action="<?= base_url('action/changepassword') ?>" onsubmit="return validatePasswordForm()" class="space-y-4">
            <?= csrf_field() ?>
            <div class="space-y-2">
                <label class="text-sm font-medium leading-none"><?= esc($lang['form_password_old']) ?></label>
                <input type="password" name="old" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" required>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium leading-none flex justify-between">
                    <?= esc($lang['form_password_new']) ?>
                    <span id="password-error-show" class="text-xs text-destructive"></span>
                </label>
                <input type="password" name="new" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" minlength="6" required>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium leading-none"><?= esc($lang['from_password_new_repeat']) ?></label>
                <input type="password" name="rnew" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" required>
            </div>
            <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full">
                <?= esc($lang['label_change_password']) ?>
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="bg-card text-card-foreground rounded-lg border shadow-sm p-6">
    <div data-vue-component="TabNav" data-props="<?= esc(json_encode([
        'tabs' => [
            ['id' => 'activity', 'label' => $lang['label_activity']],
            ['id' => 'ideas', 'label' => $lang['label_ideas'], 'count' => count($ideas)],
            ['id' => 'comments', 'label' => $lang['label_comments']],
        ],
    ]), 'attr') ?>"></div>

    <div id="activity-panel" class="activity-panel block">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-muted-foreground uppercase bg-muted/50 border-b">
                    <tr>
                        <th scope="col" class="px-4 py-3"><?= esc($lang['label_log']) ?></th>
                        <th scope="col" class="px-4 py-3 w-48"><?= esc($lang['label_date']) ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($logs as $log): ?>
                    <tr class="bg-background hover:bg-muted/50 transition-colors">
                        <td class="px-4 py-3 text-foreground"><?= esc($log->content) ?></td>
                        <td class="px-4 py-3 text-muted-foreground text-xs"><?= esc($log->date) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="2" class="px-4 py-8 text-center text-muted-foreground">No activity logs found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="ideas-panel" class="activity-panel hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-muted-foreground uppercase bg-muted/50 border-b">
                    <tr>
                        <th scope="col" class="px-4 py-3"><?= esc($lang['label_idea']) ?></th>
                        <th scope="col" class="px-4 py-3 w-40"><?= esc($lang['label_category']) ?></th>
                        <th scope="col" class="px-4 py-3 w-32"><?= esc($lang['label_comments']) ?></th>
                        <th scope="col" class="px-4 py-3 w-32"><?= esc($lang['label_votes']) ?></th>
                        <th scope="col" class="px-4 py-3 w-32"><?= esc($lang['label_date']) ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($ideas as $idea): ?>
                    <?php
                        $rowColor = match($idea->status) {
                            'declined' => 'bg-red-50/50 dark:bg-red-900/10',
                            'started' => 'bg-green-50/50 dark:bg-green-900/10',
                            'planned' => 'bg-orange-50/50 dark:bg-orange-900/10',
                            'completed' => 'bg-blue-50/50 dark:bg-blue-900/10',
                            'considered' => 'bg-muted/30',
                            default => 'bg-background hover:bg-muted/50',
                        };
                    ?>
                    <tr class="transition-colors <?= $rowColor ?>">
                        <td class="px-4 py-3 font-medium">
                            <a href="<?= esc($idea->url, 'attr') ?>" class="hover:underline text-foreground"><?= esc($idea->title) ?></a>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground"><?= esc($categories[$idea->categoryid]->name ?? '') ?></td>
                        <td class="px-4 py-3 text-muted-foreground"><?= esc($idea->comments) ?></td>
                        <td class="px-4 py-3 text-muted-foreground"><?= esc($idea->votes) ?></td>
                        <td class="px-4 py-3 text-muted-foreground text-xs"><?= esc($idea->date) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($ideas)): ?>
                        <tr><td colspan="5" class="px-4 py-8 text-center text-muted-foreground">No ideas found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="comments-panel" class="activity-panel hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-muted-foreground uppercase bg-muted/50 border-b">
                    <tr>
                        <th scope="col" class="px-4 py-3"><?= esc($lang['label_commented']) ?></th>
                        <th scope="col" class="px-4 py-3 w-48"><?= esc($lang['label_date']) ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($comments as $comment): ?>
                    <tr class="bg-background hover:bg-muted/50 transition-colors">
                        <td class="px-4 py-3 font-medium">
                            <a href="<?= esc($comment['idea']->url, 'attr') ?>" class="hover:underline text-foreground"><?= esc($comment['idea']->title) ?></a>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground text-xs"><?= esc($comment['date']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($comments)): ?>
                        <tr><td colspan="2" class="px-4 py-8 text-center text-muted-foreground"><?= esc($lang['text_no_comments_found']) ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function validatePasswordForm() {
    var pass = document.forms["password-change-form"]["new"].value;
    var passVerify = document.forms["password-change-form"]["rnew"].value;
    var errorShowDiv = document.getElementById("password-error-show");
    var passMatchError = "<?= esc($lang['error_passwords'], 'js') ?>";
    
    if (pass != passVerify) {
        errorShowDiv.innerHTML = passMatchError;
        document.forms["password-change-form"]["rnew"].focus();
        return false;
    }
    return true;
}
</script>

<?= $this->endSection() ?>
