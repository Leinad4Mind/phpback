<?= $this->extend('templates/layout') ?>
<?= $this->section('content') ?>

<!-- Breadcrumbs -->
<nav class="flex text-sm text-muted-foreground mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="<?= base_url() ?>" class="hover:text-foreground transition-colors"><?= esc($lang['label_feedback']) ?></a>
        </li>
        <li aria-current="page">
            <div class="flex items-center">
                <svg class="w-3 h-3 mx-1 text-muted-foreground" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                </svg>
                <span class="ml-1 font-medium text-foreground md:ml-2"><?= esc($lang['label_post_new_idea']) ?></span>
            </div>
        </li>
    </ol>
</nav>

<div class="bg-card text-card-foreground rounded-lg border shadow-sm p-6 mb-8 max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold mb-6"><?= esc($lang['label_post_new_idea']) ?></h2>

    <?php if (! is_logged_in()): ?>
    <div class="bg-destructive/10 text-destructive border border-destructive/20 p-4 rounded-md flex items-center gap-3">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <p class="font-medium text-sm"><?= esc($lang['error_login_to_post']) ?></p>
    </div>
    <?php else: ?>
    
    <?php if ($error !== 'none'): ?>
    <div class="bg-destructive/10 text-destructive border border-destructive/20 p-4 rounded-md mb-6 flex items-center gap-3">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <p class="font-medium text-sm">
        <?php
            switch ($error) {
                case 'errortitle': echo esc($lang['error_title']); break;
                case 'errorcat':   echo esc($lang['error_category']); break;
                case 'errordesc':  echo esc($lang['error_description']); break;
                case 'errorfile':
                case 'errorsize':  echo esc($lang['error_upload']); break;
            }
        ?>
        </p>
    </div>
    <?php endif; ?>

    <form name="post-idea-form" method="post" action="<?= base_url('action/newidea') ?>" enctype="multipart/form-data" class="space-y-6">
        <?= csrf_field() ?>
        
        <div class="space-y-2">
            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"><?= esc($lang['label_idea_title']) ?></label>
            <input type="text" name="title" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" value="<?= esc($POST['title'] ?? '', 'attr') ?>" minlength="9" maxlength="100" required>
        </div>
        
        <div class="space-y-2">
            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"><?= esc($lang['label_category']) ?></label>
            <select name="category" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" required>
                <option value=""><?= esc($lang['text_select_category']) ?></option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= (int) $cat->id ?>" <?= ((string) ($POST['catid'] ?? '') === (string) $cat->id) ? 'selected="selected"' : '' ?>><?= esc($cat->name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="space-y-2">
            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"><?= esc($lang['label_description']) ?></label>
            <textarea name="description" rows="5" class="flex min-h-[120px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" minlength="20" maxlength="1500" required><?= esc($POST['desc'] ?? '') ?></textarea>
        </div>
        
        <div class="space-y-2">
            <label for="tags" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                <?= esc($lang['label_tags']) ?> <span class="text-muted-foreground font-normal">(<?= esc($lang['text_press_enter_to_add']) ?>)</span>
            </label>
            <div data-vue-component="TagsInputIsland" data-props="<?= esc(json_encode([
                'inputName' => 'tags',
                'placeholder' => $lang['text_add_tag'],
                'initialTags' => ''
            ]), 'attr') ?>"></div>
        </div>
        
        <div class="space-y-2">
            <label for="attachment" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                <?= esc($lang['label_attachment']) ?> <span class="text-muted-foreground font-normal">(<?= esc($lang['text_attachment_hint']) ?>)</span>
            </label>
            <input type="file" name="attachment" id="attachment" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-secondary file:text-secondary-foreground file:h-full file:px-4 file:-mx-3 file:-my-2 file:mr-3 file:cursor-pointer hover:file:bg-secondary/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx">
        </div>
        
        <div class="pt-4 border-t">
            <button type="submit" class="inline-flex w-full items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-11 px-8">
                <?= esc($lang['label_submit']) ?>
            </button>
        </div>
    </form>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
