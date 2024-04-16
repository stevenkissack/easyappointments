<?php extend('layouts/backend_layout'); ?>

<?php section('content'); ?>

<div id="integrations-page" class="container backend-page">
    <div class="row">
        <div class="col-sm-3 offset-sm-1">
            <?php component('settings_nav'); ?>
        </div>
        <div id="exports" class="col-sm-6">
            <h4 class="text-black-50 border-bottom py-3 mb-3 fw-light">
                <?= lang('exports') ?>
            </h4>

            <!-- <p class="form-text text-muted mb-4">
                <?= lang('exports_info') ?>
            </p> -->

            <div class="row">
                <div class="col-sm-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="fw-light text-black-50 mb-0">
                                <?= lang('appointments') ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3 export-info">
                                <small>
                                    <?= lang('export_appointments_info') ?>
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <form method="POST" action="<?= site_url('exports/download_appointments') ?>">
                                <input type="hidden" name="csrf_token" value="<?= vars('csrf_token') ?>">
                                <button type="submit" class="btn btn-download btn-outline-primary w-100">
                                    <i class="fas fa-download me-2"></i>
                                    <?= lang('download') ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="fw-light text-black-50 mb-0">
                                <?= lang('Customers') ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3 exports-info">
                                <small>
                                    <?= lang('export_customers_info') ?>
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <form method="POST" action="<?= site_url('exports/download_customers') ?>">
                                <input type="hidden" name="csrf_token" value="<?= vars('csrf_token') ?>">
                                <button type="submit" class="btn btn-download btn-outline-primary w-100">
                                    <i class="fas fa-download me-2"></i>
                                    <?= lang('download') ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>


                <div class="col-sm-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="fw-light text-black-50 mb-0">
                                <?= lang('Database') ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3 exports-info">
                                <small>
                                    <?= lang('export_database_info') ?>
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <form method="POST" action="<?= site_url('exports/download_database') ?>">
                                <input type="hidden" name="csrf_token" value="<?= vars('csrf_token') ?>">
                                <button type="submit" class="btn btn-download disabled btn-outline-primary w-100">
                                    <i class="fas fa-download me-2"></i>
                                    <?= lang('download') ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php end_section('content'); ?>