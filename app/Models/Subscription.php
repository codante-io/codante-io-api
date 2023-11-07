<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function translatedStatus()
    {
        switch ($this->status) {
            case "pending":
                return "Pendente";
            case "active":
                return "Ativa";
            case "canceled":
                return "Cancelada";
            case "expired":
                return "Vencida";
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
        }
    }

    public function changeStatus(string $newStatus)
    {
        $this->status = $newStatus;
        $this->save();
    }
}
