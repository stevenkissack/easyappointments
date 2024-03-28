<?php extend('layouts/backend_layout'); 

require_once __DIR__ . '/../../../vendor/koolreport/core/autoload.php';
use \koolreport\widgets\koolphp\Table;
use \koolreport\widgets\google\BarChart;

?>

<?php section('content'); ?>

<div class="container-fluid backend-page" id="sales-report-page">

    <div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="fw-light text-black-50 mb-0">
                    3 Month Sales Counts By Service per Week
                </h5>
            </div>
            <div class="card-body">
                <?php 
                BarChart::create([
                    "dataSource" => vars('chart_data'),
                    "options" => [
                        "isStacked" => true,
                        "hAxis" => ["title" => "Sales Count"],
                        "vAxis" => ["title" => "Period"],
                    ]
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
                        Last 90 Days Sales
                    </h5>
                </div>
                <div class="card-body">
                <?php 
                    Table::create(array(
                        "dataSource"=>vars('sales_history'),
                        "columns"=>array(
                            "id"=>array(
                                "label"=>"ID"
                            ),
                            "start_datetime"=>array(
                                "type"=>"date",
                                "label"=>"Sale Date",
                            ),
                            "price"=>array(
                                'formatValue'=>function($value, $row, $cKey)
                                                {
                                                    if ($cKey === 'price')
                                                        return number_format($value)." ".$row["currency"];
                                                    else
                                                        return $value;
                                                },
                                "label"=>"Price"
                            ),
                            "service_name"=>array(
                                "label"=>"Service"
                            ),
                        ),
                    ));
                ?>
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
