<?php

namespace Filehosting;

/**
 * Application configuration file, can use default settings, or load an .ini file.
 *
 * @author foobar1643 <foobar76239@gmail.com>
 */
class Config
{
    /** @var int $appSizeLimit Uploaded file size limit in bytes. */
    private $appSizeLimit = 10485760;
    /** @var int $appEnableXsendfile An option to enable file downloads through X-Sendfile. */
    private $appEnableXsendfile = 0;

    /** @var string $dbHost Database IP address. */
    private $dbHost = "127.0.0.1";
    /** @var string $dbPort Database port. */
    private $dbPort = "5432";
    /** @var string $dbUsername Database user. */
    private $dbUsername = "root";
    /** @var string $dbPassword Database password. */
    private $dbPassword = "qwerty";
    /** @var string $dbName Database name. */
    private $dbName = "filehosting";

    /** @var string $sphinxHost Sphinx search engine IP address. */
    private $sphinxHost = "127.0.0.1";
    /** @var string $sphinxPort Sphinx search engine port. */
    private $sphinxPort = "9306";

    /**
     * Loads config from a .ini file.
     *
     * @param string $file A file to load.
     *
     * @throws InvalidArgumentException if value does not exists in a config class.
     *
     * @return void
     */
    public function loadFromFile($file)
    {
        $ini = parse_ini_file($file, true);
        foreach($ini as $section => $container) {
            foreach($container as $name => $value) {
                if(isset($this->{$section . ucfirst($name)})) {
                    $this->{$section . ucfirst($name)} = $value;
                } else {
                    throw new \InvalidArgumentException(_("Can't set value, no such value in the config (Section: $section; Key: $name)"));
                }
            }
        }
    }

    /**
     * Returns a value form a config.
     *
     * @param string $section Config section.
     * @param string $key Config value key.
     *
     * @throws InvalidArgumentException if value can't be found.
     *
     * @return string
     */
    public function getValue($section, $key)
    {
        if(isset($this->{$section . ucfirst($key)})) {
            return $this->{$section . ucfirst($key)};
        } else {
            throw new \InvalidArgumentException(_("Can't get value, no such value in the config (Section: $section; Key: $key)"));
        }
    }
}
