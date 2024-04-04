<?php extend('layouts/backend_layout');
use koolreport\widgets\koolphp\Table;
use koolreport\widgets\google\BarChart;
use koolreport\widgets\koolphp\Card;
?>

<?php section('content'); ?>

<div class="container-fluid backend-page" id="sales-report-page">

    <div class="row mb-3">
        <div class="col-md-4 col-12">
            <?php Card::create(array(
                "title"=>"30 Day Revenue",
                "value"=>11249,
                "baseValue"=>9230,
                "format"=>array(
                    "value"=>array(
                        "prefix"=>"THB "
                    )
                )
            )); ?>  
        </div>
        <div class="col-md-4 col-12">
            <?php Card::create(array(
                "title"=>"30 Day Sales",
                "value"=>56,
                "baseValue"=>43,
            )); ?>  
        </div>
        <div class="col-md-4 col-12">
            <?php Card::create(array(
                "title"=>"Total Sales",
                "value"=>343,
                "baseValue"=>295,
            )); ?>  
        </div>
    </div>


    <div class="row">
            <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="fw-light text-black-50 mb-0">
                        90 Days Weekly Sales
                    </h5>
                </div>
                <div class="card-body">
                    <?php 
                    $columns = array(
                        "week" => array(
                            "label" => "Week",
                            "type" => "string"
                        )
                    );
                    
                    // Dynamically add columns for each service
                    foreach (vars('service_map') as $serviceId => $serviceName) {
                        $columns[$serviceName] = array(
                            "type" => "number",
                            "label" => $serviceName
                        );
                    }

                    BarChart::create([
                        'dataSource' => $report->dataStore('ninety_days_weekly_sales'),
                        'columns' => $columns,
                        'options' => [
                            'isStacked' => true,
                            'hAxis' => ['title' => 'Sales Count', 'format' => '0'],
                            'vAxis' => ['title' => 'Period'],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="fw-light text-black-50 mb-0">
                        Last 90 Days Sales
                    </h5>
                </div>
                <div class="card-body">
                <?php Table::create([
                    'dataSource' => $report->dataStore('ninety_days_sales'),
                    'columns' => [
                        'id' => [
                            'label' => 'ID',
                        ],
                        'start_datetime' => [
                            'type' => 'date',
                            'label' => 'Sale Date',
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
