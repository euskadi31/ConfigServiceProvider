<?php
/*
 * This file is part of the ConfigServiceProvider.
 *
 * (c) Axel Etcheverry <axel@etcheverry.biz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Euskadi31\Silex\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use RuntimeException;
use InvalidArgumentException;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

/**
 * Config integration for Silex.
 *
 * @author Axel Etcheverry <axel@etcheverry.biz>
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
    /**
     * @var SplFileInfo
     */
    private $filename;

    /**
     * @var array
     */
    private $replacements = [];

    /**
     *
     * @param string $filename
     * @param array  $replacements
     */
    public function __construct($filename, array $replacements = [])
    {
        if (empty($filename)) {
            throw new RuntimeException(
                'A valid configuration file must be passed before reading the config.'
            );
        }

        $this->filename = new SplFileInfo($filename);

        if (!empty($replacements)) {
            foreach ($replacements as $key => $value) {
                $this->replacements['%' . $key . '%'] = $value;
            }
        }
    }

    /**
     * @param  Pimple\Container $app
     * @return void
     */
    public function register(Container $app)
    {
        $config = $this->loadConfig();

        foreach ($config as $name => $value) {
            if ('%' === substr($name, 0, 1)) {
                $this->replacements[$name] = (string) $value;
            }
        }

        $this->merge($app, $config);
    }

    /**
     *
     * @param  Container $app
     * @param  array     $config
     * @return void
     */
    private function merge(Container $app, array $config)
    {
        foreach ($config as $name => $value) {
            if (isset($app[$name]) && is_array($value)) {
                $app[$name] = $this->mergeRecursively($app[$name], $value);
            } else {
                $app[$name] = $this->doReplacements($value);
            }
        }
    }

    /**
     * @param  array  $currentValue
     * @param  array  $newValue
     * @return array
     */
    private function mergeRecursively(array $currentValue, array $newValue)
    {
        foreach ($newValue as $name => $value) {
            if (is_array($value) && isset($currentValue[$name])) {
                $currentValue[$name] = $this->mergeRecursively($currentValue[$name], $value);
            } else {
                $currentValue[$name] = $this->doReplacements($value);
            }
        }

        return $currentValue;
    }

    /**
     * @param  mixed $value
     * @return mixed
     */
    private function doReplacements($value)
    {
        if (empty($this->replacements)) {
            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->doReplacements($v);
            }

            return $value;
        }

        if (is_string($value)) {
            return strtr($value, $this->replacements);
        }

        return $value;
    }

    /**
     * Load config file
     *
     * @return array
     */
    private function loadConfig()
    {
        if (!$this->filename->isFile()) {
            throw new InvalidArgumentException(sprintf(
                'The config file "%s" does not exist.',
                $this->filename
            ));
        }

        $config = Yaml::parse(file_get_contents($this->filename));

        if (!is_array($config)) {
            throw new InvalidArgumentException(sprintf(
                'The config file "%s" appears to have an invalid format.',
                $this->filename
            ));
        }

        return $config;
    }
}
