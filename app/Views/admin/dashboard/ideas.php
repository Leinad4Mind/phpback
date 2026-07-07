<?= $this->extend('templates/admin_layout') ?>
<?= $this->section('content') ?>

<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold tracking-tight">Ideas and Comments</h2>
        <p class="text-muted-foreground mt-1 text-sm">Manage feedback, moderate comments, and track feature requests.</p>
    </div>
</div>

<div class="border-b mb-6">
    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="ideas-tabs">
        <li class="mr-2">
            <button type="button" onclick="showIdeasTab('new-ideas', this)" class="ideas-tab inline-flex items-center gap-2 p-4 border-b-2 border-primary text-primary rounded-t-lg active">
                New Ideas
                <span class="bg-primary/20 text-primary text-xs rounded-full px-2 py-0.5"><?= (int) $newideas_num ?></span>
            </button>
        </li>
        <li class="mr-2">
            <button type="button" onclick="showIdeasTab('all-ideas', this)" class="ideas-tab inline-flex items-center gap-2 p-4 border-b-2 border-transparent hover:text-foreground hover:border-muted-foreground rounded-t-lg text-muted-foreground">
                All Ideas
            </button>
        </li>
        <li class="mr-2">
            <button type="button" onclick="showIdeasTab('flagged-comments', this)" class="ideas-tab inline-flex items-center gap-2 p-4 border-b-2 border-transparent hover:text-foreground hover:border-muted-foreground rounded-t-lg text-muted-foreground">
                Flagged Comments
                <?php if(count($flags) > 0): ?>
                    <span class="bg-destructive/20 text-destructive text-xs rounded-full px-2 py-0.5"><?= count($flags) ?></span>
                <?php endif; ?>
            </button>
        </li>
    </ul>
</div>

<!-- New Ideas Tab -->
<div id="new-ideas-panel" class="ideas-panel block">
    <div class="bg-card text-card-foreground border shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-muted-foreground uppercase bg-muted/50 border-b">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Idea</th>
                        <th scope="col" class="px-6 py-4 font-semibold w-48">Category</th>
                        <th scope="col" class="px-6 py-4 font-semibold w-32">Comments</th>
                        <th scope="col" class="px-6 py-4 font-semibold w-32">Votes</th>
                        <th scope="col" class="px-6 py-4 font-semibold w-40">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/50">
                    <?php foreach ($newideas as $idea): ?>
                    <tr class="transition-colors <?= $idea->status === 'new' ? 'bg-primary/5 hover:bg-primary/10' : 'bg-background hover:bg-muted/50' ?>">
                        <td class="px-6 py-4 font-medium">
                            <a href="<?= base_url('home/idea/' . $idea->id . '/' . url_title((string) $idea->title, '-', true)) ?>" target="_blank" class="hover:underline text-foreground"><?= esc($idea->title) ?></a>
                        </td>
                        <td class="px-6 py-4 text-muted-foreground"><?= esc($categories[$idea->categoryid]->name ?? '') ?></td>
                        <td class="px-6 py-4 text-muted-foreground"><?= esc($idea->comments) ?></td>
                        <td class="px-6 py-4 text-muted-foreground"><?= esc($idea->votes) ?></td>
                        <td class="px-6 py-4 text-muted-foreground text-xs whitespace-nowrap"><?= esc($idea->date) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($newideas)): ?>
                        <tr><td colspan="5" class="px-6 py-8 text-center text-muted-foreground">No new ideas pending review.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- All Ideas Tab -->
