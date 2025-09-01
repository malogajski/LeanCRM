<?php

namespace App\Listeners;

use App\Events\DealStageChanged;
use App\Jobs\SendDealStageChangeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendDealStageNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(DealStageChanged $event): void
    {
        SendDealStageChangeNotification::dispatch(
            $event->deal,
            $event->oldStage,
            $event->newStage
        );
    }
}
