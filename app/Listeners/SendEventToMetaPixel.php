<?php

namespace App\Listeners;

use App\Events\LeadRegistered;
use App\Events\PurchaseCompleted;
use App\Events\PurchaseStarted;
use Combindma\FacebookPixel\Facades\MetaPixel;
use FacebookAds\Object\ServerSide\Content;
use FacebookAds\Object\ServerSide\CustomData;
use Illuminate\Auth\Events\Registered;

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
        if (
            $event instanceof PurchaseCompleted ||
            $event instanceof PurchaseStarted
        ) {
            $this->sendPurchaseEvent($event);
        } elseif ($event instanceof LeadRegistered) {
            $this->sendRegisteredLeadEvent($event);
        } elseif ($event instanceof Registered) {
            $this->sendRegisteredUserEvent($event);
        }
    }

    private function sendRegisteredLeadEvent($event)
    {
        $fbp = request()->cookie('_fbp');
        $fbc = request()->cookie('_fbc');

        $userData = MetaPixel::userData()
            ->setEmail($event->email)
            ->setFbp($fbp, '_fbp')
            ->setFbc($fbc, '_fbc');
        $eventId = uniqid('prefix_');
        $customData = new CustomData();

        MetaPixel::send('Lead', $eventId, $customData, $userData);
    }

    private function sendRegisteredUserEvent($event)
    {
        $fbp = request()->cookie('_fbp');
        $fbc = request()->cookie('_fbc');

        $userData = MetaPixel::userData()
            ->setEmail($event->user->email)
            ->setFbp($fbp, '_fbp')
            ->setFbc($fbc, '_fbc');
        $eventId = uniqid('prefix_');
        $customData = new CustomData();

        MetaPixel::send(
            'CompleteRegistration',
            $eventId,
            $customData,
            $userData
        );
    }

    private function sendPurchaseEvent($event)
    {
        $fbp = request()->cookie('_fbp');
        $fbc = request()->cookie('_fbc');

        $userData = MetaPixel::userData()
            ->setEmail($event->user->email)
            ->setFbp($fbp, '_fbp')
            ->setFbc($fbc, '_fbc');

        $content = (new Content())
            ->setProductId($event->subscription->id)
            ->setItemPrice($event->subscription->price_paid_in_cents / 100)
            ->setQuantity(1);

        $customData = (new CustomData())
            ->setContents([$content])
            ->setCurrency('brl')
            ->setValue($event->subscription->price_paid_in_cents / 100);

        $eventId = uniqid('prefix_');

        $eventType =
            $event instanceof PurchaseCompleted
                ? 'Purchase'
                : 'InitiateCheckout';

        MetaPixel::send($eventType, $eventId, $customData, $userData);
    }
}
