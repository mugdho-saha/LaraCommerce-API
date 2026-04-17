<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    use HasFactory;

    // Define the table name if it's not the plural of the model name
    // Since our table is 'carts', Laravel handles this automatically, 
    // but it's good practice to be explicit if using custom IDs.
    protected $table = 'carts';

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
    ];

    /**
     * Relationship: A cart item belongs to a User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: A cart item belongs to a Product.
     * Note: We specify 'product_id' as the foreign key because 
     * your products table uses 'product_id' instead of 'id'.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}