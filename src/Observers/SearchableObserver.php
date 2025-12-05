<?php

namespace PlusInfoLab\Search\Observers;

use Illuminate\Database\Eloquent\Model;

class SearchableObserver
{
    /**
     * Handle the model "created" event.
     */
    public function created(Model $model): void
    {
        if (method_exists($model, 'searchable')) {
            $model->searchable();
        }
    }

    /**
     * Handle the model "updated" event.
     */
    public function updated(Model $model): void
    {
        if (method_exists($model, 'searchable')) {
            $model->searchable();
        }
    }

    /**
     * Handle the model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        if (method_exists($model, 'unsearchable')) {
            $model->unsearchable();
        }
    }

    /**
     * Handle the model "restored" event.
     */
    public function restored(Model $model): void
    {
        if (method_exists($model, 'searchable')) {
            $model->searchable();
        }
    }

    /**
     * Observe a model for searchable events.
     */
    public static function observe($model): void
    {
        $model::observe(new static);
    }
}
