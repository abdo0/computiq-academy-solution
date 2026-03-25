<?php

namespace App\Observers;

use App\Enums\PropertyType;
use App\Enums\PropertyUnitType;
use App\Models\Property;

class PropertyObserver
{
    /**
     * Handle the Property "created" event.
     */
    public function created(Property $property): void
    {
        //
    }

    /**
     * Handle the Property "updated" event.
     */
    public function updated(Property $property): void
    {
        // Check if the type has changed
        if ($property->wasChanged('type')) {
            $this->handleTypeChange($property);
        }
    }

    /**
     * Handle type change and clean up related data
     */
    private function handleTypeChange(Property $property): void
    {
        if ($property->type === PropertyType::BUILDING) {
            // If changed to building, remove all houses (units with unit_type = 'house')
            $property->houses()->delete();
        } elseif ($property->type === PropertyType::SECTOR) {
            // If changed to sector, remove all floors and apartments
            $property->floors()->delete();
            $property->apartments()->delete();
        }
    }

    /**
     * Handle the Property "deleted" event.
     */
    public function deleted(Property $property): void
    {
        //
    }

    /**
     * Handle the Property "restored" event.
     */
    public function restored(Property $property): void
    {
        //
    }

    /**
     * Handle the Property "force deleted" event.
     */
    public function forceDeleted(Property $property): void
    {
        //
    }
}
