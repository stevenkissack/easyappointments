<?php
require_once __DIR__ . '/base.php';
use \koolreport\processes\Custom;
use \koolreport\processes\Group;
use \koolreport\processes\ColumnRename;
use \koolreport\processes\RemoveColumn;
use \koolreport\processes\TimeBucket;

// TODO: Split to their own sub reports: https://www.koolreport.com/examples/reports/others/async_loading/
class NinetyDaySalesReport extends BaseReport
{

    protected function setup()
    {
        $now = new \DateTime();
        // TODO: Refactor this to a configurable length of time, maybe based on the month(+year) + number of months backwards to load
        $ninety_days_ago = $now->modify('-90 days')->format('Y-m-d H:i:s');

        $this->src('db')
            ->query("SELECT id, start_datetime, end_datetime, status, price, currency, id_services
                     FROM {$this->tablePrefix}appointments
                     WHERE start_datetime >= :ninety_days_ago
                     AND status = 'Booked'")
            ->params(["ninety_days_ago" => $ninety_days_ago])
            ->pipeTree(
                function ($node)
                {
                    $node->pipe($this->dataStore('ninety_days_sales'));
                },
                function ($node)
                {
                    $node->pipe(new TimeBucket(array(
                        "start_datetime"=>"week"
                    )))
                    ->pipe(new Group(array(
                        "by"=>array("start_datetime","id_services"),
                        "sum"=>"price",
                        "count"=>"id"
                    )))->pipe(new ColumnRename(array(
                        "id"=>"count",
                        "start_datetime"=>"week",
                    )))->pipe(new RemoveColumn(array(
                        "end_datetime","currency","status"
                    )))->pipe(new Custom(function($row) {
                        $serviceNameKey = ($this->params['service_map'][$row['id_services']] ?? 'Other');
                        $row[$serviceNameKey] = $row['count'];
                        unset($row['count']);
                        return $row;
                    }))->pipe(new Group(array(
                        "custom" => function($row, $aggregateRow, $count) {

                            if ($count == 1) { // First row in the group
                                foreach ($this->params['service_map'] as $id => $name) {
                                    $aggregateRow[$name] = 0;
                                }
                            }

                            $serviceNameKey = ($this->params['service_map'][$row['id_services']] ?? 'Other');
                            $aggregateRow[$serviceNameKey] = $row[$serviceNameKey];
                            unset($aggregateRow['id_services']);
                            
                            return $aggregateRow;
                        },
                        "by"=>"week",
                        "sum"=>"price",
                    )))
                    ->pipe($this->dataStore('ninety_days_weekly_sales'));
                }   
            );
    }
}
