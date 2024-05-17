<?php

namespace App\Listeners;

use App\Events\PurchaseCompleted;
use Combindma\FacebookPixel\Facades\MetaPixel;
use FacebookAds\Object\ServerSide\Content;
use FacebookAds\Object\ServerSide\CustomData;

class SendEventToMetaPixel
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        $userData = MetaPixel::userData()->setEmail($event->user->email);

        $content = (new Content())
            ->setProductId($event->subscription->id)
            ->setItemPrice($event->subscription->price_paid_in_cents)
            ->setQuantity(1);

        $customData = (new CustomData())
            ->setContents([$content])
            ->setCurrency("brl")
            ->setValue($event->subscription->price_paid_in_cents);

        $eventId = uniqid("prefix_");

        $eventType =
            $event instanceof PurchaseCompleted
                ? "Purchase"
                : "InitiateCheckout";

        MetaPixel::send($eventType, $eventId, $customData, $userData);
    }
}
