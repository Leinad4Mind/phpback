<?= $this->extend('templates/admin_layout') ?>
<?= $this->section('content') ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold tracking-tight flex items-center gap-2">
        <?= esc($lang['label_system_settings'] ?? 'System Settings') ?>
        <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">v<?= esc($version) ?></span>
    </h2>
    <p class="text-muted-foreground mt-1 text-sm"><?= esc($lang['text_system_desc'] ?? 'Configure global application settings and manage categories.') ?></p>
</div>

<div data-vue-component="TabNav" data-props="<?= esc(json_encode([
    'tabs' => [
        ['id' => 'general', 'label' => $lang['label_general_settings'] ?? 'General Settings'],
        ['id' => 'admin', 'label' => $lang['label_admin_management'] ?? 'Admin Management'],
        ['id' => 'categories', 'label' => $lang['label_categories'] ?? 'Categories'],
    ],
]), 'attr') ?>"></div>

<!-- General Settings Tab -->
<div id="general-panel" class="system-panel block">
    <div class="bg-card text-card-foreground border shadow-sm rounded-lg p-6 max-w-2xl">
        <form role="form" method="post" action="<?= base_url('adminaction/editsettings') ?>" class="space-y-6">
            <?= csrf_field() ?>
            
            <div class="space-y-4">
                <?php foreach ($settings as $setting): ?>
                <div class="grid gap-2">
                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"><?= esc($lang['setting_' . $setting->name] ?? $setting->name) ?></label>
                    <?php if ($setting->name === 'language' && ! empty($availableLanguages)): ?>
                    <select name="setting-<?= (int) $setting->id ?>" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        <?php foreach ($availableLanguages as $slug => $nativeName): ?>
                            <option value="<?= esc($slug, 'attr') ?>" <?= $setting->value === $slug ? 'selected' : '' ?>><?= esc($nativeName) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php else: ?>
                    <input type="text" name="setting-<?= (int) $setting->id ?>" value="<?= esc($setting->value, 'attr') ?>" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <button name="submit-changes" type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                <?= esc($lang['label_save_changes'] ?? 'Save Changes') ?>
            </button>
        </form>
    </div>
</div>

<!-- Admin Management Tab -->
<div id="admin-panel" class="system-panel hidden">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2">
            <div class="bg-card text-card-foreground border shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-muted-foreground uppercase bg-muted/50 border-b">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold w-24">ID</th>
                                <th scope="col" class="px-6 py-4 font-semibold"><?= esc($lang['label_name'] ?? 'Name') ?></th>
                                <th scope="col" class="px-6 py-4 font-semibold"><?= esc($lang['label_email'] ?? 'Email') ?></th>
                                <th scope="col" class="px-6 py-4 font-semibold w-32"><?= esc($lang['label_level'] ?? 'Level') ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
                            <?php foreach ($adminusers as $user): ?>
                            <tr class="bg-background hover:bg-muted/50 transition-colors">
                                <td class="px-6 py-4">
                                    <a href="<?= base_url('home/profile/' . $user->id . '/' . url_title((string) $user->name, '-', true)) ?>" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">#<?= (int) $user->id ?></a>
                                </td>
                                <td class="px-6 py-4 font-medium text-foreground"><?= esc($user->name) ?></td>
                                <td class="px-6 py-4 text-muted-foreground"><?= esc($user->email) ?></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">
                                        <?= esc($lang['label_level'] ?? 'Level') ?> <?= esc($user->isadmin) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div>
            <div class="bg-card text-card-foreground border shadow-sm rounded-lg p-6 sticky top-24">
                <h3 class="text-lg font-semibold tracking-tight mb-4"><?= esc($lang['label_edit_admin_privileges'] ?? 'Edit Admin Privileges') ?></h3>
                <form role="form" method="post" action="<?= base_url('adminaction/editadmin') ?>" class="space-y-4">
                    <?= csrf_field() ?>
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none"><?= esc($lang['label_user_id'] ?? 'User ID') ?></label>
                        <input type="number" name="id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" required>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none"><?= esc($lang['label_admin_level'] ?? 'Admin Level') ?></label>
                        <select name="level" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            <option value="0"><?= esc($lang['label_admin_level_0'] ?? '0 - No Administration Rights') ?></option>
                            <option value="1"><?= esc($lang['label_admin_level_1'] ?? '1 - Ideas and Comments') ?></option>
                            <option value="2"><?= esc($lang['label_admin_level_2'] ?? '2 - Level 1 + User Management') ?></option>
                            <option value="3"><?= esc($lang['label_admin_level_3'] ?? '3 - Full Administration Rights') ?></option>
                        </select>
                    </div>
                    <button name="submit-create-admin" type="submit" class="inline-flex w-full items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 mt-2">
                        <?= esc($lang['label_apply_changes'] ?? 'Apply Changes') ?>
                    </button>
                </form>
            </div>
        </div>
        
    </div>
