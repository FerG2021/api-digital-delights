<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
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
        'title',
        'description',
        'price',
        'available',
        'image',
    ];

    // public function
    public function getDataObject(): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'available' => $this->available,
            'image' => $this->image,
        ];
    }

}
