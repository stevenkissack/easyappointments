<?php defined('BASEPATH') or exit('No direct script access allowed');

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

        $now = new DateTime();
        $ninety_days_ago = $now->modify('-90 days');

        $services = $this->db
            ->select(
                'name',
            )
            ->from('services')
            ->get()
            ->result_array();

        $sales_history = $this->db
            ->select('appointments.id, start_datetime, end_datetime, status, appointments.price, appointments.currency, id_services, services.name as service_name')
            ->from('appointments')
            ->join('services', 'appointments.id_services = services.id', 'left')
            ->where('start_datetime >=', $ninety_days_ago->format('Y-m-d H:i:s'))
            ->where('appointments.status', 'Booked')
            ->get()
            ->result_array();

        $service_names = array_map(function($service) {
                return $service['name'];
            }, $services);

        // echo $this->db->last_query();

        // Aggregate sales data
        $aggregated_data = [];
        foreach ($sales_history as $record) {
            $serviceDate = new DateTime($record['start_datetime']);
            // First day of the month for the given service date
            $firstDayOfMonth = new DateTime($serviceDate->format('Y-m-01'));
            // Calculate the difference in weeks + 1 to adjust week numbering starting from 1
            $weekOfMonth = intval($serviceDate->format('W')) - intval($firstDayOfMonth->format('W')) + 1;
            
            $monthName = $serviceDate->format('M');
            $weekLabel = "{$monthName}, week {$weekOfMonth}";
            
            if (!isset($aggregated_data[$weekLabel])) {
                $aggregated_data[$weekLabel] = array_fill_keys($service_names, 0);
            }
            
            if (in_array($record['service_name'], $service_names)) {
                $aggregated_data[$weekLabel][$record['service_name']]++;
            }
        }

        // Corrected approach for initializing $chartData with header row
        $chartData = [["Week"]];
        foreach ($service_names as $name) {
            $chartData[0][] = $name;
        }

        // Populate chart data with sales counts
        foreach ($aggregated_data as $weekLabel => $services) {
            $row = [$weekLabel]; // First element of each row is the week label
            foreach ($service_names as $serviceName) {
                $row[] = $services[$serviceName]; // Append sales count for each service
            }
            $chartData[] = $row;
        }
        
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
            'sales_history' => $sales_history,
            'chart_data' => $chartData,
            'service_names' => $service_names
        ]);

        $this->load->view('pages/sales_report');
    }

}
