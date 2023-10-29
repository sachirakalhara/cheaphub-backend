<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        for ($i = 1; $i <= 12; $i++) {
            $timestamp = mktime(0, 0, 0, $i, 1);
            $monthName = date("F", $timestamp);
            DB::table('subscriptions')->insert(
                [ 'month_number'=>$i, 'month_name'=>$monthName]
            );
        }

//        DB::table('subscriptions')->insert([
//
//            [ 'month_number'=>1, 'month_name'=>'web'],
//            [ 'month_number'=>2, 'month_name'=>'web'],
//            [ 'month_number'=>3, 'month_name'=>'web'],
//            [ 'month_number'=>4, 'month_name'=>'web'],
//            [ 'month_number'=>5, 'month_name'=>'web'],
//            [ 'month_number'=>6, 'month_name'=>'web'],
//            [ 'month_number'=>7, 'month_name'=>'web'],
//            [ 'month_number'=>8, 'month_name'=>'web'],
//            [ 'month_number'=>9, 'month_name'=>'web'],
//            [ 'month_number'=>10, 'month_name'=>'web'],
//            [ 'month_number'=>11, 'month_name'=>'web'],
//            [ 'month_number'=>12, 'month_name'=>'web'],
//
//        ]);
    }
}
