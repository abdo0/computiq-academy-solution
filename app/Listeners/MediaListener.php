<?php

namespace App\Listeners;

use App\Models\BookingDoc;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class MediaListener
{
    /**
     * Handle the event.
     */
    public function handle(MediaHasBeenAddedEvent $event): void
    {
        $media = $event->media;

        if ($media->model_type === BookingDoc::class) {
            $bookingDoc = $media->model;
            if ($bookingDoc) {
                $bookingDoc->update(['name' => $media->name]);
            }
        }
    }
}
