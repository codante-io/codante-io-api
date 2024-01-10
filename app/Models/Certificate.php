<?php

namespace App\Models;

use App\Notifications\Discord;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Certificate extends Model
{
    use CrudTrait;
    use HasFactory;

    public $incrementing = false; // desativa increment pois o id é uuid
    protected $keyType = "string";

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            do {
                $uuid = (string) Str::random(8);
            } while (self::where("id", $uuid)->exists());

            $model->id = $uuid;
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
                        "pedidos-certificados"
                    );
                } elseif ($certificate->status === "pending") {
                    new Discord(
                        "❌❌❌ Certificado atualizado para {$user->name} - {$challenge->name}. Status atual: {$certificate->status}",
                        "pedidos-certificados"
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

    protected $casts = [
        // usado para o laravel converter json corretamente
        "metadata" => "array",
    ];

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, "taggable");
    }

    public function certifiable()
    {
        return $this->morphTo();
    }

    public static function validateCertifiable($certifiableType)
    {
        $certifiableClass = "App\\Models\\" . $certifiableType;

        // check if certifiable model exists, if not, return error
        if (!class_exists($certifiableClass)) {
            return response()->json(
                [
                    "message" => "Reactable model does not exist",
                ],
                404
            );
        }

        return $certifiableClass;
    }
}
