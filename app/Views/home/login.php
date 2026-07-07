<?= $this->extend('templates/layout') ?>
<?= $this->section('content') ?>

<!-- Breadcrumbs -->
<nav class="flex text-sm text-muted-foreground mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="<?= base_url() ?>" class="hover:text-foreground transition-colors">Feedback</a>
        </li>
        <li aria-current="page">
            <div class="flex items-center">
                <svg class="w-3 h-3 mx-1 text-muted-foreground" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                </svg>
                <span class="ml-1 font-medium text-foreground md:ml-2"><?= esc($lang['label_log_in']) ?></span>
            </div>
        </li>
    </ol>
</nav>

<div class="max-w-md mx-auto">
    <div class="bg-card text-card-foreground rounded-lg border shadow-sm p-6 mb-8">
        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold tracking-tight"><?= esc($lang['label_log_in']) ?></h2>
            <p class="text-sm text-muted-foreground mt-2">Enter your credentials to access your account</p>
        </div>

        <?php if ($error === 'errorlogin'): ?>
            <div class="bg-destructive/10 text-destructive border border-destructive/20 p-3 rounded-md mb-6 text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <?= esc($lang['error_login']) ?>
            </div>
        <?php elseif ($error === 'toomany'): ?>
            <div class="bg-destructive/10 text-destructive border border-destructive/20 p-3 rounded-md mb-6 text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <?= esc($lang['error_toomany']) ?>
            </div>
        <?php elseif ($error === 'register'): ?>
            <div class="bg-green-50 text-green-700 border border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-900 p-3 rounded-md mb-6 text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <?= esc($lang['text_registration_success']) ?>
            </div>
        <?php elseif ($error === 'banned'): ?>
            <div class="bg-destructive/10 text-destructive border border-destructive/20 p-3 rounded-md mb-6 text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <?= ($ban === -1) ? esc($lang['error_banned_inf']) : esc(str_replace('%s', (string) $ban, $lang['error_banned'])) ?>
            </div>
        <?php endif; ?>

        <form name="login-form" action="<?= base_url('action/login') ?>" method="POST" class="space-y-4">
            <?= csrf_field() ?>
            
            <div class="space-y-2">
                <label for="InputEmail" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"><?= esc($lang['form_email']) ?></label>
                <input type="email" id="InputEmail" name="email" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="<?= esc($lang['form_email'], 'attr') ?>" required>
            </div>
            
            <div class="space-y-2">
                <label for="InputPassword" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"><?= esc($lang['form_password']) ?></label>
                <input type="password" id="InputPassword" name="password" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="<?= esc($lang['form_password'], 'attr') ?>" required>
            </div>
            
            <div class="flex items-center space-x-2 pt-2">
                <input type="checkbox" id="checkbox1" name="rememberme" value="1" class="h-4 w-4 rounded border-input bg-background text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                <label for="checkbox1" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                    <?= esc($lang['form_remember']) ?>
                </label>
            </div>
            
            <button type="submit" class="inline-flex w-full mt-4 items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                <?= esc($lang['label_log_in']) ?>
            </button>
        </form>

        <div class="mt-6 text-center text-sm">
            <span class="text-muted-foreground">Don't have an account?</span>
            <a href="<?= base_url('home/register') ?>" class="font-medium text-primary hover:underline ml-1">
                <?= esc($lang['text_create_an_account']) ?>
            </a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