<div id="all-ideas-panel" class="ideas-panel hidden">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Filters Sidebar -->
        <div class="w-full lg:w-1/4">
            <form role="form" method="post" action="<?= base_url('admin/ideas') ?>" class="bg-card border rounded-lg p-5 shadow-sm space-y-6">
                <?= csrf_field() ?>
                
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-3">Status</h3>
                    <div class="space-y-2">
                        <?php foreach (['completed' => 'Completed', 'started' => 'Started', 'planned' => 'Planned', 'considered' => 'Under Consideration', 'declined' => 'Declined'] as $key => $label): ?>
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" id="status-<?= $key ?>" name="status-<?= $key ?>" <?= ! empty($form['status-' . $key]) ? 'checked' : '' ?> class="h-4 w-4 rounded border-input bg-background text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                <label for="status-<?= $key ?>" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"><?= $label ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-3">Categories</h3>
                    <div class="space-y-2 max-h-60 overflow-y-auto pr-2">
                        <?php foreach ($categories as $category): ?>
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" id="category-<?= (int) $category->id ?>" name="category-<?= (int) $category->id ?>" <?= ! empty($form['category-' . $category->id]) ? 'checked' : '' ?> class="h-4 w-4 rounded border-input bg-background text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                <label for="category-<?= (int) $category->id ?>" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"><?= esc($category->name) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-3">Sorting</h3>
                    <div class="space-y-3">
                        <select name="orderby" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            <option value="votes">Order by Votes</option>
                            <option value="id" <?= ($form['orderby'] === 'id') ? 'selected' : '' ?>>Order by Date</option>
                            <option value="title" <?= ($form['orderby'] === 'title') ? 'selected' : '' ?>>Order by Title</option>
                        </select>
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" id="isdesc" name="isdesc" <?= ! empty($form['isdesc']) ? 'checked' : '' ?> class="h-4 w-4 rounded border-input bg-background text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            <label for="isdesc" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Decreasing order</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                    Apply Filters
                </button>
            </form>
        </div>

        <!-- Ideas Results -->
        <div class="w-full lg:w-3/4">
            <div class="bg-card text-card-foreground border shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-muted-foreground uppercase bg-muted/50 border-b">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold">Idea</th>
                                <th scope="col" class="px-6 py-4 font-semibold w-40">Category</th>
                                <th scope="col" class="px-6 py-4 font-semibold w-24">Votes</th>
                                <th scope="col" class="px-6 py-4 font-semibold w-32">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
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
                                <td class="px-6 py-4 font-medium">
                                    <div class="flex items-center gap-2">
                                        <?php
                                        $statusClass = match($idea->status) {
                                            'declined' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300',
                                            'started' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
                                            'planned' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300',
                                            'completed' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300',
                                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
                                        };
                                        ?>
                                        <span class="inline-block w-2 h-2 rounded-full <?= $statusClass ?>" title="<?= esc($idea->status) ?>"></span>
                                        <a href="<?= base_url('home/idea/' . $idea->id . '/' . url_title((string) $idea->title, '-', true)) ?>" target="_blank" class="hover:underline text-foreground line-clamp-1"><?= esc($idea->title) ?></a>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-muted-foreground"><?= esc($categories[$idea->categoryid]->name ?? '') ?></td>
                                <td class="px-6 py-4 text-muted-foreground"><?= esc($idea->votes) ?></td>
                                <td class="px-6 py-4 text-muted-foreground text-xs whitespace-nowrap"><?= esc($idea->date) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($ideas)): ?>
                                <tr><td colspan="4" class="px-6 py-8 text-center text-muted-foreground">No ideas matched the criteria.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    </div>
</div>

<!-- Flagged Comments Tab -->
<div id="flagged-comments-panel" class="ideas-panel hidden">
    <div class="bg-card text-card-foreground border shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-muted-foreground uppercase bg-muted/50 border-b">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-64">Context</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Comment Content</th>
                        <th scope="col" class="px-6 py-4 font-semibold w-72 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/50">
                    <?php foreach ($flags as $comment): ?>
                    <tr class="bg-background hover:bg-muted/50 transition-colors">
                        <td class="px-6 py-4 text-muted-foreground text-xs space-y-1">
                            <div><span class="font-semibold text-foreground">ID:</span> #<?= (int) $comment->id ?></div>
                            <div>
                                <span class="font-semibold text-foreground">User:</span> 
                                <a href="<?= base_url('admin/users/' . $comment->userid) ?>" class="text-blue-600 dark:text-blue-400 hover:underline">#<?= (int) $comment->userid ?></a>
                            </div>
                            <div>
                                <span class="font-semibold text-foreground">Idea:</span> 
                                <a href="<?= base_url('home/idea/' . $comment->ideaid) ?>" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">#<?= (int) $comment->ideaid ?></a>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="bg-muted/50 p-3 rounded text-foreground/90 font-mono text-xs whitespace-pre-wrap"><?= esc($comment->content) ?></div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="mb-3 text-destructive font-semibold">
                                Flagged <span class="inline-flex items-center rounded-full bg-destructive/20 px-2 py-0.5 text-xs ml-1"><?= (int) $comment->votes ?></span> times
                            </div>
                            <div class="flex items-center justify-end gap-2 flex-wrap">
                                <form method="post" action="<?= base_url('adminaction/deletecomment') ?>" onsubmit="return confirm('Are you sure you want to delete this comment?');">
                                    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $comment->id ?>">
                                    <button type="submit" class="inline-flex items-center justify-center rounded-md text-xs font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 px-3">
                                        Delete Comment
                                    </button>
                                </form>
                                <?php if (is_admin(2)): ?>
                                    <a href="<?= base_url('admin/users/' . $comment->userid) ?>" class="inline-flex items-center justify-center rounded-md text-xs font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-8 px-3">
                                        Ban User
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($flags)): ?>
                        <tr><td colspan="3" class="px-6 py-8 text-center text-muted-foreground">No flagged comments to review.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function showIdeasTab(panelId, btn) {
    document.querySelectorAll('.ideas-panel').forEach(p => {
        p.classList.remove('block');
        p.classList.add('hidden');
    });
    document.getElementById(panelId + '-panel').classList.remove('hidden');
    document.getElementById(panelId + '-panel').classList.add('block');
    
    document.querySelectorAll('.ideas-tab').forEach(t => {
        t.classList.remove('border-primary', 'text-primary', 'active');
        t.classList.add('border-transparent', 'text-muted-foreground');
    });
    btn.classList.remove('border-transparent', 'text-muted-foreground');
    btn.classList.add('border-primary', 'text-primary', 'active');
}

// Auto-switch to All Ideas if search was performed
<?php if (! empty($toall)): ?>
document.addEventListener('DOMContentLoaded', () => {
    const allIdeasBtn = document.querySelector('.ideas-tab:nth-child(2) button') || document.querySelectorAll('.ideas-tab')[1];
    if(allIdeasBtn) showIdeasTab('all-ideas', allIdeasBtn);
});
<?php endif; ?>
</script>

<?= $this->endSection() ?>
