<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGoogleOauthSettings extends Migration
{
    private const SETTINGS = ['google_client_id', 'google_client_secret'];

    public function up()
    {
        $db = \Config\Database::connect();

        // Ensure settings table exists and we don't insert duplicates
        if ($db->tableExists('settings')) {
            foreach (self::SETTINGS as $name) {
                $exists = $db->table('settings')->where('name', $name)->countAllResults() > 0;
                if (! $exists) {
                    $db->table('settings')->insert([
                        'name'  => $name,
                        'value' => '',
                    ]);
                }
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('settings')) {
            $db->table('settings')->whereIn('name', self::SETTINGS)->delete();
        }
    }
}
