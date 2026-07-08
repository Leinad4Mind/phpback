<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Panel - <?= esc($title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>" sizes="16x16">
    <script>
        // Apply the theme before any CSS/JS loads to avoid a flash of the wrong theme.
        (function () {
            try {
                var theme = localStorage.getItem('theme');
                if (theme === 'dark' || (theme !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>
    <?= vite_tags() ?>
</head>
<body class="bg-muted min-h-screen flex flex-col font-sans antialiased dark:bg-background">
    
    <!-- Admin Navigation -->
    <header class="bg-slate-900 text-slate-50 sticky top-0 z-30">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- Logo & Nav -->
                <div class="flex items-center gap-8">
                    <a href="<?= base_url('admin/dashboard') ?>" class="flex items-center gap-3 hover:opacity-90">
                        <img src="<?= base_url('img/logo_small_free.png') ?>" alt="PHPBack" class="h-8 brightness-0 invert">
                        <span class="font-bold tracking-tight"><?= esc($lang['label_admin_panel'] ?? 'Admin Panel') ?></span>
                    </a>
                    
                    <nav class="hidden md:flex items-center gap-1">
                        <a href="<?= base_url('admin/dashboard') ?>" class="px-3 py-2 rounded-md text-sm font-medium <?= ($active === 'dashboard') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white transition-colors' ?>"><?= esc($lang['label_dashboard'] ?? 'Dashboard') ?></a>
                        
                        <a href="<?= base_url('admin/ideas') ?>" class="px-3 py-2 rounded-md text-sm font-medium <?= ($active === 'ideas') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white transition-colors' ?>"><?= esc($lang['label_ideas_comments'] ?? 'Ideas and Comments') ?></a>
                        
                        <?php if (is_admin(2)): ?>
                            <a href="<?= base_url('admin/users') ?>" class="px-3 py-2 rounded-md text-sm font-medium <?= ($active === 'users') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white transition-colors' ?>"><?= esc($lang['label_users_management'] ?? 'Users Management') ?></a>
                        <?php endif; ?>
                        
                        <?php if (is_admin(3)): ?>
                            <a href="<?= base_url('admin/system') ?>" class="px-3 py-2 rounded-md text-sm font-medium <?= ($active === 'system') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white transition-colors' ?>"><?= esc($lang['label_system_settings'] ?? 'System Settings') ?></a>
                        <?php endif; ?>
                        
                        <div class="h-4 w-px bg-slate-700 mx-2"></div>
                        <a href="<?= base_url() ?>" class="px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                            <?= esc($lang['label_view_site'] ?? 'View Site') ?>
                        </a>
                    </nav>
                </div>

                <!-- Right side -->
                <div class="flex items-center gap-4">
                    <div data-vue-component="ThemeToggle" data-props="<?= esc(json_encode([
                        'buttonClass' => 'text-slate-300 hover:text-white hover:bg-slate-800',
                    ]), 'attr') ?>"></div>
                    
                    <div class="flex items-center gap-3 text-sm">
                        <span class="text-slate-400"><?= esc($lang['label_signed_in_as'] ?? 'Signed in as') ?></span>
                        <span class="font-medium text-emerald-400"><?= esc(session('username')) ?></span>
                        
                        <form action="<?= base_url('action/logout') ?>" method="post" class="ml-2 border-l border-slate-700 pl-4">
                            <?= csrf_field() ?>
                            <button type="submit" class="text-red-400 hover:text-red-300 hover:underline font-medium transition-colors">
                                <?= esc($lang['label_log_out'] ?? 'Log out') ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8 flex-1">
        <div class="bg-card text-card-foreground rounded-xl shadow-sm border p-6 min-h-[500px]">
            <?= $this->renderSection('content') ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="mt-auto py-6 text-center text-sm text-muted-foreground">
        &copy; <?= date('Y') ?> - Powered by <a href="http://www.phpback.org/" target="_blank" rel="noopener" class="font-medium hover:underline">PHPBack</a>
    </footer>

</body>
</html>