</div>

<!-- Categories Tab -->
<div id="categories-panel" class="system-panel hidden">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <div class="space-y-8">
            <div class="bg-card text-card-foreground border shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold tracking-tight mb-4"><?= esc($lang['label_add_update_category'] ?? 'Add or Update Category') ?></h3>
                <form role="form" method="post" action="<?= base_url('adminaction/addcategory') ?>" class="space-y-4">
                    <?= csrf_field() ?>
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none"><?= esc($lang['label_category_name'] ?? 'Category Name') ?></label>
                        <input type="text" name="name" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" required>
                        <p class="text-[0.8rem] text-muted-foreground"><?= esc($lang['text_tip_category'] ?? 'Tip: Enter an existing category name to update its description.') ?></p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none"><?= esc($lang['label_description'] ?? 'Description') ?></label>
                        <textarea name="description" rows="3" class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"></textarea>
                    </div>
                    <button name="add-category" type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                        <?= esc($lang['label_save_category'] ?? 'Save Category') ?>
                    </button>
                </form>
            </div>

            <div class="bg-destructive/5 border border-destructive/20 rounded-lg p-6">
                <h3 class="text-lg font-semibold tracking-tight mb-4 text-destructive flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <?= esc($lang['label_delete_category'] ?? 'Delete Category') ?>
                </h3>
                <form role="form" method="post" action="<?= base_url('adminaction/deletecategory') ?>" class="space-y-4" onsubmit="return confirm('<?= esc(addslashes($lang['text_confirm_delete_category'] ?? 'Are you sure you want to delete this category?')) ?>');">
                    <?= csrf_field() ?>
                    <div class="space-y-3">
                        <select name="catid" class="flex h-10 w-full rounded-md border border-destructive/50 bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int) $cat->id ?>"><?= esc($cat->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div data-vue-component="CheckboxIsland" data-props="<?= esc(json_encode([
                            'id' => 'delete-ideas',
                            'name' => 'ideas',
                            'label' => $lang['label_also_delete_ideas'] ?? 'Also delete all ideas in this category',
                            'value' => '1',
                            'checked' => false
                        ]), 'attr') ?>"></div>
                    </div>
                    <button name="delete-category" type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-10 px-4 py-2 mt-2">
                        <?= esc($lang['label_delete_category'] ?? 'Delete Category') ?>
                    </button>
                </form>
            </div>
        </div>

        <div>
            <div class="bg-card text-card-foreground border shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold tracking-tight mb-4"><?= esc($lang['label_rename_categories'] ?? 'Rename Categories') ?></h3>
                <form role="form" name="update-form" method="post" action="<?= base_url('adminaction/updatecategories') ?>" class="space-y-4">
                    <?= csrf_field() ?>
                    <div class="space-y-3 max-h-[500px] overflow-y-auto pr-2">
                        <?php foreach ($categories as $cat): ?>
                        <div class="relative">
                            <input type="text" name="category-<?= (int) $cat->id ?>" value="<?= esc($cat->name, 'attr') ?>" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button name="update-names" type="submit" class="inline-flex w-full items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                        <?= esc($lang['label_update_all_names'] ?? 'Update All Names') ?>
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
