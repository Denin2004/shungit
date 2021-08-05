<?php
namespace App\Entity;

use App\Entity\Entity;

class Batches extends Entity
{
    public function byDate($date)
    {
        return $this->provider->fetchAll(
            'select batches.batch
            from public.batches batches
            where batches.dt = :date',
            [
                'date' => $date
            ]
        );
    }

    public function create($params)
    {
        return $this->provider->  fetchAll(
            'insert into public.batches(dt, batch) values(:date, :batch)',
            $params
        );
    }
}
