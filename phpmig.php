<?php
use Phpmig\Adapter;
use Pimple\Container;

$container = new Container();

$container['db'] = function () {
    require_once __DIR__ . '/config.php';

    $mysql = "mysql:dbname={$env['database']['db']};host={$env['database']['host']};port={$env['database']['port']}";
    $dbh = new PDO($mysql, $env['database']['user'], $env['database']['pass']);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $dbh;
};

$container['phpmig.adapter'] = function ($c) {
    return new Adapter\PDO\Sql($c['db'], 'migrations');
};

$container['phpmig.migrations_path'] = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';

// You can also provide an array of migration files
// $container['phpmig.migrations'] = array_merge(
//     glob('migrations_1/*.php'),
//     glob('migrations_2/*.php')
// );

return $container;
