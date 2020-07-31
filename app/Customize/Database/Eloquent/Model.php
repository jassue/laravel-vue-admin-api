<?php

namespace App\Customize\Database\Eloquent;

class Model extends \Illuminate\Database\Eloquent\Model
{
    protected $dateTimeFormatForArray = 'Y-m-d H:i:s';
    protected $dataFormatForArray = 'Y-m-d';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at'
    ];

    /**
     * Get the casts array.
     *
     * @return array
     */
    public function getCasts() {
        $casts = collect(parent::getCasts());
        $dates = collect(parent::getDates());
        $dates->map(
            function ($column) use ($casts) {
                if (!$casts->has($column)) {
                    $casts->put($column, 'datetime:'.$this->dateTimeFormatForArray);
                } else{
                    $value = $casts->get($column);
                    if ($value === 'date') {
                        $casts->put($column, $value.':'.$this->dataFormatForArray);
                    } elseif ($value === 'datetime') {
                        $casts->put($column, $value.':'.$this->dateTimeFormatForArray);
                    }
                }
            }
        );
        return $casts->all();
    }
}