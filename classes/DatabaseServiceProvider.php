<?php

require 'Connection.php';

use Silex\Application;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DataBaseServiceProvider implements ServiceProviderInterface {

    public function register(Container $app) {
        $app['db.create'] = $app->protect(function ($dsn, $user, $pass, $options) {
            return new Connection($dsn, $user, $pass, $options);
        });

        $app['db'] = function ($app) {
            return $app['db.create']($app['db.dsn'],
                $app['db.user'],
                $app['db.pass'],
                $app['db.options']);
        };
    }
}