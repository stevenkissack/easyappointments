<?php
require_once __DIR__ . '/../../vendor/koolreport/core/autoload.php';
require_once __DIR__ . '/../config/database.php';

use \koolreport\KoolReport;
use \koolreport\core\Utility;

class BaseReport extends KoolReport
{
    public $tablePrefix;

    protected function settings()
    {
        global $db; // bring the $db array into scope

        $defaultDbConfig = $db['default'];

        $this->tablePrefix = $db['default']['dbprefix'];

        // Taken from the Friendship codeignitor module of KoolReport
        $document_root = Utility::getDocumentRoot();
        $script_folder = str_replace("\\", "/", realpath(dirname($_SERVER["SCRIPT_FILENAME"])));
        $asset_path = $script_folder . "/assets";
        $asset_url = Utility::strReplaceFirst($document_root, "", $script_folder) . "/assets";
        if (!is_dir($asset_path . "/koolreport_assets")) {
            if (!is_dir($asset_path)) {
                mkdir($asset_path, 0755);
            }
            mkdir($asset_path . "/koolreport_assets", 0755);
        }

        return array(
            "dataSources" => array(
                "db" => [
                    "connectionString" => "mysql:host={$defaultDbConfig['hostname']};dbname={$defaultDbConfig['database']}",
                    "username" => $defaultDbConfig['username'],
                    "password" => $defaultDbConfig['password'],
                    "charset" => $defaultDbConfig['char_set'],
                    "collation" => $defaultDbConfig['dbcollat'],
                    "tablePrefix" => $defaultDbConfig['dbprefix'],
                ],
            ),
            "assets" => array(
                "path" => $asset_path . "/koolreport_assets", // Path to store assets
                "url" => $asset_url . "/koolreport_assets", // URL to access the assets
            )
        );
    }
}
