<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permissions')->insert([

            [ 'name'=>'role super_admin', 'guard_name'=>'web'],
            [ 'name'=>'role customer', 'guard_name'=>'web'],

        ]);
    }
}
