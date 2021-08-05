<?php
namespace App\Services;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\DBAL\Driver\Connection;

use App\Services\SiteConfig;

class DbProvider
{
    protected $db;
    protected $user;
    protected $dateFormat;
    protected $timeFormat;
    protected $dateTimeFormat;
    protected $token;
    protected $error;

    public function __construct(Connection $db, TokenStorageInterface $token, SiteConfig $siteConfig)
    {
        $this->db = $db;
        $this->token = $token;
        $this->dateFormat = $siteConfig->get('db_date_format');
        $this->timeFormat = $siteConfig->get('db_time_format');
        $this->error = '';
    }

    public function db()
    {
        return $this->db;
    }

    public function user()
    {
        return $this->token->getToken()->getUser();
    }

    public function dateFormat()
    {
        return $this->dateFormat;
    }

    public function dateTimeFormat()
    {
        return $this->dateFormat.' '.$this->timeFormat;
    }

    public function timeFormat()
    {
        return $this->timeFormat;
    }

    public function executeQuery($sql, $params = [])
    {
        $this->error = '';
        try {
            $this->db->executeQuery($sql, $params);
        } catch (\Doctrine\DBAL\DBALException $ex) {
            $err = explode('ERROR:', $ex->getPrevious()->getMessage());
            $this->error = str_replace(' ', '', explode("\n", $err[count($err)-1])[0]);
        }
    }

    public function fetchAll($sql, $params = [])
    {
        $this->error = '';
        try {
            $res = $this->db->fetchAll($sql, $params);
        } catch (\Doctrine\DBAL\DBALException $ex) {
            $err = explode('ERROR:', $ex->getPrevious()->getMessage());
            $this->error = str_replace(' ', '', explode("\n", $err[count($err)-1])[0]);
            return;
        }
        return $res;
    }

    public function isError()
    {
        return $this->error != '';
    }

    public function getError()
    {
        return $this->error;
    }
}
