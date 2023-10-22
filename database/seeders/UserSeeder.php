<?php
namespace Database\Seeders;

use App\Models\User\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'fname' => 'Super',
                'lname' => 'Admin',
                'display_name' => 'Super Admin',
                'contact_no' => '012544545',
                'email' => 'superadmin@gmail.com',
                'email_verified_at'=>'2020-06-18 19:43:54.000000',
                'password' => Hash::make('super_admin'),
                'user_level_id' => \App\Models\User\UserLevel::where('scope','super_admin')->first()->id
            ]

        ]);

        $user = User::where('email', 'superadmin@gmail.com')->first();
        $user->assignRole('super_admin');
        $user->save();
    }
}
