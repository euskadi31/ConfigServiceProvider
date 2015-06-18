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

use Euskadi31\Silex\Provider\ConfigServiceProvider;
use Silex\Application;

class ConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     */
    public function testWithoutConfig()
    {
        $app = new Application;

        $app->register(new ConfigServiceProvider(''));
    }

    public function testConfig()
    {
        $app = new Application;

        $app->register(new ConfigServiceProvider(__DIR__ . '/../_files/config.yml'));

        $this->assertEquals('apc', $app['cache.options']['default']['driver']);
    }

    public function testConfigWithoutRemplacements()
    {
        $app = new Application;

        $app->register(new ConfigServiceProvider(__DIR__ . '/../_files/config_without_replacements.yml'));

        $this->assertEquals('apc', $app['cache.options']['default']['driver']);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConfigNotFound()
    {
        $app = new Application;

        $app->register(new ConfigServiceProvider(__DIR__ . '/../_files/config_not_found.yml'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testBadConfigFormat()
    {
        $app = new Application;

        $app->register(new ConfigServiceProvider(__DIR__ . '/../_files/config.xml'));
    }

    public function testConfigWithReplacements()
    {
        $app = new Application;

        $app->register(new ConfigServiceProvider(__DIR__ . '/../_files/config.yml', [
            'root_path' => __DIR__,
            'life_time' => 3600
        ]));

        $this->assertEquals(3600, $app['cache.options']['default']['lifetime']);

        $this->assertEquals(__DIR__ . '/cache', $app['cache.options']['local']['path']);
    }

    public function testConfigMerge()
    {
        $app = new Application;
        $app['redis.options'] = [
            'server' => [
                'host' => '10.0.0.4',
                'port' => 1337
            ]
        ];

        $app->register(new ConfigServiceProvider(__DIR__ . '/../_files/config.yml', [
            'root_path' => __DIR__,
            'life_time' => 3600
        ]));

        $this->assertEquals('127.0.0.1', $app['redis.options']['server']['host']);
        $this->assertEquals(1337, $app['redis.options']['server']['port']);
    }
}
