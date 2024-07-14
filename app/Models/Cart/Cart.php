<?php

namespace App\Models\Cart;

use App\Models\Product\Bulk\BulkProduct;
use App\Models\Product\Contribution\ContributionProduct;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'bulk_product_id','contribution_product_id', 'quantity'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bulkProduct()
    {
        return $this->belongsTo(BulkProduct::class);
    }

    public function contributionProduct()
    {
        return $this->belongsTo(ContributionProduct::class);
    }
}
