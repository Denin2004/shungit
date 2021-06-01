<?php
namespace App\Entity;

use App\Entity\Entity;

class Players extends Entity
{
    public function players()
    {
        return $this->provider->fetchAll(
            'select players.id, players.name, players.phone
            from players.players players
            where players.del = false
            order by players.name'
        );
    }

    public function post($params)
    {
        if ($params['id'] == -1) {
            return $this->provider->fetchAll('insert into players.players (name, phone)values(:name, :phone) returning id, name, phone', $params);
        } else {
            $this->provider->executeQuery('update players.players set name=:name, phone=:phone where id=:id', $params);
        }
    }

    public function player($params)
    {
        $res = $this->provider->fetchAll(
            'select players.id, players.name, players.phone
            from players.players players where players.id=:id',
            $params
        );
        return isset($res[0]) ? $res[0] : false;
    }

    public function delete($params)
    {
        $this->provider->executeQuery('update players.players set id=-1, del=true where id=:id', $params);
    }

    public function search($name)
    {
        return $this->provider->fetchAll(
            'select players.id, players.name as value, players.name as label
                from players.players players
                   where (upper(players.name) like upper(:f))',
            [
                'f' => '%'.$name.'%'
            ]
        );
    }
}
