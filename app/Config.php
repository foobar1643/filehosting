<?php

namespace Filehosting;

class Config
{
    /* Default settings */
    private $appSizeLimit = 10;
    private $appEnableXsendfile = 0;

    private $dbHost = "127.0.0.1";
    private $dbPort = "5432";
    private $dbUsername = "root";
    private $dbPassword = "qwerty";
    private $dbName = "filehosting";

    private $sphinxHost = "127.0.0.1";
    private $sphinxPort = "9306";

    public function loadFromFile($file)
    {
        $ini = parse_ini_file($file, true);
        foreach($ini as $section => $container) {
            foreach($container as $name => $value) {
                if(isset($this->{$section . ucfirst($name)})) {
                    $this->{$section . ucfirst($name)} = $value;
                } else {
                    throw new \Exception(_("Can't set value, no such value in the config (Section: $section; Key: $name)"));
                }
            }
        }
    }

    public function getValue($section, $key)
    {
        if(isset($this->{$section . ucfirst($key)})) {
            return $this->{$section . ucfirst($key)};
        } else {
            throw new \InvalidArgumentException(_("Can't get value, no such value in the config (Section: $section; Key: $key)"));
        }
    }
}
