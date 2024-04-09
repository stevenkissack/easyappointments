<?php extend('layouts/backend_layout');
use koolreport\widgets\koolphp\Table;
use koolreport\widgets\google\BarChart;
use koolreport\widgets\koolphp\Card;
?>

<?php section('content'); ?>

<div class="container-fluid backend-page" id="sales-report-page">

    <div class="row mb-3">
        <div class="col-md-4 col-12">
            <?php

            $thirtyDaySalesArray = $report->dataStore('30_day_sales_stats')->toArray();
            $prevThirtyDaySalesArray = $report->dataStore('prev_30_day_sales_stats')->toArray();

            $thirtyDayStats = reset($thirtyDaySalesArray);
            $prevThirtyDayStats = reset($prevThirtyDaySalesArray);

            $thirtyDayRevenue = 0;
            $thirtyDaySalesCount = 0;
            $thirtyDayAveragePrice = 0;

            $prevThirtyDayRevenue = 0;
            $prevThirtyDaySalesCount = 0;
            $prevThirtyDayAveragePrice = 0;

            // Calculate values if the arrays contain data
            if ($thirtyDayStats) {
                $thirtyDayRevenue = $thirtyDayStats['price'];
                $thirtyDaySalesCount = $thirtyDayStats['id'];
                $thirtyDayAveragePrice = $thirtyDayStats['avg_price'];
            }

            if ($prevThirtyDayStats) {
                $prevThirtyDayRevenue = $prevThirtyDayStats['price'];
                $prevThirtyDaySalesCount = $prevThirtyDayStats['id'];
                $prevThirtyDayAveragePrice = $prevThirtyDayStats['avg_price'];
            }

            Card::create([
                'report' => $report,
                'title' => '30 Day Revenue',
                'value' => $thirtyDayRevenue,
                'baseValue' => $prevThirtyDayRevenue,
                'format' => [
                    'value' => [
                        'prefix' => 'THB ',
                    ],
                ],
            ]);
            ?>  
        </div>
        <div class="col-md-4 col-12">
            <?php Card::create([
                'report' => $report,
                'title' => '30 Day Sales Count',
                'value' => $thirtyDaySalesCount,
                'baseValue' => $prevThirtyDaySalesCount,
            ]); ?>  
        </div>
        <div class="col-md-4 col-12">
            <?php Card::create([
                'report' => $report,
                'title' => '30 Day Average Price',
                'value' => $thirtyDayAveragePrice,
                'baseValue' => $prevThirtyDayAveragePrice,
                'format' => [
                    'value' => [
                        'prefix' => 'THB ',
                    ],
                ],
            ]); ?>  
        </div>
    </div>


    <div class="row">
            <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="fw-light text-black-50 mb-0">
                        90 Day Weekly Services Booked
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    $columns = [
                        'label' => [
                            'label' => 'Week',
                            'type' => 'string',
                        ],
                    ];

                    // Dynamically add columns for each service
                    foreach (vars('service_map') as $serviceId => $serviceName) {
                        $columns[$serviceName] = [
                            'type' => 'number',
                            'label' => $serviceName,
                        ];
                    }

                    BarChart::create([
                        'dataSource' => $report->dataStore('ninety_days_weekly_sales'),
                        'columns' => $columns,
                        'options' => [
                            'isStacked' => true,
                            'hAxis' => ['title' => 'Sales Count', 'format' => '0'],
                            'vAxis' => ['title' => 'Period'],
                        ],
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="fw-light text-black-50 mb-0">
                        Appointments In Last 90 Days
                    </h5>
                </div>
                <div class="card-body">
                <?php Table::create([
                    'report' => $report,
                    'dataSource' => $report->dataStore('ninety_days_sales'),
                    'columns' => [
                        'id' => [
                            'label' => 'ID',
                        ],
                        'start_datetime' => [
                            'type' => 'date',
                            'label' => 'Appointment Date',
                        ],
                        'price' => [
                            'formatValue' => function ($value, $row, $cKey) {
                                if ($cKey === 'price') {
                                    return number_format($value) . ' ' . $row['currency'];
                                } else {
                                    return $value;
                                }
                            },
                            'label' => 'Price',
                        ],
                        'id_services' => [
                            'label' => 'Service',
                            'formatValue' => function ($value, $row, $cKey) {
                                return vars('service_map')[$value];
                            },
                        ],
                    ],
                ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php end_section('content'); ?>

<?php section('scripts'); ?>

<script src="<?= asset_url('assets/js/utils/message.js') ?>"></script>
<script src="<?= asset_url('assets/js/utils/validation.js') ?>"></script>
<script src="<?= asset_url('assets/js/utils/url.js') ?>"></script>
<script src="<?= asset_url('assets/js/pages/sales_report.js') ?>"></script>

<?php end_section('scripts'); ?>
