<?php
/**
 * Created by PhpStorm.
 * User: dave
 * Date: 05/04/2017
 * Time: 19:46
 */

namespace davebarnwell\Model;


use Symfony\Component\Yaml\Yaml;

class SettingsModel extends Model
{
    private $rootDir;
    private $settings;

    public function __construct($file = 'settings.yml')
    {
        $this->rootDir  = dirname(dirname(__DIR__));
        $this->settings = Yaml::parse(file_get_contents($this->rootDir . '/' . $file));
    }

    public function getSetting(string $name) {
        if (!isset($this->settings[$name])) {
            throw new MissingSettingException("No setting of name[$name]");
        }
        return $this->settings[$name];
    }

    public function getDirectorySetting(string $name) : string {
        return $this->rootDir.$this->getSetting($name);
    }
}