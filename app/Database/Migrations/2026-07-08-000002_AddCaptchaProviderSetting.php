<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCaptchaProviderSetting extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // Ensure settings table exists and we don't insert duplicates
        if ($db->tableExists('settings')) {
            $exists = $db->table('settings')->where('name', 'captcha_provider')->countAllResults() > 0;
            if (! $exists) {
                $db->table('settings')->insert([
                    'name'  => 'captcha_provider',
                    'value' => 'recaptcha_v2'
                ]);
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('settings')) {
            $db->table('settings')->where('name', 'captcha_provider')->delete();
        }
    }
}
