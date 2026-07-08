<!DOCTYPE html>
<html lang="<?= esc($langCode ?? 'en', 'attr') ?>" dir="<?= esc($langDir ?? 'ltr', 'attr') ?>">

<head>
    <title><?= esc($title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>" sizes="16x16">
    <script>
        // Apply the theme before any CSS/JS loads to avoid a flash of the wrong theme.
        (function () {
            try {
                var theme = localStorage.getItem('theme');
                if (theme === 'dark' || (theme !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) { }
        })();
    </script>
    <?= vite_tags() ?>
</head>

<body class="bg-background text-foreground min-h-screen flex flex-col font-sans antialiased">

    <!-- Header / Navbar -->
    <header class="border-b bg-card text-card-foreground">
        <div class="container mx-auto px-4 h-16 flex items-center justify-between">
            <h1 class="text-xl font-bold tracking-tight">
                <a href="<?= base_url() ?>"><?= esc($title) ?></a>
            </h1>

            <div class="flex items-center gap-4">
                <div data-vue-component="ThemeToggle" data-props="<?= esc(json_encode([
                    'labels' => [
                        'light' => $lang['label_theme_light'],
                        'dark' => $lang['label_theme_dark'],
                        'system' => $lang['label_theme_system'],
                        'toggle' => $lang['label_toggle_theme'],
                    ],
                ]), 'attr') ?>"></div>
                <?php if (is_logged_in()): ?>
                    <div data-vue-component="UserDropdownIsland" data-props="<?= esc(json_encode([
                        'username' => current_username(),
                        'profileUrl' => base_url('profile/' . current_user_id() . '/' . url_title((string) current_username(), '-', true)),
                        'isAdmin' => is_admin(1),
                        'adminUrl' => base_url('admin/dashboard'),
                        'logoutUrl' => base_url('action/logout'),
                        'csrfTokenName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'labels' => [
                            'logged_as' => $lang['label_logged_as'] ?? 'Logged in as',
                            'view_profile' => $lang['label_view_profile'] ?? 'View Profile',
                            'admin_panel' => $lang['label_admin_panel'] ?? 'Admin Panel',
                            'logout' => $lang['label_log_out'] ?? 'Log out',
                        ]
                    ]), 'attr') ?>"></div>
                <?php else: ?>
                    <a href="<?= base_url('login') ?>" class="text-sm font-medium hover:underline">
                        <?= esc($lang['label_log_in']) ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="container mx-auto px-4 py-8 flex-1 flex flex-col md:flex-row gap-8">

        <!-- Left content -->
        <div class="w-full md:w-3/4 order-2 md:order-1">
            <?= $this->renderSection('content') ?>
        </div>

        <!-- Sidebar Menu -->
        <aside class="w-full md:w-1/4 order-1 md:order-2 space-y-8">
            <!-- Search -->
            <div>
                <form action="<?= base_url('search') ?>" method="POST" class="flex gap-2">
                    <?= csrf_field() ?>
                    <input type="search" name="query" placeholder="<?= esc($lang['label_search']) ?>"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                    <button type="submit"
                        class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                        <?= esc($lang['label_search']) ?>
                    </button>
                </form>
            </div>

            <!-- Post Idea -->
            <div>
                <a href="<?= base_url('postidea') ?>"
                    class="inline-flex w-full items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                    <?= esc($lang['label_post_new_idea']) ?>
                </a>
            </div>

            <!-- Categories -->
            <div>
                <h3 class="text-sm font-semibold mb-3 uppercase tracking-wider text-muted-foreground">
                    <?= esc($lang['label_categories']) ?>
                </h3>
                <nav class="space-y-1">
                    <?php foreach ($categories as $cat): ?>
                        <a href="<?= esc($cat->url, 'attr') ?>"
                            class="flex items-center justify-between px-3 py-2 text-sm rounded-md hover:bg-muted <?= !$cat->ideas ? 'opacity-50 pointer-events-none' : '' ?>">
                            <span><?= esc($cat->name) ?></span>
                            <span
                                class="bg-muted-foreground/20 text-foreground text-xs rounded-full px-2 py-0.5"><?= esc($cat->ideas) ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
        </aside>

    </main>

    <!-- Footer -->
    <footer class="border-t bg-muted/40 mt-auto">
        <div class="container mx-auto px-4 py-6 text-center text-sm text-muted-foreground">
            &copy; 2014-<?= date('Y') ?> - Powered by <a href="http://www.phpback.org/" target="_blank" rel="noopener"
                class="font-medium hover:underline">PHPBack</a>
        </div>
    </footer>

</body>

</html>