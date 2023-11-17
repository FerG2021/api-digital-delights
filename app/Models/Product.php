<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'account_id',
        'category_id',
        'name',
        'description',
        'price',
        'stock',
        'uuid',
        'image',
    ];

    // public function
    public function getDataObject(): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->name,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'uuid' => $this->uuid,
            'image' => $this->image,
        ];
    }
}
