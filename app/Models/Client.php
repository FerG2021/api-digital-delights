<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
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
        'dni',
        'name',
        'lastname',
        'birthday',
        'phone_number',
        'address',
    ];

    // public function
    public function getDataObject(): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'dni' => $this->dni,
            'name' => $this->name,
            'lastname' => $this->lastname,
            'fullname' => $this->lastname . ', ' . $this->name,
            'birthday' => $this->birthday,
            'phone_number' => $this->phone_number,
            'address' => $this->address,
        ];
    }
}
