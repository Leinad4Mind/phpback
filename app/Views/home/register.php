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
                <span class="ml-1 font-medium text-foreground md:ml-2"><?= esc($lang['label_registration']) ?></span>
            </div>
        </li>
    </ol>
</nav>

<div class="max-w-xl mx-auto">
    <div class="bg-card text-card-foreground rounded-lg border shadow-sm p-6 mb-8">
        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold tracking-tight"><?= esc($lang['label_registration_form']) ?></h2>
            <p class="text-sm text-muted-foreground mt-2"><?= esc($lang['text_register_cta']) ?></p>
        </div>

        <?php
            $errors = [
                'recaptcha' => 'error_recaptcha',
                'name'      => 'error_name',
                'email'     => 'error_email',
                'pass'      => 'error_password',
                'pass2'     => 'error_passwords',
                'exists'    => 'error_email_exists',
                'toomany'   => 'error_toomany',
            ];
        ?>
        <?php if (isset($errors[$error])): ?>
            <div class="bg-destructive/10 text-destructive border border-destructive/20 p-3 rounded-md mb-6 text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <?= esc($lang[$errors[$error]]) ?>
            </div>
        <?php endif; ?>

        <form name="registration-form" action="<?= base_url('action/register') ?>" method="POST" onsubmit="return validateForm()" class="space-y-4">
            <?= csrf_field() ?>
            
            <div class="space-y-2">
                <label for="InputEmail" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"><?= esc($lang['form_email']) ?></label>
                <input type="email" id="InputEmail" name="email" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="<?= esc($lang['form_email'], 'attr') ?>" required>
            </div>
            
            <div class="space-y-2">
                <label for="InputName" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"><?= esc($lang['form_full_name']) ?></label>
                <input type="text" id="InputName" name="name" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="<?= esc($lang['form_full_name'], 'attr') ?>" required>
            </div>
            
            <div class="space-y-2">
                <label for="InputPassword" class="text-sm font-medium leading-none flex items-center justify-between">
                    <?= esc($lang['form_password']) ?>
                    <span id="password-error-show" class="text-xs text-destructive"></span>
                </label>
                <input type="password" id="InputPassword" name="password" minlength="6" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="<?= esc($lang['form_password'], 'attr') ?>" required>
            </div>
            
            <div class="space-y-2">
                <label for="InputPassword2" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"><?= esc($lang['form_repeat_password']) ?></label>
                <input type="password" id="InputPassword2" name="password2" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="<?= esc($lang['form_repeat_password'], 'attr') ?>" required>
            </div>
            
            <?php if ($recaptchapublic !== ''): ?>
                <div class="pt-2 flex justify-center">
                    <?php if ($captcha_provider === 'turnstile'): ?>
                        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
                        <div class="cf-turnstile" data-sitekey="<?= esc($recaptchapublic, 'attr') ?>"></div>
                    <?php elseif ($captcha_provider === 'recaptcha_v3'): ?>
                        <script src="https://www.google.com/recaptcha/api.js?render=<?= esc($recaptchapublic, 'attr') ?>"></script>
                        <script>
                            document.querySelector('form').addEventListener('submit', function(e) {
                                e.preventDefault();
                                grecaptcha.ready(function() {
                                    grecaptcha.execute('<?= esc($recaptchapublic, 'attr') ?>', {action: 'submit'}).then(function(token) {
                                        let input = document.createElement('input');
                                        input.type = 'hidden';
                                        input.name = 'g-recaptcha-response';
                                        input.value = token;
                                        e.target.appendChild(input);
                                        e.target.submit();
                                    });
                                });
                            });
                        </script>
                    <?php elseif ($captcha_provider === 'recaptcha_invisible'): ?>
                        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                        <div class="g-recaptcha" data-sitekey="<?= esc($recaptchapublic, 'attr') ?>" data-size="invisible" data-callback="onSubmitRecaptchaInvisible"></div>
                        <script>
                            document.querySelector('form').addEventListener('submit', function(e) {
                                if (!document.querySelector('[name="g-recaptcha-response"]').value) {
                                    e.preventDefault();
                                    grecaptcha.execute();
                                }
                            });
                            function onSubmitRecaptchaInvisible(token) {
                                document.querySelector('form').submit();
                            }
                        </script>
                    <?php else: ?>
                        <!-- reCAPTCHA v2 (default) -->
                        <script type="text/javascript" src="https://www.google.com/recaptcha/api.js" async defer></script>
                        <div class="g-recaptcha" data-sitekey="<?= esc($recaptchapublic, 'attr') ?>"></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <button type="submit" class="inline-flex w-full mt-4 items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                <?= esc($lang['label_registration']) ?>
            </button>
        </form>
    </div>
</div>

<script>
function validateForm() {
    var pass = document.forms["registration-form"]["password"].value;
    var passVerify = document.forms["registration-form"]["password2"].value;
    var errorShowDiv = document.getElementById("password-error-show");
    var passMatchError = "<?= esc($lang['error_passwords'], 'js') ?>";
    
    if (pass != passVerify) {
        errorShowDiv.innerHTML = passMatchError;
        document.forms["registration-form"]["password2"].focus();
        return false;
    }
    
    errorShowDiv.innerHTML = "";
    return true;
}
</script>

<?= $this->endSection() ?>
