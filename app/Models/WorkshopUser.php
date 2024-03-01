<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkshopUser extends Model
{
    use HasFactory;

    protected $table = "workshop_user";
    protected $fillable = ["status", "completed_at"];
    // protected $dates = ["completed_at"];

    function User()
    {
        return $this->belongsTo(User::class);
    }

    function Workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    function Certificate()
    {
        return $this->morphOne(Certificate::class, "certifiable");
    }
}
