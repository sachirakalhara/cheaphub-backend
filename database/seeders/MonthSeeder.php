<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MonthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        for ($i = 1; $i <= 12; $i++) {
            // $timestamp = mktime(0, 0, 0, $i, 1);
            // $monthName = date("F", $timestamp);
            DB::table('months')->insert(
                [ 'month_number'=>$i, 'month_name'=>$i.' Month']
            );
        }
    }
}
