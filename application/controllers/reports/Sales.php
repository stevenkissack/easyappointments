<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . "/../../reports/ninety_day_sales.php";

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Online Appointment Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://easyappointments.org
 * @since       v1.5.0
 * ---------------------------------------------------------------------------- */

/**
 * Sales reports controller.
 *
 * Handles the sales reports related operations.
 *
 * @package Controllers
 */
class Sales extends EA_Controller
{
    /**
     * Sales constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->model('services_model');
        $this->load->model('appointments_model');
        $this->load->model('roles_model');

        $this->load->library('accounts');
        $this->load->library('timezones');
    }

    /**
     * Render the backend sales reports page.
     *
     * On this page admin users will be able to see sales data
     */
    public function index()
    {
        session(['dest_url' => site_url('reports/sales')]);

        $user_id = session('user_id');

        if (cannot('view', PRIV_REPORTS)) {
            if ($user_id) {
                abort(403, 'Forbidden');
            }

            redirect('login');

            return;
        }

        $role_slug = session('role_slug');

        $services = $this->db
            ->select(
                'id, name',
            )
            ->from('services')
            ->get()
            ->result_array();

        $serviceMap = [];
        foreach ($services as $service) {
            $serviceMap[$service['id']] = $service['name'];
        }
        // $service_names = array_map(function($service) {
        //         return $service['name'];
        //     }, $services);

        $report = new NinetyDaySalesReport([
            "service_map"=>$serviceMap
        ]);
        $report->run();
        
        script_vars([
            'user_id' => $user_id,
            'role_slug' => $role_slug,
        ]);

        html_vars([
            'page_title' => lang('sales_report'),
            'active_menu' => PRIV_REPORTS,
            'user_display_name' => $this->accounts->get_user_display_name($user_id),
            'timezones' => $this->timezones->to_array(),
            'privileges' => $this->roles_model->get_permissions_by_slug($role_slug),
            'report' => $report,
            'service_map' => $serviceMap,
        ]);

        $this->load->view('pages/sales_report', array(
            'report' => $report
        ));
    }

}
