<?= $this->extend('templates/admin_layout') ?>
<?= $this->section('content') ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold tracking-tight"><?= esc($lang['label_users_management'] ?? 'Users Management') ?></h2>
    <p class="text-muted-foreground mt-1 text-sm"><?= esc($lang['text_users_desc'] ?? 'Manage user accounts and moderate access to the platform.') ?></p>
</div>

<div data-vue-component="TabNav" data-props="<?= esc(json_encode([
    'tabs' => [
        ['id' => 'new-users', 'label' => $lang['label_active_users'] ?? 'Active Users'],
        ['id' => 'banned', 'label' => $lang['label_banned_list'] ?? 'Banned List'],
        ['id' => 'ban-user', 'label' => $lang['label_ban_user'] ?? 'Ban User'],
    ],
    'initialTab' => isset($idban) ? 'ban-user' : null,
]), 'attr') ?>"></div>

<!-- New Users Tab -->
<div id="new-users-panel" class="users-panel block">
    <div class="bg-card text-card-foreground border shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-muted-foreground uppercase bg-muted/50 border-b">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-24">ID</th>
                        <th scope="col" class="px-6 py-4 font-semibold"><?= esc($lang['label_name'] ?? 'Name') ?></th>
                        <th scope="col" class="px-6 py-4 font-semibold"><?= esc($lang['label_email'] ?? 'Email') ?></th>
                        <th scope="col" class="px-6 py-4 font-semibold w-32"><?= esc($lang['label_votes_left'] ?? 'Votes Left') ?></th>
                        <th scope="col" class="px-6 py-4 font-semibold w-40 text-right"><?= esc($lang['label_actions'] ?? 'Actions') ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/50">
                    <?php foreach ($users as $user): ?>
                    <tr class="bg-background hover:bg-muted/50 transition-colors">
                        <td class="px-6 py-4">
                            <a href="<?= base_url('profile/' . $user->id . '/' . url_title((string) $user->name, '-', true)) ?>" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">#<?= (int) $user->id ?></a>
                        </td>
                        <td class="px-6 py-4 font-medium text-foreground"><?= esc($user->name) ?></td>
                        <td class="px-6 py-4 text-muted-foreground"><?= esc($user->email) ?></td>
                        <td class="px-6 py-4 text-muted-foreground">
                            <span class="inline-flex items-center rounded-full bg-secondary px-2.5 py-0.5 text-xs font-semibold text-secondary-foreground">
                                <?= esc($user->votes) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="<?= base_url('admin/users/' . $user->id) ?>" class="inline-flex items-center justify-center rounded-md text-xs font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-8 px-3">
                                <?= esc($lang['label_ban_user'] ?? 'Ban User') ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="5" class="px-6 py-8 text-center text-muted-foreground"><?= esc($lang['text_no_active_users'] ?? 'No active users found.') ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Banned List Tab -->
<div id="banned-panel" class="users-panel hidden">
    <div class="bg-card text-card-foreground border shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-muted-foreground uppercase bg-muted/50 border-b">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-24">ID</th>
                        <th scope="col" class="px-6 py-4 font-semibold"><?= esc($lang['label_name'] ?? 'Name') ?></th>
                        <th scope="col" class="px-6 py-4 font-semibold"><?= esc($lang['label_email'] ?? 'Email') ?></th>
                        <th scope="col" class="px-6 py-4 font-semibold w-48"><?= esc($lang['label_status'] ?? 'Status') ?></th>
                        <th scope="col" class="px-6 py-4 font-semibold w-40 text-right"><?= esc($lang['label_actions'] ?? 'Actions') ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/50">
                    <?php foreach ($banned as $user): ?>
                    <tr class="bg-red-50/50 dark:bg-red-900/10 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        <td class="px-6 py-4">
                            <a href="<?= base_url('profile/' . $user->id . '/' . url_title((string) $user->name, '-', true)) ?>" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">#<?= (int) $user->id ?></a>
                        </td>
                        <td class="px-6 py-4 font-medium text-foreground"><?= esc($user->name) ?></td>
                        <td class="px-6 py-4 text-muted-foreground"><?= esc($user->email) ?></td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2 text-destructive text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                <?php
                                if ((int) $user->banned === -1) {
                                    echo esc($lang['text_indefinitely'] ?? 'Indefinitely');
                                } else {
                                    $d = $user->banned % 100;
                                    $m = ((int) ($user->banned / 100)) % 100;
                                    $y = (int) ($user->banned / 10000);
                                    echo esc($lang['text_until'] ?? 'Until') . ' ' . esc("$d/$m/$y");
                                }
                                ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <form method="post" action="<?= base_url('adminaction/unban') ?>">
                                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $user->id ?>">
                                <button type="submit" class="inline-flex items-center justify-center rounded-md text-xs font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 px-3">
                                    <?= esc($lang['label_disable_ban'] ?? 'Disable ban') ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($banned)): ?>
                        <tr><td colspan="5" class="px-6 py-8 text-center text-muted-foreground"><?= esc($lang['text_no_banned_users'] ?? 'No banned users.') ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Ban User Tab -->
<div id="ban-user-panel" class="users-panel hidden">
    <div class="bg-card text-card-foreground border shadow-sm rounded-lg p-6 max-w-md">
        <form role="form" method="post" action="<?= base_url('adminaction/banuser') ?>" class="space-y-4">
            <?= csrf_field() ?>
            
            <div class="space-y-2">
                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"><?= esc($lang['label_user_id'] ?? 'User ID') ?></label>
                <input type="number" name="id" value="<?= esc($idban ?? '', 'attr') ?>" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" maxlength="9" placeholder="<?= esc($lang['placeholder_enter_user_id'] ?? 'Enter User ID', 'attr') ?>" required>
            </div>
            
            <div class="space-y-2">
                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                    <?= esc($lang['label_ban_length'] ?? 'Ban length in days') ?> <span class="text-muted-foreground font-normal"><?= esc($lang['text_0_for_indefinite'] ?? '(0 for indefinite ban)') ?></span>
                </label>
                <input type="number" name="days" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" maxlength="4" value="0" required>
            </div>
            
            <button name="banuser" type="submit" class="inline-flex w-full items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-10 px-4 py-2 mt-2">
                <?= esc($lang['label_ban_user'] ?? 'Ban User') ?>
            </button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
