<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $users = User::factory()->times(50)->make([
            User::CREATED_AT => now(),
            User::UPDATED_AT => now()
        ]);

        $res = User::insert($users->makeVisible(['password', 'remember_token'])->toArray());

        $user = User::find(1);
        $user->name = 'Summer';
        $user->email = '15916965182@163.com';
        $user->password = bcrypt('DGDzdx258...');
        $user->is_admin = true;
        $user->save();
    }
}
