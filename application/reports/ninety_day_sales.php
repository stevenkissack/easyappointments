<?php
require_once __DIR__ . '/base.php';
require_once __DIR__ . '/helpers.php';

use \koolreport\processes\Custom;
use \koolreport\processes\Group;
use koolreport\processes\CopyColumn;
use \koolreport\processes\TimeBucket;
use \koolreport\processes\Filter;
use \koolreport\processes\Sort;

// TODO: Split to their own sub reports: https://www.koolreport.com/examples/reports/others/async_loading/
class NinetyDaySalesReport extends BaseReport
{

    protected function setup()
    {
        function convertDateRangeToDate($str) {
            // Extract the first date from the string
            $firstDateStr = explode(' - ', $str)[0];
            // Assuming the year is the current year, adjust as necessary
            $year = date('Y');
            // Convert to DateTime
            return DateTime::createFromFormat('M j Y', "$firstDateStr $year");
        }

        $now = new \DateTime();
        // TODO: Refactor this to a configurable length of time, maybe based on the month(+year) + number of months backwards to load
        $ninety_days_ago = $now->modify('-90 days')->format('Y-m-d H:i:s');
        $sixty_days_ago = $now->modify('-60 days')->format('Y-m-d H:i:s');
        $thirty_days_ago = $now->modify('-30 days')->format('Y-m-d H:i:s');

        $this->src('db')
            ->query("SELECT id, start_datetime, end_datetime, status, price, currency, id_services
                     FROM {$this->tablePrefix}appointments
                     WHERE start_datetime >= :ninety_days_ago
                     AND status = 'Booked'
                     ORDER BY start_datetime DESC")
            ->params(["ninety_days_ago" => $ninety_days_ago])
            ->pipeTree(
                function ($node)
                {
                    $node->pipe($this->dataStore('ninety_days_sales'));
                },
                function ($node)
                {
                    $thirty_days_ago = (new \DateTime())->modify('-30 days');
                    $sixty_days_ago = (new \DateTime())->modify('-60 days');
                    
                    // Filtering for bookings for previous 30 days (between -60 and -30 days from now)
                    $node->pipe(new Filter([
                        function ($row) use ($thirty_days_ago, $sixty_days_ago) {
                            $value = new DateTime($row['start_datetime']);
                            return $value >= $sixty_days_ago && $value <= $thirty_days_ago;
                        }
                    ]))
                    ->pipe(new CopyColumn(array(
                        "avg_price"=>"price",
                    )))
                    ->pipe(new Group(array(
                        "sum"=>"price",
                        "count"=>"id",
                        "avg"=>"avg_price",
                        "by"=>"status",
                    )))
                    ->pipe($this->dataStore('prev_30_day_sales_stats'));
                },
                function ($node)
                {
                    $now = new \DateTime();
                    $thirty_days_ago = (new \DateTime())->modify('-30 days');
                    
                    // Filtering for bookings in the last 30 days
                    $node->pipe(new Filter([
                        function ($row) use ($now, $thirty_days_ago) {
                            $value = new DateTime($row['start_datetime']);
                            return $value >= $thirty_days_ago && $value <= $now;
                        }
                    ]))
                    ->pipe(new CopyColumn(array(
                        "avg_price"=>"price",
                    )))
                    ->pipe(new Group(array(
                        "sum"=>"price",
                        "count"=>"id",
                        "avg"=>"avg_price",
                        "by"=>"status",
                    )))
                    ->pipe($this->dataStore('30_day_sales_stats'));
                },
                function ($node)
                {
                    $node->pipe(new TimeBucket(array(
                        "start_datetime"=>array(
                            "bucket"=>"week",
                            // "formatString"=>"jS M" doesn't work because it's used in the bucket logic??
                        )
                    )))
                    ->pipe(new Group(array(
                        "by"=>array("start_datetime","id_services"),
                        "sum"=>"price",
                        "count"=>"id"
                    )))->pipe(new Custom(function($row) {
                        
                        $row['count'] = $row['id'];

                        $row['label'] = getWeekDateRangeFromString($row['start_datetime']);

                        unset($row['id']);
                        unset($row['start_datetime']);

                        unset($row['end_datetime']);
                        unset($row['currency']);
                        unset($row['status']);

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
                        "by"=>"label",
                        "sum"=>"price",
                    )))->pipe(new Sort(array(
                        "label"=>function($a,$b){
                            return convertDateRangeToDate($a)<=>convertDateRangeToDate($b);
                        },
                    )))
                    ->pipe($this->dataStore('ninety_days_weekly_sales'));
                }   
            );
    }
}
