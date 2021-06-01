<?php
namespace App\Entity;

use App\Services\DbProvider;

class Entity
{
    protected $provider;

    public function __construct(DbProvider $provider)
    {
        $this->provider = $provider;
    }

    public function isError()
    {
        return $this->provider->isError();
    }

    public function getError()
    {
        return $this->provider->getError();
    }
}
