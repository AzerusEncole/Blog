<?php

include 'Form.php';

use Silex\Application;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class FormServiceProvider implements ServiceProviderInterface {

    public function register(Container $app) {
        $app['form.create'] = $app->protect(function ($db) {
            return new Form($db);
        });

        $app['form'] = function ($app) {
            return $app['form.create']($app['db']);
        };
    }
}