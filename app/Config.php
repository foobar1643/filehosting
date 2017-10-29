<?php

namespace Filehosting;

/**
 * Application configuration file, can use default settings, or load an .ini file.
 *
 * @package Filehosting
 * @author foobar1643 <foobar76239@gmail.com>
 */
class Config
{
    /**
     * @var int Uploaded file size limit in bytes.
     */
    private $appSizeLimit = 10485760;

    /**
     * @var int An option to enable file downloads through X-Sendfile.
     */
    private $appEnableXsendfile = false;

    /**
     * @var string Database IP address.
     */
    private $dbHost = "127.0.0.1";

    /**
     * @var int Database port.
     */
    private $dbPort = 5432;

    /**
     * @var string Database user
     */
    private $dbUsername = "root";

    /**
     * @var string Database password.
     */
    private $dbPassword = "qwerty";

    /**
     * @var string Database name.
     */
    private $dbName = "filehosting";

    /**
     * @var string Sphinx search IP address.
     */
    private $sphinxHost = "127.0.0.1";

    /**
     * @var int Sphinx search engine port.
     */
    private $sphinxPort = 9306;

    /**
     * Loads config from a .ini file.
     *
     * @param string $file A file to load.
     *
     * @throws \InvalidArgumentException if value does not exists in a config class.
     *
     * @return void
     */
    public function loadFromFile(string $file)
    {
        $ini = parse_ini_file($file, true);
        foreach ($ini as $section => $container) {
            foreach ($container as $name => $value) {
                if (isset($this->{$section . ucfirst($name)})) {
                    $this->{$section . ucfirst($name)} = $value;
                } else {
                    throw new \InvalidArgumentException(
                        _("Can't set value, no such value in the config (Section: $section; Key: $name)")
                    );
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
     * @throws \InvalidArgumentException if value can't be found.
     *
     * @return string
     */
    public function getValue(string $section, string $key)
    {
        if (isset($this->{$section . ucfirst($key)})) {
            return $this->{$section . ucfirst($key)};
        } else {
            throw new \InvalidArgumentException(
                _("Can't get value, no such value in the config (Section: $section; Key: $key)")
            );
        }
    }
}
