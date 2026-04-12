<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Category extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'category_id';
    protected $table = 'categories';
    protected $fillable = ['name','name_slug', 'description', 'created_at', 'updated_at'];
    protected $hidden = ['created_at', 'updated_at'];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    /**
     * Interact with the category name.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => [
                'name' => $value,
                'name_slug' => Str::slug($value), // Logic moved here!
            ],
        );
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'name_slug';
    }
}
