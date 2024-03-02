<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Car extends Model
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
        'patent',
        'mark_id',
        'category_id',
        'name',
        'description',
        'year',
        'kilometres',
        'condition',
        'fuel',
        'trunk_space',
        'tank_space',
        'weight',
        'image',
        'buyer_id',
        'buy_date',
    ];

    // public function
    public function getDataObject(): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'patent' => $this->patent,
            'mark_id' => $this->mark_id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
            'year' => $this->year,
            'kilometres' => $this->kilometres,
            'condition' => $this->condition,
            'fuel' => $this->fuel,
            'trunk_space' => $this->trunk_space,
            'tank_space' => $this->tank_space,
            'weight' => $this->weight,
            'image' => env('IMAGE_URL') . $this->image,
            'buyer_id' => $this->buyer_id,
            'buy_date' => $this->buy_date,
            'monthly_fee_paid' => $this->monthly_fee_paid,
            'expiration_day' => $this->expiration_day,
        ];
    }
}
