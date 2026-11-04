<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 
        'name', 
        'slug', 
        'unit', 
        'purchase_price', 
        'selling_price', 
        'stock', 
        'is_available', 
        'image'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}