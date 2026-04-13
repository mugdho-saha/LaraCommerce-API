<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id('product_id');
            $table->string('product_name');
            $table->string('product_slug')->unique(); // Added unique for better SEO/Logic
            $table->text('product_description'); // text for longer descriptions
            $table->decimal('product_price', 10, 2); // Use decimal for currency
            $table->string('product_image')->nullable();
            $table->string('product_brand')->nullable();
            $table->boolean('product_status')->default(true);
            $table->boolean('popular')->default(false);
            $table->foreignId('category_id')->constrained('categories', 'category_id')->onDelete('cascade');
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
