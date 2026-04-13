<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'product_id';
    protected $fillable = ['product_name', 'product_slug', 'product_description', 'product_price', 'product_image', 'category_id', 'product_brand', 'product_status', 'popular', 'created_at', 'updated_at'];
    

    /**
     * Interact with the product name.
     */
    protected function productName(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => [
                'product_name' => $value,
                'product_slug' => Str::slug($value), // Logic moved here!
            ],
        );
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'product_slug';
    }
    
    
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
