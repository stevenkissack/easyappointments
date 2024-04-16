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
 * Exports controller.
 *
 * Displays the exports page.
 *
 * @package Controllers
 */
class Exports extends EA_Controller
{
    /**
     * Exports constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->model('customers_model');

        $this->load->dbutil();
        $this->load->helper('file');
        $this->load->helper('download');
    }

    /**
     * Render the exports page.
     */
    public function index()
    {
        session(['dest_url' => site_url('exports')]);

        $user_id = session('user_id');

        if (cannot('view', PRIV_SYSTEM_SETTINGS)) {
            if ($user_id) {
                abort(403, 'Forbidden');
            }

            redirect('login');

            return;
        }

        $role_slug = session('role_slug');

        html_vars([
            'page_title' => lang('exports'),
            'active_menu' => PRIV_SYSTEM_SETTINGS,
            'user_display_name' => $this->accounts->get_user_display_name($user_id),
            'privileges' => $this->roles_model->get_permissions_by_slug($role_slug),
        ]);

        $this->load->view('pages/exports');
    }

    /**
     * Download Appointments.
     */
    public function download_appointments()
    {
        try {
            if (cannot('view', PRIV_SYSTEM_SETTINGS)) {
                throw new RuntimeException('You do not have the required permissions for this task.');
            }

            $appointments = $this->db
                ->get_where('appointments', ['is_unavailability' => false]);

            $csv = $this->dbutil->csv_from_result($appointments);
            
            force_download('appointments.csv', $csv);

        } catch (Throwable $e) {
            json_exception($e);
        }
    }

    /**
     * Download Customers.
     */
    public function download_customers()
    {
        try {
            if (cannot('view', PRIV_SYSTEM_SETTINGS)) {
                throw new RuntimeException('You do not have the required permissions for this task.');
            }

            $role_id = $this->customers_model->get_customer_role_id();
    
            $customers = $this->db->get_where('users', ['id_roles' => $role_id]);

            $csv = $this->dbutil->csv_from_result($customers);
            
            force_download('customers.csv', $csv);

        } catch (Throwable $e) {
            json_exception($e);
        }
    }
}
