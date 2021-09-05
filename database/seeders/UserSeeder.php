<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    
    public function run()
    {
        User::create([
            'name' => 'Ignacio Montecinos Rodriguez',
            'email' => 'ignacio14montecinos@gmail.com',
            'password' => bcrypt('12345678')
        ])->assignRole('Admin');
        
        User::factory(9)->create();
    }
}
