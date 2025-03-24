<?php

namespace App\Console\Commands;

use App\Models\Coupon\Coupon;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteExpiredCoupons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-expired-coupons';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deleted = Coupon::where('expiry_date', '<', Carbon::now())->delete();

        $this->info("$deleted expired coupon(s) have been deleted.");
        return 0;
    }
}
