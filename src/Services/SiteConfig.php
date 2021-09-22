<?php
namespace App\Services;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SiteConfig
{
    protected $config = [];

    public function __construct($projectDir, TokenStorageInterface $tokenStorage)
    {
        $this->config = json_decode(file_get_contents($projectDir.'/templates/site_config.json.twig'), true);
        $token = $tokenStorage->getToken();
        $userName = $token != null ? $token->getUser()->getUsername() : '';
        if ($userName != '') {
            $this->config = array_merge($this->config, $this->config['users'][$userName]);
            unset($this->config['users']);
        }
    }

    public function get($name)
    {
        return isset($this->config[$name]) ? $this->config[$name] : '';
    }
}
