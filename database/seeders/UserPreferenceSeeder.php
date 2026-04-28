<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserPreferenceSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->pluck('id');

        $defaults = [
            ['key' => 'theme',         'value' => 'light'],
            ['key' => 'notifications', 'value' => '1'],
            ['key' => 'language',      'value' => 'pt'],
        ];

        foreach ($users as $userId) {
            foreach ($defaults as $pref) {
                $exists = DB::table('user_preferences')
                    ->where('user_id', $userId)
                    ->where('key', $pref['key'])
                    ->exists();

                if (!$exists) {
                    DB::table('user_preferences')->insert([
                        'id'         => Str::uuid(),
                        'user_id'    => $userId,
                        'key'        => $pref['key'],
                        'value'      => $pref['value'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
