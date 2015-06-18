# Silex Config Service Provider

[![Build Status](https://travis-ci.org/euskadi31/ConfigServiceProvider.svg?branch=master)](https://travis-ci.org/euskadi31/ConfigServiceProvider)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/07bf7885-e810-48e8-9043-e30f49f1f2e7/mini.png)](https://insight.sensiolabs.com/projects/07bf7885-e810-48e8-9043-e30f49f1f2e7)

This service provider for Silex 2.0 uses the Yaml classes from Symfony
to provide a config service to a Silex application, and other service providers.

## Install

Add `euskadi31/config-service-provider` to your `composer.json`:

    % php composer.phar require euskadi31/config-service-provider:~1.0

## Usage

### Configuration

```php
<?php

$env = getenv('APP_ENV') ?: 'prod';

$app = new Silex\Application;

$app->register(new \Euskadi31\Silex\Provider\ConfigServiceProvider(
    __DIR__ . '/config/' . $env . '.yml'
));
```

Now you can specify a `prod` and a `dev` environment.

**config/prod.yml**

```yaml
debug: false
```

**config/dev.yml**

```yaml
debug: true
```

To switch between them, just set the `APP_ENV` environment variable. In apache
that would be:

    SetEnv APP_ENV dev

Or in nginx with fcgi:

    fastcgi_param APP_ENV dev

### Replacements

Also, you can pass an array of replacement patterns as second argument.

```php
<?php

$app = new Silex\Application;

$app->register(new \Euskadi31\Silex\Provider\ConfigServiceProvider(
    __DIR__ . '/config/services.yml',
    [
        'data_path' => __DIR__ . '/data'
    ]
));
```

Now you can use the pattern in your configuration file.

**/config/services.yml**

```yaml
xsl.path: %data_path%/xsl
```

You can also specify replacements inside the config file by using a key with
`%foo%` notation:

```yaml
%root_path%: ../..,
xsl.path: %root_path%/xsl
```

### Register order

Make sure you register ConfigServiceProvider last with your application. If you do not do this,
the default values of other Providers will override your configuration.

## License

ConfigServiceProvider is licensed under [the MIT license](LICENSE.md).
