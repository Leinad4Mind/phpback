<?= $this->extend('templates/layout') ?>
<?= $this->section('content') ?>

<div class="max-w-md mx-auto py-12">
    <div class="bg-card text-card-foreground rounded-lg border shadow-sm p-8 text-center">
        <div class="mb-8 flex justify-center">
            <img src="<?= base_url('img/logo_free.png') ?>" alt="PHPBack" class="h-12 object-contain dark:invert">
        </div>
        
        <h2 class="text-2xl font-bold tracking-tight mb-6">Admin Panel</h2>

        <?php if ($error === 'noadmin'): ?>
            <div class="bg-destructive/10 text-destructive border border-destructive/20 p-4 rounded-md mb-6 font-medium">
                You are not an admin
            </div>
        <?php else: ?>
            <?php if ($error === 'error'): ?>
                <div class="bg-destructive/10 text-destructive border border-destructive/20 p-3 rounded-md mb-6 text-sm font-medium">
                    Email or password are incorrect
                </div>
            <?php elseif ($error === 'toomany'): ?>
                <div class="bg-destructive/10 text-destructive border border-destructive/20 p-3 rounded-md mb-6 text-sm font-medium">
                    Too many attempts. Please try again later.
                </div>
            <?php endif; ?>

            <form action="<?= base_url('adminaction/login') ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                
                <div class="relative">
                    <svg class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <input type="email" name="email" class="flex h-10 w-full rounded-md border border-input bg-background pl-10 pr-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="Enter your email" required>
                </div>
                
                <div class="relative">
                    <svg class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    <input type="password" name="password" class="flex h-10 w-full rounded-md border border-input bg-background pl-10 pr-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="Password" required>
                </div>
                
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-11 mt-4">
                    Log In
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
