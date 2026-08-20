<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'STRAVANTA Admin',
            'email' => 'v.s.anandapiya@gmail.com',
            'password' => bcrypt('$$v.s.anandapiya@gmail.com##123'),
        ]);

        $this->call(PostSeeder::class);
    }
}
