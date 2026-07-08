<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeds the default application settings. Idempotent: only inserts settings
 * whose `name` is not already present, so it is safe on upgrades.
 */
class SettingsSeeder extends Seeder
{
    public function run()
    {
        $defaults = [
            'title'                   => 'PHPBack',
            'language'                => 'english',
            'max_results'             => '10',
            'maxvotes'                => '20',
            'welcometext-title'       => 'Welcome to our feedback',
            'welcometext-description' => 'Share your ideas and vote for the ones you like the most.',
            'mainmail'                => 'noreply@example.com',
            'smtp-host'               => '',
            'smtp-port'               => '',
            'smtp-user'               => '',
            'smtp-pass'               => '',
            'recaptchapublic'         => '',
            'recaptchaprivate'        => '',
            'captcha_provider'        => 'recaptcha_v2',
            'google_client_id'        => '',
            'google_client_secret'    => '',
            'homepage_show_completed'  => '1',
            'homepage_show_started'    => '1',
            'homepage_show_planned'    => '1',
            'homepage_show_considered' => '1',
            'homepage_show_recent'     => '1',
        ];

        $table = $this->db->table('settings');

        foreach ($defaults as $name => $value) {
            $exists = $this->db->table('settings')->where('name', $name)->countAllResults() > 0;
            if (! $exists) {
                $table->insert(['name' => $name, 'value' => $value]);
            }
        }
    }
}
