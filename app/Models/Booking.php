<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    
    // تأكد من إضافة الأعمدة التي يمكن ملؤها
    protected $fillable = [
        'user_id', 
        'resource_id', 
        'start_time', 
        'end_time', 
        'status'
    ];

    /**
     * التحويل التلقائي لأعمدة قاعدة البيانات إلى أنواع PHP محددة.
     * نستخدم 'datetime' لتحويلها إلى كائنات Carbon تلقائياً.
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    // علاقة الحجز بالمستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // علاقة الحجز بالموارد
    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }
}