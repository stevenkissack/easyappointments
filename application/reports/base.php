<?php
require_once __DIR__ . '/../../vendor/koolreport/core/autoload.php';
require_once __DIR__ . '/../config/database.php';

use \koolreport\KoolReport;

class BaseReport extends KoolReport
{
    public $tablePrefix;

    protected function settings()
    {
        global $db; // bring the $db array into scope

        $defaultDbConfig = $db['default'];

        $this->tablePrefix = $db['default']['dbprefix'];

        return [
            "dataSources" => [
                "db" => [
                    "connectionString" => "mysql:host={$defaultDbConfig['hostname']};dbname={$defaultDbConfig['database']}",
                    "username" => $defaultDbConfig['username'],
                    "password" => $defaultDbConfig['password'],
                    "charset" => $defaultDbConfig['char_set'],
                    "collation" => $defaultDbConfig['dbcollat'],
                    "tablePrefix" => $defaultDbConfig['dbprefix'],
                ],
            ],
            "assets" => [
                "path" => "../assets", // Path to store assets
                "url" => "/assets", // URL to access the assets
            ]
        ];
    }
}
