<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class ConfigReader
{
    private $config = array();

    public function __construct($iniFile ='mysql.ini')
    {
        $this->config = parse_ini_file($iniFile, TRUE);
    }

    public function getSection($sectionName)
    {
        if (isset($this->config[$sectionName]))
            return $this->config[$sectionName];
        else
            return null;
    }

    public function getSectionKey($sectionName, $keyName)
    {
        if (isset($this->config[$sectionName][$keyName]))
        {
            return $this->config[$sectionName][$keyName];
        }
        else
        {
            return null;
        }
    }

    public function getConfigs()
    {
        return $this->config;
    }
}

?>
