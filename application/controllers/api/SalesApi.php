<?php

ini_set('error_reporting', 1);
ini_set('display_errors', 1);

defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * @property  employee
 */
class SalesApi extends CI_Controller
{

    private $staff_token;
    private $employee;
    private $item;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Employee','Sales');

        $this->load->helper(array('cookie', 'date', 'form', 'email'));
        $this->load->library(array('encryption', 'form_validation'));

        /* Authentication Begin **/
        $headers = $this->input->request_headers();
        header('Content-type:application/json;charset=utf-8');

        if (array_key_exists("X-Token", $headers)) {
            $this->staff_token = $headers['X-Token'];
            try {
                if (isset($this->driver_token)) {
                    $employee = $this->Employee->get_logged_in_employee_info();
                    if ($employee == false && count($employee) <= 0) {
                        echo json_encode(array("is_logged_out" => "Yes"));
                        die;
                    } else {
                        $this->employee = $employee->row();
                    }
                }
            } catch (Exception $ex) {
                echo $ex->getMessage();
                die;
            }
        } else {
            $response['status'] = '0';
            $response['response'] = 'Token not provided';
            echo json_encode($response, JSON_PRETTY_PRINT);
            exit;
        }

    }


    public function get_receiving_details()
    {

        $returnArr['status'] = '0';
        $returnArr['response'] = '';

        try {
            if (!$this->input->post()) {
                $returnArr['response'] = "Only POST method is allowed";
            } else {
                $receiving_id = $this->input->post('receiving_id');

                if ($receiving_id == '') {
                    $returnArr['response'] = "Some Parameters are missing";
                } else {
                    $receiving = $this->Receiving->get_info($receiving_id);

                    if (count($receiving) < 1) {
                        $returnArr['response'] = 'No receiving found';
                    } else {
                        $returnArr['status'] = '1';
                        $returnArr['response'] = $receiving->result();
                    }
                }
            }
        } catch (Exception $ex) {
            $returnArr['response'] = "Error in connection";
            $returnArr['error'] = $ex->getMessage();
        }
        $response = json_encode($returnArr, JSON_PRETTY_PRINT);
        echo $response;
    }


    public function create_new_receiving()
    {
        $returnArr['status'] = '0';
        $returnArr['response'] = '';

        try {
            if (!$this->input->post()) {
                $returnArr['response'] = "Only POST method is allowed";
            } else {

                $receiving_data = array(
                    'receiving_time' => date('Y-m-d H:i:s'),
                    'supplier_id' => $this->input->post('supplier_id'),
                    'employee_id' => $this->input->post('employee_id'),
                    'comment' => $this->input->post('comment'),
                    'payment_type' => $this->input->post('payment_type')
                );
                $items = $this->input->post('received_items');

                if (!isset($receiving_data)) {
                    $returnArr['response'] = "Some Parameters are missing";
                } else {

                    $data = array();

                    foreach($items as $re_item=> $received_item){
                        $item = array(
                            'item_id' => $received_item['item_id'],
                            'description' => $received_item['description'],
                            'serialnumber' =>$received_item['serialnumber'],
                            'quantity_purchased' => $received_item['quantity_purchased'],
                            'item_cost_price' => $received_item['item_cost_price'],
                            'item_unit_price' => $received_item['item_unit_price'],
                            'discount_percent' => $received_item['discount_percent'],
                            'item_location' => $received_item['item_location'],
                            'receiving_quantity' => $received_item['receiving_quantity']
                        );
                        array_push($data, $item);
                    }

                    $receiving_saved = $this->Receiving->create_new_receiving($receiving_data, $data);

                    if (!$receiving_saved) {
                        $returnArr['response'] = 'Object not saved';
                    } else {
                        $returnArr['status'] = '1';
                        $returnArr['response'] =  $receiving_saved;
                    }
                }
            }
        } catch (Exception $ex) {
            $returnArr['response'] = "Error in connection";
            $returnArr['error'] = $ex->getMessage();
        }
        $response = json_encode($returnArr, JSON_PRETTY_PRINT);
        echo $response;

    }


    public function create_sales()
    {
        $returnArr['status'] = '0';
        $returnArr['response'] = '';
        try {
            if(!$this->input->post()){
                $returnArr['response'] = "Only Post method is allowed";
            }else{
                $sales_data = array(
                    'sale_time'			=> date('Y-m-d H:i:s'),
                    'customer_id'		=> $this->Customer->exists($customer_id) ? $customer_id : NULL,
                    'employee_id'		=> $this->input->post('employee_id'),
                    'comment'			=> $this->input->post('comment'),
                    'sale_status'		=> $this->input->post('sale_status'),
                    'invoice_number'	=> $this->input->post('invoice_number'),
                    'quote_number'		=> $this->input->post('quote_number'),
                    'work_order_number'	=> $this->input->post('work_order_number'),
                    'dinner_table_id'	=> $this->input->post('dinner_table'),
                    'sale_status'		=> $this->input->post('sale_status'),
                    'sale_type'			=> $this->input->post('sale_type')
                );
                 
                $sales = $this->Sales->save($sales_data);
                if (!$sales_data){
                    $returnArr['response'] = 'Object Not saved';
                }else{
                    $returnArr['status'] = '1';
                    $returnArr['response'];
                }
            }
        } catch (Exception $ex) {
            $returnArr['response'] = 'Error in connection';
            $returnArr['error'] = $ex->getMessage();
        }
        $response = json_encode($returnArr,JSON_PRETTY_PRINT);
        echo $response;

    }

}
