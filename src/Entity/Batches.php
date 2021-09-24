<?php
namespace App\Entity;

use App\Entity\Entity;

class Batches extends Entity
{
    public function find($params)
    {
        return $this->provider->fetchAll(
            'select batches.batch
            from public.batches batches
            where (batches.dt = :dt)and(mail_type=:mail_type)and(postoffice_code=:postoffice_code)',
            $params
        );
    }

    public function create($params)
    {
        return $this->provider->fetchAll(
            'insert into public.batches(dt, batch, mail_type, postoffice_code) values(:dt, :batch, :mail_type, :postoffice_code)',
            $params
        );
    }

    public function delete($params)
    {
        return $this->provider->fetchAll(
            'delete from public.batches where batch=:batch',
            $params
        );
    }
}
