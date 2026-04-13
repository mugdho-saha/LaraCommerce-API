<?php
namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // We omit product_id here, so it stays hidden
            'name' => $this->product_name,
            'slug' => $this->product_slug,
            'description' => $this->product_description,
            'price' => $this->product_price,
            'image_url' => $this->product_image 
            ? asset('storage/' . $this->product_image) 
            : null,
            'brand' => $this->product_brand,
            'status' => (bool) $this->product_status,
            'is_popular' => (bool) $this->popular,
            
            // Only sending the category name instead of the whole object
            'category_name' => $this->category ? $this->category->name : null,
        ];
    }
}