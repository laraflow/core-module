<?php

namespace Laraflow\Core\Traits;

use ErrorException;
use Illuminate\Database\Eloquent\Model;

trait BlameableTrait
{
    /**
     * check if the trait required config file is present
     *
     * @throws ErrorException
     */
    public static function checkConfig()
    {
        if (is_null(config('blameable'))) {
            if (app()->environment('production')) {
                \Log::error('Blameable Config is missing. please import config or fix model namespace');
            } else {
                throw new ErrorException('Blameable Config is missing. please import config or fix model namespace');
            }
        }


    }

    /**
     * load this event listener to model
     *
     * @throws ErrorException
     */
    public static function bootBlamableTrait()
    {
        self::checkConfig();

        /**
         * Trigger Event and append creator id to model
         */
        static::creating(function (Model $model) {

            $modelCreatedByAttribute = config('blameable.createdBy', 'created_by');

            $blameable_id = (auth()->check())
                ? auth()->user()->id
                : config('blameable.user')::where('email', 'admin@admin.com')->first()->id;

            $model->$modelCreatedByAttribute = $blameable_id ?? null;

            $model->save();
        });

        /**
         * Trigger Event and append updater id to model
         */
        static::updating(function (Model $model) {

            $modelUpdatedByAttribute = config('blameable.updatedBy', 'created_by');

            $blameable_id = (auth()->check())
                ? auth()->user()->id
                : config('blameable.user')::where('email', 'admin@admin.com')->first()->id;

            $model->$modelUpdatedByAttribute = $blameable_id ?? null;

            $model->save();
        });

        /**
         * Trigger Event and append deleter id to model
         */
        static::deleting(function (Model $model) {

            $modelDeletedByAttribute = config('blameable.deletedBy', 'created_by');

            $blameable_id = (auth()->check())
                ? auth()->user()->id
                : config('blameable.user')::where('email', 'admin@admin.com')->first()->id;

            $model->$modelDeletedByAttribute = $blameable_id ?? null;

            $model->save();
        });
    }

    /**
     * relation of model is created by a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @throws ErrorException
     */
    public function creator()
    {
        return $this->belongsTo(
            config('blameable.user'),
            config('blameable.createdBy', 'created_by'),
            'id');
    }

    /**
     * if this model is updated by a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @throws ErrorException
     */
    public function editor()
    {
        return $this->belongsTo(
            config('blameable.user'),
            config('blameable.updatedBy', 'updated_by'),
            'id');
    }

    /**
     * if this model is deleted by a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @throws ErrorException
     */
    public function deletor()
    {
        return $this->belongsTo(
            config('blameable.user'),
            config('blameable.deletedBy', 'deleted_by'),
            'id');
    }

}
