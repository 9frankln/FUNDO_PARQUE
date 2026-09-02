<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        if ($app['config']->get('database.default') === 'mysql') {
            $database = $app['config']->get('database.connections.mysql.database');
            if ($database === 'fundo_parque01' || $database !== 'fundo_parque_testing') {
                $app['config']->set('database.connections.mysql.database', 'fundo_parque_testing');
                \Illuminate\Support\Facades\DB::purge('mysql');
            }
        }

        return $app;
    }
}
