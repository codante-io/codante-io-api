<?php

namespace App\Models;

use App\Notifications\Discord;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use CrudTrait;
    use HasFactory;

    public $incrementing = false; // desativa increment pois o id é uuid
    protected $keyType = 'string';

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    protected static function booted()
    {
        static::updated(function ($certificate) {
            if ($certificate->source_type === "challenge") {
                $user = User::find($certificate->user_id);
                $challenge = Challenge::find($certificate->challenge_id);
                if ($certificate->status === "published") {
                    new Discord(
                        "✅✅✅ Certificado atualizado para {$user->name} - {$challenge->name}. Status atual: {$certificate->status}",
                        "pedidos-certificados",
                    );
                } elseif ($certificate->status === "pending") {
                    new Discord(
                        "❌❌❌ Certificado atualizado para {$user->name} - {$challenge->name}. Status atual: {$certificate->status}",
                        "pedidos-certificados",
                    );
                }
            }
        });
    }

    protected $guarded = ["id"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    public function challenge_user()
    {
        return $this->belongsTo(ChallengeUser::class);
    }
    protected $casts = [
        'metadata' => 'array',
    ];
}
