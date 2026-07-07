<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHPBack Installer</title>
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>" sizes="16x16">
    <?= vite_tags() ?>
</head>
<body class="bg-muted min-h-screen flex items-center justify-center font-sans antialiased p-4">
    <div class="w-full max-w-lg bg-card text-card-foreground border shadow-sm rounded-xl p-8">
        
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold tracking-tight text-foreground">PHPBack Installer</h1>
            <p class="text-muted-foreground mt-2">Set up or upgrade your feedback system.</p>
        </div>

        <?php if (! empty($message)): ?>
            <div class="bg-green-50 text-green-700 border border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-900 p-4 rounded-md mb-6 font-medium flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <?= esc($message) ?>
            </div>
        <?php endif; ?>
        
        <?php if (! empty($error)): ?>
            <div class="bg-destructive/10 text-destructive border border-destructive/20 p-4 rounded-md mb-6 font-medium flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <?= esc($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($state === 'noconfig'): ?>
            <div class="bg-destructive/10 text-destructive border border-destructive/20 p-4 rounded-md mb-6 flex items-start gap-3">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div>
                    <strong class="font-medium">Cannot connect to the database.</strong>
                    <div class="text-sm mt-1 opacity-90"><?= esc($connectError ?? '') ?></div>
                </div>
            </div>
            
            <p class="text-sm text-muted-foreground mb-6">
                Enter your database connection details. They will be written to the project's <code class="bg-muted px-1.5 py-0.5 rounded text-foreground font-mono text-xs">.env</code> file.
            </p>
            
            <form method="post" action="<?= base_url('install/run') ?>" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="saveconfig">
                
                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none">Database Driver</label>
                    <select name="db_driver" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        <option value="MySQLi">MySQL / MariaDB</option>
                        <option value="Postgre">PostgreSQL</option>
                        <option value="SQLite3">SQLite</option>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none">Host</label>
                        <input type="text" name="db_host" value="localhost" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none">Port</label>
                        <input type="text" name="db_port" value="3306" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none">Database Name</label>
                    <input type="text" name="db_name" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none">Username</label>
                        <input type="text" name="db_user" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none">Password</label>
                        <input type="password" name="db_pass" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none">Table Prefix</label>
                    <input type="text" name="db_prefix" value="voice_" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <p class="text-xs text-muted-foreground">Existing PHPBack installs use <code class="font-mono bg-muted px-1 rounded">voice_</code>. Leave empty for none.</p>
                </div>
                
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 mt-4">
                    Save &amp; Continue
                </button>
            </form>

        <?php elseif ($state === 'install'): ?>
            <div class="bg-green-50 text-green-700 border border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-900 p-4 rounded-md mb-6 font-medium flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Database connected. Ready for a fresh installation.
            </div>
            
            <form method="post" action="<?= base_url('install/run') ?>" class="space-y-6">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="install">
                
                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none">Site Title</label>
                    <input type="text" name="site_title" value="PHPBack" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                </div>
                
                <div class="pt-6 border-t">
                    <h3 class="text-lg font-semibold tracking-tight mb-4">Create Administrator Account</h3>
                    
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none">Name</label>
                            <input type="text" name="admin_name" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none">Email</label>
                            <input type="email" name="admin_email" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none">Password <span class="text-muted-foreground font-normal">(min. 6 characters)</span></label>
                            <input type="password" name="admin_password" minlength="6" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-11 px-8">
                    Install PHPBack
                </button>
            </form>

        <?php elseif ($state === 'upgrade'): ?>
            <div class="bg-blue-50 text-blue-800 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-900 p-4 rounded-md mb-6 flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div class="font-medium">An existing PHPBack database was detected.</div>
            </div>
            
            <p class="text-sm text-foreground mb-6 leading-relaxed">
                There <?= $pending === 1 ? 'is' : 'are' ?> <span class="inline-flex items-center rounded-full bg-primary px-2 py-0.5 text-xs font-semibold text-primary-foreground"><?= (int) $pending ?></span> pending database migration<?= $pending === 1 ? '' : 's' ?> to apply. This is safe and will not remove existing data.
            </p>
            
            <form method="post" action="<?= base_url('install/run') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="upgrade">
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                    Apply Upgrade
                </button>
            </form>

        <?php else: /* done */ ?>
            <div class="bg-green-50 text-green-700 border border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-900 p-6 rounded-lg mb-6 text-center">
                <svg class="w-12 h-12 mx-auto mb-3 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <h3 class="text-lg font-bold mb-1">Installation Complete!</h3>
                <p class="text-sm opacity-90">PHPBack is installed and the database is up to date.</p>
            </div>
            
            <div class="bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-900 p-4 rounded-md mb-8 text-sm flex items-start gap-3">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <p>For security, consider removing or protecting the installer route once you are done.</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <a href="<?= base_url('home') ?>" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                    Go to Site
                </a>
                <a href="<?= base_url('admin') ?>" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                    Admin Panel
                </a>
            </div>
        <?php endif; ?>
        
    </div>
</body>
</html>
