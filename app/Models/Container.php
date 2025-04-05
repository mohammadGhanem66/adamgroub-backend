<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Container extends Model
{
    //
    use HasFactory;

    protected $table = 'containers';
    protected $fillable =[
        'file_name',
        'file_path',
        'user_id',
        'type',
        'tracking_number',
        'is_delevired',
        'delivery_date'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    protected $appends = ['public_url'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getPublicUrlAttribute()
    {
        return url('storage/' . $this->file_path);
    }
}
