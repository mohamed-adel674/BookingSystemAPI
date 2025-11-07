<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// App/Models/Availability.php

class Availability extends Model
{
    use HasFactory;
    
    protected $fillable = ['resource_id', 'day_of_week', 'start_time', 'end_time', 'date_from', 'date_to'];

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }
}
