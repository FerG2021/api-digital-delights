<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
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
        'date',
        'notification_type',
        'client_id',
        'event_date',
        'car_id'
    ];

    // public function
    public function getDataObject(): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'date' => $this->date,
            'notification_type' => $this->notification_type,
            'client_id' => $this->client_id,
            'event_date' => $this->event_date,
            'car_id' => $this->car_id
        ];
    }
}
