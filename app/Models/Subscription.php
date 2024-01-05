<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, CrudTrait, SoftDeletes;

    protected $guarded = ["id"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function translatedStatus()
    {
        switch ($this->status) {
            case "pending":
                return "Pendente";
            case "active":
                return "Ativa";
            case "canceled":
            case "refused":
                return "Cancelada";
            case "expired":
                return "Vencida";
            case "refunded":
                return "Reembolsada";
            default:
                return "Pendente";
        }
    }

    public function translatedPaymentMethod()
    {
        switch ($this->payment_method) {
            case "credit_card":
                return "Cartão de Crédito";
            case "boleto":
                return "Boleto Bancário";
            case "pix":
                return "Pix";
        }
    }

    public function changeStatus(string $newStatus)
    {
        if ($newStatus === "active") {
            $this->user->upgradeUserToPro();
        } else {
            $this->user->downgradeUserFromPro();
        }

        $this->status = $newStatus;
        $this->save();
    }
}
