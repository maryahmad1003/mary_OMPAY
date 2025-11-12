<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OAuthClientSeeder extends Seeder
{
    public function run(): void
    {
        // Create a password grant client if passport migrations exist
        if (! DB::getSchemaBuilder()->hasTable('oauth_clients')) {
            return;
        }

        $secret = Str::random(40);
        $id = DB::table('oauth_clients')->insertGetId([
            'name' => 'Password Grant Client',
            'secret' => $secret,
            'redirect' => 'http://localhost',
            'personal_access_client' => 0,
            'password_client' => 1,
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a personal access client
        $personalSecret = Str::random(40);
        $personalId = DB::table('oauth_clients')->insertGetId([
            'name' => 'Laravel Personal Access Client',
            'secret' => $personalSecret,
            'redirect' => 'http://localhost',
            'personal_access_client' => 1,
            'password_client' => 0,
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('oauth_personal_access_clients')->insert([
            'client_id' => $personalId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Output the client id/secret for manual configuration if running interactively
        echo "Created password client id={$id} secret={$secret}\n";
        echo "Created personal access client id={$personalId}\n";
    }
}
