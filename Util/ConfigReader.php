<?php

/*
 * This file is part of the Allegro package.
 *
 * (c) Arturo Rodríguez <arturo@fugadigital.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Allegro\SitesBundle\Util;

use Symfony\Component\Yaml\Yaml;

class ConfigReader
{
    private static function getConfig()
    {
        $config = Yaml::parse(file_get_contents(__DIR__.'/../../../../app/config/config.yml'));

        return $config['allegro_sites'];
    }

    /**
     * Reads a value from the bundle configuration
     *
     * @param string* route strings a sequence of strings with the config path
     */
    public static function getValue()
    {
        $config = ConfigReader::getConfig();
        $args = func_get_args();

        foreach ($args as $arg) {
            $config = $config[$arg];
        }

        return $config;
    }

    public static function isAbsoluteUrl()
    {
        return 'absolute' === ConfigReader::getValue('format', 'urls');
    }
}
