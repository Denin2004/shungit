<?php
namespace App\Services;

class SiteConfig
{
    protected $config = [];

    public function __construct($projectDir)
    {
        $this->config = json_decode(file_get_contents($projectDir.'/templates/site_config.json.twig'), true);
    }

    public function get($name)
    {
        return isset($this->config[$name]) ? $this->config[$name] : '';
    }
}
