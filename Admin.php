<?php
if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Admin extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        // Load Models
        $this->load->model('admin_model', '', true);
        $this->load->model('page_model', '', true);

        // Set Response Type Headers
        $this->output->set_header('Last-Modified: ' .gmdate("D, d M Y H:i:s") . ' GMT');
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header("Expires: 0");
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');

        // Load Helpers
        $this->load->helper(
            [
                'no_cache',
                'form',
                'url',
                'captcha',
                'directory'
            ]
        );

        // Load Libraries
        $this->load->library(
            [
                'upload',
                'Datatables',
                'image_lib',
                'pagination',
                'form_validation',
                'table',
                'email',
                'session'
            ]
        );

        $this->load->database();

        // Explicitly Setting Language for Development
        $_SESSION['language_id'] = 1;
        $this->lang->load('en', 'english');
    }

    /**
     * Render the admin control page.
     *
     * @return view Returns the dashboard or Login page based on user authentication.
     */
    public function index()
    {
        $userTypeId  = $this->session->userdata('user_type_id');
        $isLoggedIn  = $this->session->userdata('is_logged_in');

        $this->session->set_userdata(
            [
                'current_menu_id'   => 'dashboard',
                'current_page_name' => 'Dashboard',
                'small_page'        => 'Statistics'
            ]
        );

        if ($isLoggedIn == true) {
            redirect('crayo-admin/dashboard');
        } else {
            $this->load->view('admin/login');
        }
    }

    /**
     * Utility/Helper Function to List Grocery Crud Records.
     *
     * @param array  $current_page_name Current Page Name in format of language => value
     * @param string $current_menu_id   Current Menu Id to store in Session.
     * @param array  $columns           Columns to fetch in format of column_name => Display As.
     * @param string $subject           Grocery CRUD Subject Title.
     * @param string $table_name        Name of the Associated Table.
     * @param string $edit_link         Link to Edit the Record.
     *
     * @return mixed $crud|redirect      Returns an Grocery CRUD instance or redirects the user to login page.
     */
    protected function getGroceryCrudListings(
        $current_page_name = [],
        $current_menu_id = '',
        $columns = [],
        $subject = '',
        $table_name = '',
        $edit_link = ''
    ) {
        if ($_SESSION['language_id'] == 2 || !isset($_SESSION['language_id'])) {
            $current_page_name = $current_page_name['arabic'];
            $this->lang->load("ar", "arabic");
        } else {
            $current_page_name = $current_page_name['english'];
            $this->lang->load("en", "english");
        }
        $sess_page = [
            'current_menu_id'   => $current_menu_id,
            'current_page_name' => $current_page_name,
        ];
        $this->session->set_userdata($sess_page);
        $userTypeId  = $this->session->userdata('user_type_id');
        $isLoggedIn  = $this->session->userdata('is_logged_in');
        if ($isLoggedIn == true) {
            switch ($userTypeId) {
            case 1:
            case 2:
                redirect('crayo-admin');
                break;
            case 3:
                $this->load->helper('form');
                $this->load->library('grocery_CRUD');

                $crud = new grocery_CRUD();

                if ($_SESSION['language_id'] == 2 || !isset($_SESSION['language_id'])) {
                    $crud->set_language('arabic');
                } else {
                    $crud->set_language('english');
                }

                $crud->unset_add();
                $crud->unset_edit();
                $crud->unset_read();
                $crud->unset_export();
                $crud->unset_print();

                $crud->columns(array_keys($columns));

                foreach ($columns as $key => $val) {
                    if (empty($this->lang->line($val))) {
                        $crud->display_as($key, $val);
                    } else {
                        $crud->display_as($key, $this->lang->line($val));
                    }
                }

                $crud->set_subject($subject);
                $crud->set_table($table_name);
                $crud->set_theme('datatables');

                $crud->add_action('Edit', '', $edit_link, 'bg-orange fa fa-pencil');

                return $crud;
            }
        } else {
            redirect('crayo-admin');
        }
    }

    /**
     * List all Menus used in the application
     *
     * @return view Returns the view along with all the menus.
     */
    public function manage_menu()
    {
        $current_page_name = [
            'arabic'  => "إدارة القوائم",
            'english' => "Manage Menus"
        ];

        $current_menu_id = 'manage_menu';

        $columns = [
            'menu_name_english' => 'Name',
            'menu_slug'         => 'Custom Link',
            'menu_parent'       => 'Menu Parent'
        ];

        $crud = $this->getGroceryCrudListings(
            $current_page_name,
            $current_menu_id,
            $columns,
            'Manage Menus',
            'crayotech_main_menu',
            'crayo-admin/addedit_menus/edit'
        );

        $crud->set_relation('page_layout', 'crayotech_page_layout', 'layout_name');
        $crud->set_relation('menu_id', 'crayotech_page_contents', 'type');

        if ($_SESSION['language_id'] == 2 || !isset($_SESSION['language_id'])) {
            $crud->set_relation('menu_parent', 'crayotech_main_menu', 'menu_name_arabic');
        } else {
            $crud->set_relation('menu_parent', 'crayotech_main_menu', 'menu_name_english');
        }

        $crud->field_type('menu_postion', 'dropdown', array('top' => 'Top'));

        $crud->callback_column('type', array($this, '_type'));

        // Added btn styling as the first in the third parameter
        // The first styling in the third parameter is applied to the
        // respective action buttons
        $crud->add_action('Edit Page Contents', '', 'crayo-admin/content', 'bg-olive fa fa-eye');

        $output = $crud->render();
        $this->load->view('admin/manage_menus', $output);
    }

    /**
     * Function to Create or Update Menu
     *
     * @return view Returns the view whether to update or create menu.
     */
    public function addedit_menus()
    {
        if ($_SESSION['language_id'] == 2 || !isset($_SESSION['language_id'])) {
            $current_page_name = "إدارة القوائم";
        } else {
            $current_page_name = "Manage Menus";
        }
        $sess_page = array(
            'current_menu_id'   => 'manage_site',
            'current_page_name' => $current_page_name,
        );
        $this->session->set_userdata($sess_page);
        $userTypeId = $this->session->userdata('user_type_id');
        $isLoggedIn = $this->session->userdata('is_logged_in');

        if ($isLoggedIn == true) {
            switch ($userTypeId) {
            case 1:
            case 2:
                redirect('crayo-admin');
                break;

            case 3:
                $action = $this->uri->segment(3);
                $insert_id = "";
                $data['msg'] = "";
                if ($_POST) {
                    $action = "edit";
                    switch ($_POST['action']) {
                    case 'add':
                        $data['msg'] = 'Successfully Added the Menu Details!';
                        $this->admin_model->add_menus();
                        $insert_id = $this->db->insert_id();
                        break;

                    case 'edit':
                        $insert_id = $_POST['menu_id'];
                        $data['msg'] = 'Successfully Updated the Menu Details!';
                        $this->admin_model->update_menus($insert_id);
                        break;
                    }
                }

                if ($insert_id != "") {
                    $data['menu_details'] = $this->admin_model->menu_details($insert_id);
                }

                if ($action == "edit") {
                    if ($insert_id == "") {
                        $insert_id = $this->uri->segment(4);
                    }

                    $data['menu_details'] = $this->admin_model->menu_details($insert_id);
                    $data['button_name']  = "Update";
                    $data['action'] = "edit";
                } elseif ($action == "add") {
                    $data['button_name'] = "Create";
                    $data['action'] = "add";
                }
                $data['get_main_menus'] = $this->admin_model->get_menus();
                $data['get_layout']     = $this->admin_model->get_layout();
                $this->load->view('admin/editadd_menus', $data);
            }
        } else {
            redirect('crayo-admin');
        }
    }

    /**
     * Function to List Contents of Menu
     *
     * @param int $menuid Menu Id of the Content.
     *
     * @return view Returns the view that need to be edited.
     */
    public function content($menuid)
    {
        if ($_SESSION['language_id'] == 2 || !isset($_SESSION['language_id'])) {
            $current_page_name = "إدارة محتويات الصفحة";
        } else {
            $current_page_name = "Manage Page Contents";
        }
        $sess_page = array(
            'current_menu_id'   => 'manage_site',
            'current_page_name' => $current_page_name
        );
        $this->session->set_userdata($sess_page);
        $isLoggedIn = $this->session->userdata('is_logged_in');
        if ($isLoggedIn == true) {
            $all_sections = $this->admin_model->get_all_sections_for_page($menuid);
            $data['button_name'] = "Save";
            $data['msg'] = "";
            if ($all_sections) {
                $sections = explode(',', $all_sections['section_ids']);
                foreach ($sections as $section) {
                    $data['section_details'][] = $this->admin_model->get_section_details($section);
                }
                $data['menuid'] = $menuid;
                $this->load->view('admin/content_edit', $data);
            } else {
                $this->load->view('admin/content_edit', $data);
            }
        } else {
            redirect('crayo-admin');
        }
    }

    /**
     * Function to add Content to the Menu
     *
     * @param Integer $menuid Menu Id.
     *
     * @return view Returns the view with the list of menus
     */
    public function addcontent($menuid)
    {
        $increment = 0;
        $image = [];
        $image_name = '';
        $config['upload_path'] = 'assets/uploads/imagefiles/';
        $config['allowed_types'] = 'gif|jpg|png|jpeg|pdf|doc|docx';
        $config['max_size'] = '10000';
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        foreach ($_FILES as $field => $file) {
            if (empty($file['name'])) {
                $field1 = explode('_', $field);
                $section_id = $this->admin_model->get_sectionid_from_name($field1[0]);
                $image_name = $this->admin_model->get_image_name($menuid, $section_id['section_id'], $field1[1]);
                $image[$increment]['name']  = $image_name['content'];
                $image[$increment]['field'] = $field;
            } else {
                if ($file['error'] == 0) {
                    if ($this->upload->do_upload($field)) {
                        $image_details = [];
                        $image_details[] = $this->upload->data();
                        $image[$increment]['name']  = $image_details[0]['file_name'];
                        $image[$increment]['field'] = $field;
                    } else {
                        $errors = $this->upload->display_errors();
                    }
                }
            }
            $increment++;
        }
        $formValues = $this->input->post(null, false);

        $result = $this->admin_model->insert_content($menuid, $formValues, $image);
        $this->manage_menu();
    }


    /**
     * Function to generate preview of Content.
     * This function is a clone of addContent method.
     * This function is invoked via AJAX.
     *
     * @return JSON Returns the Preview URL in JSON.
     */
    public function previewContent()
    {
        // Get All Form Values
        $form_values = $this->input->post(null, true);
        $menu_id = $this->input->post('menu_id');

        $config['upload_path'] = 'assets/uploads/imagefiles/';
        $config['allowed_types'] = 'gif|jpg|png|jpeg|pdf|doc|docx';
        $config['max_size'] = '10000';
        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        $increment = 0; // Loop Counter
        $image = [];
        $image_name = '';

        foreach ($_FILES as $field => $file) {
            if (empty($file['name'])) {
                $field1 = explode('_', $field);
                $section_id = $this->admin_model->get_sectionid_from_name($field1[0]);
                $image_name = $this->admin_model->get_image_name($menuid, $section_id['section_id'], $field1[1]);
                $image[$increment]['name'] = $image_name['content'];
                $image[$increment]['field'] = $field;
            } else {
                if ($file['error'] == 0) {
                    if ($this->upload->do_upload($field)) {
                        $image_details = array();
                        $image_details[] = $this->upload->data();
                        $image[$increment]['name'] = $image_details[0]['file_name'];
                        $image[$increment]['field'] = $field;
                    } else {
                        $errors = $this->upload->display_errors();
                    }
                }
            }
            $increment++;
        }
        $result = $this->admin_model->insert_preview_content($menu_id, $form_values, $image);
        $menu_slug = $this->admin_model->get_menu_slug($menu_id);
        echo json_encode(
            [
                "preview_url" => base_url().$menu_slug->menu_slug
            ]
        );
    }

     /**
      * Function to Manage Partners.
      *
      * @return view Returns the view with the list of partners.
      */
    public function manage_partners()
    {
        $current_page_name = [
            'arabic'  => "إدارة الشركاء",
            'english' => "Manage Partners"
        ];

        $current_menu_id = 'manage_partners';

        $columns = [
            'partner_name_eng' => 'Name',
            'partner_link'     => 'Link',
            'sort_order'       => 'Sort Order'
        ];

        $subject = 'Partner Details';

        $table_name = 'crayotech_partners';

        $edit_link = 'crayo-admin/addedit_partners/edit';

        $crud = $this->getGroceryCrudListings(
            $current_page_name,
            $current_menu_id,
            $columns,
            $subject,
            $table_name,
            $edit_link
        );

        $output = $crud->render();
        $this->load->view('admin/manage_partners', $output);
    }


     /**
      * Function to Add and Edit Partners.
      *
      * @return view Returns the view to add or update Partners.
      */
    public function addedit_partners()
    {
        if ($_SESSION['language_id'] == 2 || !isset($_SESSION['language_id'])) {
            $current_page_name = "إدارة الشركاء";
        } else {
            $current_page_name = "Manage Partners";
        }
        $this->session->set_userdata(
            [
                'current_menu_id'    => 'manage_partners',
                'current_page_name'  => $current_page_name
            ]
        );
        $userTypeId = $this->session->userdata('user_type_id');
        $isLoggedIn = $this->session->userdata('is_logged_in');
        if ($isLoggedIn == true) {
            switch ($userTypeId) {
            case 1:
            case 2:
                redirect('crayo-admin');
                break;
            case 3:
                $action = $this->uri->segment(3);
                $insert_id = '';
                $data['msg'] = '';
                $source = '';
                if ($_POST) {
                    $action = 'edit';
                    switch ($_POST['action']) {
                    case 'add':
                        $data['msg'] = 'Successfully Added the Partner!';

                        // Upload Background Source Files
                        $config['upload_path']   = 'assets/uploads/partners';
                        $config['allowed_types'] = 'gif|jpg|png|jpeg|mp4|ogg';
                        $config['max_size']      = '10000';
                        $this->load->library('upload', $config);
                        $this->upload->initialize($config);

                        if ($this->upload->do_upload('image')) {
                            $source = $this->upload->data()['file_name'];
                        } else {
                            $data['errors'] = $this->upload->display_errors();
                        }

                        $this->page_model->addPartner($source);

                        $insert_id = $this->db->insert_id();
                        break;

                    case 'edit':
                        $insert_id = $_POST['partner_id'];
                        $data['msg'] = "Successfully Updated the Partner!";

                        // File Upload
                        $config['upload_path']   = 'assets/uploads/partners';
                        $config['allowed_types'] = 'gif|jpg|png|jpeg|mp4|ogg';
                        $config['max_size']      = '10000';
                        $this->load->library('upload', $config);
                        $this->upload->initialize($config);

                        if ($this->upload->do_upload('image')) {
                            $source = $this->upload->data()['file_name'];
                        } else {
                            $record = $this->page_model->getpartnerDetails($insert_id);
                            $source = $record->partner_img;
                            $data['errors'] = $this->upload->display_errors();
                        }

                        $this->page_model->updatePartner($insert_id, $source);
                    }
                }

                if ($action == "edit") {
                    if ($insert_id == "") {
                        $insert_id = $this->uri->segment(4);
                    }
                    $data['partner_details'] = $this->page_model->getPartnerDetails($insert_id);
                    $data['button_name'] = "Update";
                    $data['action'] = "edit";
                } elseif ($action == "add") {
                    $data['button_name'] = "Create";
                    $data['action'] = "add";
                }
                $this->load->view('admin/editadd_partners', $data);
                break;
            }
        } else {
            redirect('crayo-admin');
        }
    }

    /**
     * Function to Manage Contact Addresses.
     *
     * @return view Returns the view with the list of addresss.
     */
    public function manage_address()
    {
        $current_page_name = [
            'arabic'  => "إدارة العنوان",
            'english' => "Manage Address"
        ];

        $current_menu_id = 'manage_address';

        $columns = [
            'phone'   => 'Phone',
            'email'   => 'Email',
            'country' => 'Country',
            'enable'  => 'Enabled'
        ];

        $subject = 'Address Details';

        $table_name = 'crayotech_address';

        $edit_link = 'crayo-admin/addedit_address/edit';

        $crud = $this->getGroceryCrudListings(
            $current_page_name,
            $current_menu_id,
            $columns,
            $subject,
            $table_name,
            $edit_link
        );

        if ($_SESSION['language_id'] == 2 || !isset($_SESSION['language_id'])) {
            $crud->callback_column('phone', array($this, '_right_align_phone'));
        }

        $output = $crud->render();
        $this->load->view('admin/manage_address', $output);
    }

    /**
     * Function to handle CRUD of Addresses.
     *
     * @return view Return View with the Address fields.
     */
    public function addedit_address()
    {
        if ($_SESSION['language_id'] == 2 || !isset($_SESSION['language_id'])) {
            $current_page_name = "إدارة العنوان";
        } else {
            $current_page_name = "Manage Address";
        }
        $sess_page = array(
            'current_menu_id' => 'manage_site',
            'current_page_name' => $current_page_name,
        );
        $this->session->set_userdata($sess_page);
        $userTypeId = $this->session->userdata('user_type_id');
        $isLoggedIn = $this->session->userdata('is_logged_in');
        if ($isLoggedIn == true) {
            switch ($userTypeId) {
            case 1:
            case 2:
                redirect('crayo-admin');
                break;
            case 3:
                $action = $this->uri->segment(3);
                $insert_id = "";
                $data['msg'] = "";
                if ($_POST) {
                    $action = "edit";
                    switch ($_POST['action']) {
                    case 'add':
                        $data['msg'] = 'Successfully added the address details';
                        $this->page_model->addAddress();
                        $insert_id = $this->db->insert_id();
                        break;
                    case 'edit':
                        $insert_id = $_POST['address_id'];
                        $data['msg'] = 'Successfully updated the address details';
                        $this->page_model->updateAddress($insert_id);
                        break;
                    }
                }
                if ($insert_id != "") {
                    $data['address_details'] = $this->page_model->getAddressDetails($insert_id);
                }
                if ($action == "edit") {
                    if ($insert_id == "") {
                        $insert_id = $this->uri->segment(4);
                    }
                    $data['address_details'] = $this->page_model->getAddressDetails($insert_id);
                    $data['button_name'] = "Update";
                    $data['action'] = "edit";
                } elseif ($action == "add") {
                    $data['button_name'] = "Create";
                    $data['action'] = "add";
                }
                $this->load->view('admin/editadd_addresses', $data);
            }
        } else {
            redirect('crayo-admin');
        }
    }

    /**
     * Function to manage Home Page Banners.
     *
     * @return view Return the view with the list of banners on Home Page.
     */
    public function manage_banners()
    {
        $current_page_name = [
            'arabic'  => "إدارة العنوان",
            'english' => "Manage Banners"
        ];

        $current_menu_id = 'manage_banners';

        $columns = [
            "title_en"       => "Title",
            "description_en" => "Description",
            "menu_id"        => "Menu"
        ];

        $subject = 'Banner Details';

        $table_name = 'crayotech_banners';

        $edit_link = 'crayo-admin/addedit_banner/edit';

        $crud = $this->getGroceryCrudListings(
            $current_page_name,
            $current_menu_id,
            $columns,
            $subject,
            $table_name,
            $edit_link
        );

        $crud->callback_column('menu_id', array($this, '_getMenuName'));

        $output = $crud->render();
        $this->load->view('admin/manage_banners', $output);
    }

    /**
     * Function to Add and Edit Home Page Banners.
     *
     * @return view Returns the view to add or update banner.
     */
    public function addedit_banner()
    {
        if ($_SESSION['language_id'] == 2 || !isset($_SESSION['language_id'])) {
            $current_page_name = "إدارة الأخبار";
        } else {
            $current_page_name = "Manage Banners";
        }
        $this->session->set_userdata(
            [
                'current_menu_id'    => 'manage_banners',
                'current_page_name'  => $current_page_name
            ]
        );
        $userTypeId = $this->session->userdata('user_type_id');
        $isLoggedIn = $this->session->userdata('is_logged_in');
        if ($isLoggedIn == true) {
            switch ($userTypeId) {
            case 1:
            case 2:
                redirect('crayo-admin');
                break;
            case 3:
                $action = $this->uri->segment(3);
                $insert_id = '';
                $data['msg'] = '';
                $source = '';
                if ($_POST) {
                    $action = 'edit';
                    switch ($_POST['action']) {
                    case 'add':
                        $data['msg'] = 'Successfully Added the Banner!';

                        // Upload Background Source Files
                        $config['upload_path']   = 'assets/uploads/banners';
                        $config['allowed_types'] = 'gif|jpg|png|jpeg|mp4|ogg';
                        $config['max_size']      = '10000';
                        $this->load->library('upload', $config);
                        $this->upload->initialize($config);

                        if ($this->upload->do_upload('source')) {
                            $source = $this->upload->data()['file_name'];
                        } else {
                            $data['errors'] = $this->upload->display_errors();
                        }

                        $this->page_model->addBanner($source);

                        $insert_id = $this->db->insert_id();
                        break;

                    case 'edit':
                        $insert_id = $_POST['banner_id'];
                        $data['msg'] = "Successfully Updated the Banner!";

                        // File Upload
                        $config['upload_path']   = 'assets/uploads/banners';
                        $config['allowed_types'] = 'gif|jpg|png|jpeg|mp4|ogg';
                        $config['max_size']      = '10000';
                        $this->load->library('upload', $config);
                        $this->upload->initialize($config);

                        if ($this->upload->do_upload('source')) {
                            $source = $this->upload->data()['file_name'];
                        } else {
                            $record = $this->page_model->getBannerDetails($insert_id);
                            $source = $record->source;
                            $data['errors'] = $this->upload->display_errors();
                        }

                        $this->page_model->updateBanner($insert_id, $source);
                    }
                }

                if ($action == "edit") {
                    if ($insert_id == "") {
                        $insert_id = $this->uri->segment(4);
                    }
                    $data['banner_details'] = $this->page_model->getBannerDetails($insert_id);
                    $data['button_name'] = "Update";
                    $data['action'] = "edit";
                } elseif ($action == "add") {
                    $data['button_name'] = "Create";
                    $data['action'] = "add";
                }
                $this->load->view('admin/editadd_banner', $data);
                break;
            }
        } else {
            redirect('crayo-admin');
        }
    }

    /**
     * Function to manage Home Page Portfolios.
     *
     * @return view Return the view with the list of portfolios on Home Page.
     */
    public function manage_portfolios()
    {
        $current_page_name = [
            'arabic'  => "إدارة العنوان",
            'english' => "Manage Portfolios"
        ];

        $current_menu_id = 'manage_portfolios';

        $columns = [
            'service_id' => 'Service',
            'sub_service_id' => 'Sub Service',
            'order' => 'Order',
            'is_enabled' => 'Enabled'
        ];

        $subject = 'Banner Details';

        $table_name = 'crayotech_portfolios';

        $edit_link = 'crayo-admin/addedit_portfolio/edit';

        $crud = $this->getGroceryCrudListings(
            $current_page_name,
            $current_menu_id,
            $columns,
            $subject,
            $table_name,
            $edit_link
        );

        $crud->callback_column('service_id', array($this, '_getMenuName'));
        $crud->callback_column('sub_service_id', array($this, '_getMenuName'));
        $crud->callback_column('is_enabled', array($this, '_getEnabledStatus'));

        $output = $crud->render();
        $this->load->view('admin/manage_portfolios', $output);
    }

    /**
     * Function to Add and Edit Home Page Banners.
     *
     * @return view Returns the view to add or update banner.
     */
    public function addedit_portfolio()
    {
        if ($_SESSION['language_id'] == 2 || !isset($_SESSION['language_id'])) {
            $current_page_name = "إدارة الأخبار";
        } else {
            $current_page_name = "Manage Portfolios";
        }
        $this->session->set_userdata(
            [
                'current_menu_id'    => 'manage_portfolios',
                'current_page_name'  => $current_page_name
            ]
        );
        $userTypeId = $this->session->userdata('user_type_id');
        $isLoggedIn = $this->session->userdata('is_logged_in');

        // Upload Background Source Files
        $config['upload_path']   = 'assets/uploads/portfolios';
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['max_size']      = '10000';
        $config['min_width']     = 600;
        $config['min_height']    = 600;
        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if ($isLoggedIn == true) {
            switch ($userTypeId) {
                case 1:
                case 2:
                    redirect('crayo-admin');
                    break;
                case 3:
                    $action = $this->uri->segment(3);
                    $insert_id = '';
                    $data['msg'] = '';
                    $source = '';
                    if ($_POST) {
                        $action = 'edit';

                        switch ($_POST['action']) {
                            case 'add':
                                $data['action'] = $_POST['action'];
                                $data['button_name'] = ucfirst($_POST['action']);

                                $this->form_validation->set_rules('service', 'Service', 'required');
                                $this->form_validation->set_rules('sub_service', 'Sub Service', 'required');
                                $this->form_validation->set_rules('order', 'Order', 'required|numeric');

                                if ($this->form_validation->run() == false) {
                                    return $this->load->view('admin/editadd_portfolio', $data);
                                }

                                if (empty ($_FILES['image']['name'])) {
                                    $data['errors'] = 'Portfolio Image is Required!';
                                    return $this->load->view('admin/editadd_portfolio', $data);
                                } else {
                                    $accepted_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/png'];
                                    if (! in_array($_FILES['image']['type'], $accepted_types)) {
                                        $data['errors'] = 'Invalid Image Type!';
                                        return $this->load->view('admin/editadd_portfolio', $data);
                                    }

                                    list($width, $height, $type, $attr) = getimagesize($_FILES['image']["tmp_name"]);

                                    if ($width < 600) {
                                        $data['errors'] = 'Image Width should be minimum 600px!';
                                        return $this->load->view('admin/editadd_portfolio', $data);
                                    }

                                    if ($height < 600) {
                                        $data['errors'] = 'Image Height should be minimum 600px!';
                                        return $this->load->view('admin/editadd_portfolio', $data);
                                    }

                                    if ($width != $height) {
                                        $data['errors'] = 'Image Width and Height is not same!';
                                        return $this->load->view('admin/editadd_portfolio', $data);
                                    }
                                }

                                if ($this->upload->do_upload('image')) {
                                     $source = $this->upload->data()['file_name'];
                                }

                                $this->page_model->addPortfolio($source);

                                $insert_id = $this->db->insert_id();
                                $data['msg'] = 'Successfully Added Portfolio!';
                                break;

                            case 'edit':
                                $insert_id = $_POST['portfolio_id'];
                                $data['action'] = $_POST['action'];
                                $data['button_name'] = ucfirst($_POST['action']);

                                $this->form_validation->set_rules('service', 'Service', 'required');
                                $this->form_validation->set_rules('sub_service', 'Sub Service', 'required');
                                $this->form_validation->set_rules('order', 'Order', 'required|numeric');

                                if ($this->form_validation->run() == false) {
                                    return $this->load->view('admin/editadd_portfolio', $data);
                                }

                                if (! empty($_FILES['image']['name'])) {
                                    $accepted_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/png'];
                                    if (! in_array($_FILES['image']['type'], $accepted_types)) {
                                        $data['errors'] = 'Invalid Image Type!';
                                        return $this->load->view('admin/editadd_portfolio', $data);
                                    }

                                    list($width, $height, $type, $attr) = getimagesize($_FILES['image']["tmp_name"]);

                                    if ($width < 600) {
                                        $data['errors'] = 'Image Width and Height should be minimum 600px and should be equal!';
                                        return $this->load->view('admin/editadd_portfolio', $data);
                                    }

                                    if ($height < 600) {
                                        $data['errors'] = 'Image Width and Height should be minimum 600px and should be equal!';
                                        return $this->load->view('admin/editadd_portfolio', $data);
                                    }

                                    if ($width != $height) {
                                        $data['errors'] = 'Image Width and Height are not equal!';
                                        return $this->load->view('admin/editadd_portfolio', $data);
                                    }
                                }

                                if ($this->upload->do_upload('image')) {
                                    $source = $this->upload->data()['file_name'];
                                } else {
                                    $record = $this->page_model->getPortfolioDetails($insert_id);
                                    $source = $record->image;
                                }

                                $this->page_model->updatePortfolio($insert_id, $source);
                                $data['msg'] = "Successfully Updated Portfolio!";
                        }
                    }

                    if ($action == "edit") {
                        if ($insert_id == "") {
                            $insert_id = $this->uri->segment(4);
                        }
                        $data['portfolio_details'] = $this->page_model->getPortfolioDetails($insert_id);
                        $data['button_name'] = "Update";
                        $data['action'] = "edit";
                    } elseif ($action == "add") {
                        $data['button_name'] = "Create";
                        $data['action'] = "add";
                    }
                    $this->load->view('admin/editadd_portfolio', $data);
                    break;
            }
        } else {
            redirect('crayo-admin');
        }
    }

    /**
     * Function to manage Clients.
     *
     * @return view Return the view with the list of portfolios on Home Page.
     */
    public function manage_clients()
    {
        $current_page_name = [
            'arabic'  => "إدارة العنوان",
            'english' => "Manage Clients"
        ];

        $current_menu_id = 'manage_clients';

        $columns = [
            'name'        => 'Name',
            'sort_order'  => "Sort Order",
            'is_featured' => "Featured",
            'enable'      => 'Enabled'
        ];

        $subject = 'Client Details';

        $table_name = 'crayotech_clients';

        $edit_link = 'crayo-admin/addedit_client/edit';

        $crud = $this->getGroceryCrudListings(
            $current_page_name,
            $current_menu_id,
            $columns,
            $subject,
            $table_name,
            $edit_link
        );

        $output = $crud->render();
        $this->load->view('admin/manage_clients', $output);
    }

    /**
     * Function to Add and Edit Client Details.
     *
     * @return view Returns the view to add or update client details.
     */
    public function addedit_client()
    {
        if ($_SESSION['language_id'] == 2 || !isset($_SESSION['language_id'])) {
            $current_page_name = "إدارة الأخبار";
        } else {
            $current_page_name = "Manage Customers";
        }
        $this->session->set_userdata([
            'current_menu_id'    => 'manage_customers',
            'current_page_name'  => $current_page_name
        ]);
        $userTypeId = $this->session->userdata('user_type_id');
        $isLoggedIn = $this->session->userdata('is_logged_in');
        if ($isLoggedIn == true) {
            switch ($userTypeId) {
                case 1:
                case 2:
                    redirect('crayo-admin');
                    break;
                case 3:
                    $action = $this->uri->segment(3);
                    $insert_id = '';
                    $data['msg'] = '';
                    $source = '';
                    if ($_POST) {
                        $action = 'edit';
                        switch ($_POST['action']) {
                            case 'add':
                                $data['msg'] = 'Successfully Added Client!';

                                // Upload Background Source Files
                                $config['upload_path']   = 'assets/uploads/clients';
                                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                                $config['max_size']      = '10000';
                                $this->load->library('upload', $config);
                                $this->upload->initialize($config);

                                if ($this->upload->do_upload('source')) {
                                    $source = $this->upload->data()['file_name'];
                                } else {
                                    $data['errors'] = $this->upload->display_errors();
                                }

                                $this->page_model->addClient($source);

                                $insert_id = $this->db->insert_id();
                                break;

                            case 'edit':
                                $insert_id = $_POST['client_id'];
                                $data['msg'] = "Successfully Updated Client Details!";

                                // File Upload
                                $config['upload_path']   = 'assets/uploads/clients';
                                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                                $config['max_size']      = '10000';
                                $this->load->library('upload', $config);
                                $this->upload->initialize($config);

                                if ($this->upload->do_upload('source')) {
                                    $source = $this->upload->data()['file_name'];
                                } else {
                                    $record = $this->page_model->getClientDetails($insert_id);
                                    $source = $record->logo;
                                    $data['errors'] = $this->upload->display_errors();
                                }

                                $this->page_model->updateClient($insert_id, $source);
                        }
                    }

                    if ($action == "edit") {
                        if ($insert_id == "") {
                            $insert_id = $this->uri->segment(4);
                        }
                        $data['client_details'] = $this->page_model->getClientDetails($insert_id);
                        $data['button_name'] = "Update";
                        $data['action'] = "edit";
                    } elseif ($action == "add") {
                        $data['button_name'] = "Create";
                        $data['action'] = "add";
                    }
                    $this->load->view('admin/editadd_client', $data);
                    break;
            }
        } else {
            redirect('crayo-admin');
        }
    }

    /**
     * Function to manage Client Testimonials.
     *
     * @return view Returns the list of Client Testimonials
     */
    public function manage_testimonials()
    {
        $current_page_name = [
            'arabic'  => "إدارة العنوان",
            'english' => "Manage Testimonials"
        ];

        $current_menu_id = 'manage_testimonials';

        $columns = [
            'client_name'  => 'Name',
            'designation'  => 'Designation',
            'sort_order'   => 'Sort Order',
            'is_featured'  => 'Featured',
            'enabled'      => 'Enabled'
        ];

        $subject = 'Client Testimonials';

        $table_name = 'crayotech_testimonials';

        $edit_link = 'crayo-admin/addedit_testimonial/edit';

        $crud = $this->getGroceryCrudListings(
            $current_page_name,
            $current_menu_id,
            $columns,
            $subject,
            $table_name,
            $edit_link
        );

        $output = $crud->render();
        $this->load->view('admin/manage_testimonials', $output);
    }


    /**
     * Function to Add and Update Client Testimonials.
     *
     * @return views Returns the view to add or update client testimonials.
     */
    public function addedit_testimonial()
    {
        if ($_SESSION['language_id'] == 2 || !isset($_SESSION['language_id'])) {
            $current_page_name = "إدارة الأخبار";
        } else {
            $current_page_name = "Manage Testimonials";
        }
        $this->session->set_userdata([
            'current_menu_id'    => 'manage_testimonials',
            'current_page_name'  => $current_page_name
        ]);
        $userTypeId = $this->session->userdata('user_type_id');
        $isLoggedIn = $this->session->userdata('is_logged_in');
        if ($isLoggedIn == true) {
            switch ($userTypeId) {
                case 1:
                case 2:
                    redirect('crayo-admin');
                    break;
                case 3:
                    $action = $this->uri->segment(3);
                    $insert_id = '';
                    $data['msg'] = '';
                    $source = '';
                    if ($_POST) {
                        $action = 'edit';
                        switch ($_POST['action']) {
                            case 'add':
                                $data['msg'] = 'Successfully Added Testimonial!';

                                // Upload Background Source Files
                                $config['upload_path']   = 'assets/uploads/testimonials';
                                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                                $config['max_size']      = '10000';
                                $this->load->library('upload', $config);
                                $this->upload->initialize($config);

                                if ($this->upload->do_upload('source')) {
                                    $source = $this->upload->data()['file_name'];
                                } else {
                                    $data['errors'] = $this->upload->display_errors();
                                }

                                $this->page_model->addTestimonial($source);

                                $insert_id = $this->db->insert_id();
                                break;

                            case 'edit':
                                $insert_id = $_POST['testimonial_id'];
                                $data['msg'] = "Successfully Updated Portfolio!";

                                // File Upload
                                $config['upload_path']   = 'assets/uploads/testimonials';
                                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                                $config['max_size']      = '10000';
                                $this->load->library('upload', $config);
                                $this->upload->initialize($config);

                                if ($this->upload->do_upload('source')) {
                                    $source = $this->upload->data()['file_name'];
                                } else {
                                    $record = $this->page_model->getTestimonialDetails($insert_id);
                                    $source = $record->image;
                                    $data['errors'] = $this->upload->display_errors();
                                }

                                $this->page_model->updateTestimonial($insert_id, $source);
                        }
                    }

                    if ($action == "edit") {
                        if ($insert_id == "") {
                            $insert_id = $this->uri->segment(4);
                        }
                        $data['testimonial_details'] = $this->page_model->getTestimonialDetails($insert_id);
                        $data['button_name'] = "Update";
                        $data['action'] = "edit";
                    } elseif ($action == "add") {
                        $data['button_name'] = "Create";
                        $data['action'] = "add";
                    }
                    $this->load->view('admin/editadd_testimonial', $data);
                    break;
            }
        } else {
            redirect('crayo-admin');
        }
    }

    /**
     * Callback Function of Grocery CRUD.
     * To get menu name corresponding to its Id.
     *
     * @param  mixed $value Value of the Field.
     * @param  object $row Instance of the Row of the Table.
     * @return string Returns the value with wrapped html.
     */
    public function _getMenuName($value, $row)
    {
        $menu_list = $this->page_model->getMenuHash();
        if (array_key_exists($value, $menu_list)) {
            return $menu_list[$value];
        } else {
            return '';
        }
    }

    public function _getEnabledStatus($value, $row)
    {
        if ($value == 1) {
            return 'Yes';
        } else {
            return 'No';
        }
    }

    /**
     * Edit Profile Details.
     *
     * @return view Returns the view with the profile details.
     */
    public function edit_profile()
    {
        if ($_SESSION['language_id'] == 2 || !isset($_SESSION['language_id'])) {
            $current_page_name = "إدارة الأخبار";
        } else {
            $current_page_name = "Manage Profile";
        }
        $this->session->set_userdata([
            'current_menu_id'    => 'manage_admin_profile',
            'current_page_name'  => $current_page_name
        ]);
        $userTypeId = $this->session->userdata('user_type_id');
        $isLoggedIn = $this->session->userdata('is_logged_in');
        if ($isLoggedIn == true) {
            switch ($userTypeId) {
                case 1:
                case 2:
                    redirect('crayo-admin');
                    break;
                case 3:
                    $this->load->view('admin/manage_profile');
                    break;
            }
        } else {
            redirect('crayo-admin');
        }
    }

    /**
     * Function to manage Home Page Banners.
     *
     * @return view Return the view with the list of banners on Home Page.
     */
    public function manage_careers()
    {
        $current_page_name = [
            'arabic'  => "إدارة العنوان",
            'english' => "Manage Careers"
        ];

        $current_menu_id = 'manage_careers';

        $columns = [
            "job_code"     => "Title",
            "job_title_en" => "Description",
            "sort_order"   => "Sort Order",
            "is_active"    => "Active"
        ];

        $subject = 'Career Details';

        $table_name = 'crayotech_careers';

        $edit_link = 'crayo-admin/addedit_career/edit';

        $crud = $this->getGroceryCrudListings(
            $current_page_name,
            $current_menu_id,
            $columns,
            $subject,
            $table_name,
            $edit_link
        );

        $output = $crud->render();
        $this->load->view('admin/manage_careers', $output);
    }

        /**
     * Function to Add and Update Sub Services.
     *
     * @return views Returns the view to add or update sub services.
     */
    public function addedit_career()
    {
        if ($_SESSION['language_id'] == 2 || !isset($_SESSION['language_id'])) {
            $current_page_name = "إدارة الأخبار";
        } else {
            $current_page_name = "Manage Career";
        }
        $this->session->set_userdata([
            'current_menu_id'    => 'manage_careers',
            'current_page_name'  => $current_page_name
        ]);
        $userTypeId = $this->session->userdata('user_type_id');
        $isLoggedIn = $this->session->userdata('is_logged_in');
        if ($isLoggedIn == true) {
            switch ($userTypeId) {
                case 1:
                case 2:
                    redirect('crayo-admin');
                    break;
                case 3:
                    $action = $this->uri->segment(3);
                    $insert_id = '';
                    $data['msg'] = '';
                    $source = '';
                    if ($_POST) {
                        $action = 'edit';
                        switch ($_POST['action']) {
                            case 'add':
                                $data['msg'] = 'Successfully Added Career!';

                                $this->page_model->addCareer();

                                $insert_id = $this->db->insert_id();
                                break;

                            case 'edit':
                                $insert_id = $_POST['job_id'];
                                $data['msg'] = "Successfully Updated Career!";

                                $this->page_model->updateCareer($insert_id);
                        }
                    }

                    if ($action == "edit") {
                        if ($insert_id == "") {
                            $insert_id = $this->uri->segment(4);
                        }
                        $data['career_details'] = $this->page_model->getCareerDetails($insert_id);
                        $data['button_name'] = "Update";
                        $data['action'] = "edit";
                    } elseif ($action == "add") {
                        $data['button_name'] = "Create";
                        $data['action'] = "add";
                    }
                    $this->load->view('admin/editadd_career', $data);
                    break;
            }
        } else {
            redirect('crayo-admin');
        }
    }

    /**
     * Returns Career Section with respect to pagination.
     *
     * @return JSON Returns JSON of career Section.
     */
    public function getCareerBlock()
    {
        $offset = $this->input->post('offset');
        $offset = ($offset - 1) * 7;
        $careers_section = $this->page_model->getActiveCareerOpenings($offset);
        $response = [];
        foreach ($careers_section as $career_section) {
            $temp = [];
            $temp['job_code']           = $career_section->job_code;
            if ($_SESSION['language_id'] == 1) {
                $temp['job_title']   = $career_section->job_title_en;
                $temp['job_description'] = $career_section->job_description_en;
            } else {
                $temp['job_title']   = $career_section->job_title_ar;
                $temp['job_description'] = $career_section->job_description_ar;
            }
            $temp['form_url'] = base_url('crayo-admin/processCareerApplication');
            $response[] = $temp;
        }
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode($response));
    }

    /**
     * Function to handle Enquiry through Contact Form.
     *
     * @return JSON Returns the JSON with the status and message.
     */
    public function processEnquiry()
    {
        // Captcha Verification
        $form_captcha = $this->input->post('captcha_response');
        $captcha_response = json_decode(
        file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".
            $this->config->item('google-recaptcha-secret-key')."&response=".
            $form_captcha ."&remoteip=". $_SERVER['REMOTE_ADDR']),
            true
        );

        if ($captcha_response['success']) {

            // Input Validation
            $this->form_validation->set_rules('sendername', 'Name', 'trim|required|regex_match[/^[A-Za-z\s]+$/]');
            $this->form_validation->set_rules('emailaddress', 'Email', 'trim|required|valid_email');
            $this->form_validation->set_rules('telephone', 'Phone', 'trim|required|numeric');
            $this->form_validation->set_rules('sendertype', 'Type', 'trim|required');
            $this->form_validation->set_rules('sendermessage', 'Project Details', 'trim|required');

            $inserted_enquiry_id = null;

            if ($this->form_validation->run() == false) {
                $message = $this->form_validation->error_array();
                $status_header = 500;
                $type = 'error';
            } else {
                // Insert Record
                $inserted_enquiry_id = $this->page_model->storeEnquiryRecord();

                $message = "You have received a enquiry from <br/><br/>
                            Name: {$this->input->post('sendername')}<br/><br/>
                            Email: {$this->input->post('emailaddress')} <br/><br/>
                            Phone: {$this->input->post('telephone')} <br/><br/>
                            Type: {$this->input->post('sendertype')} <br/><br/>
                            Details: {$this->input->post('sendermessage')}";

                // Send Confirmation Mail
                $this->load->library('email');
                $this->email->initialize(['mailtype' => 'html']);
                $this->email->from('contact@crayotech.com', 'Crayotech - Enquiry');
                $this->email->to($this->config->item('contact-us-address'));
                $this->email->subject('Crayotech - Enquiry');
                $this->email->message($message);
                $this->email->send();
            }

            if (!is_null($inserted_enquiry_id)) {
                $message = "<p class='alert alert-success'>Thank you for your interest in Crayo Tech's services. We will be in touch with you very soon..</p>";
                $status_header = 200;
                $type = 'success';
            }
        } else {
            $message = ['captcha_response' => 'Invalid Captcha!'];
            $status_header = 500;
            $type = 'error';
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header($status_header)
            ->set_output(json_encode(array(
                    'text' => $message,
                    'type' => $type
            )));
    }

    /**
     * Process the Quotations received.
     *
     * @return JSON Returns the json with the status and flag.
     */
    public function processQuotations()
    {
        // Captcha Verification
        $form_captcha = $this->input->post('captcha_response');
        $captcha_response = json_decode(
        file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".
            $this->config->item('google-recaptcha-secret-key')."&response=".
            $form_captcha ."&remoteip=". $_SERVER['REMOTE_ADDR']),
            true
        );

        if ($captcha_response['success']) {

            // Input Validation
            $this->form_validation->set_rules('firstname', 'Name', 'trim|required|alpha');
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
            $this->form_validation->set_rules('phone', 'Phone', 'trim|required|numeric');
            $this->form_validation->set_rules('company', 'Company', 'trim|required|regex_match[/^[A-Za-z\s]+$/]');
            $this->form_validation->set_rules('country', 'Country', 'trim|required');
            $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[255]');

            $inserted_quotation_id = null;

            if ($this->form_validation->run() == false) {
                $message = $this->form_validation->error_array();
                $status_header = 500;
                $type = 'error';
            } else {
                // Insert Record
                $inserted_quotation_id = $this->page_model->storeQuotation();
            }

            if (!is_null($inserted_quotation_id)) {

                if (empty($this->input->post('senderneed'))) {
                    $need = '';
                } else {
                    $need = implode(", ", $this->input->post('senderneed'));
                }

                $message = "You have received a quotation from <br/><br/>
                            Name: {$this->input->post('firstname')} {$this->input->post('lastname')}<br/><br/>
                            Email: {$this->input->post('email')} <br/><br/>
                            Job Title: {$this->input->post('jobtitle')} <br/><br/>
                            Company: {$this->input->post('company')} <br/><br/>
                            Phone: {$this->input->post('phone')} <br/><br/>
                            Country: {$this->input->post('country')} <br/><br/>
                            Looking for: {$this->input->post('looking_for')} <br/><br/>
                            Need: {$need}<br/><br/>
                            Description: {$this->input->post('description')}<br/><br/>
                            Heard From: {$this->input->post('heard_from')}";

                // Send Confirmation Mail
                $this->load->library('email');
                $this->email->initialize(['mailtype' => 'html']);
                $this->email->from('quotations@crayotech.com', 'Crayotech - Request Quote');
                $this->email->to($this->config->item('quotations-email-address'));
                $this->email->subject('Crayotech - Request Quote');
                $this->email->message($message);
                $this->email->send();

                $message = "<p class='alert alert-success'>Thank you for your interest in Crayo Tech's services. We will be in touch with you very soon..</p>";
                $status_header = 200;
                $type = 'success';
            }
        } else {
            $message = "<p class='alert alert-danger'>Invalid Captcha!</p>";
            $status_header = 500;
            $type = 'error';
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header($status_header)
            ->set_output(json_encode(array(
                    'text' => $message,
                    'type' => $type
            )));
    }

    /**
     * Process the Quotations from Horizontal Form.
     *
     * @return JSON Returns the json with the status and flag.
     */
    public function processHorizontalQuotationForm()
    {
    
        $url = $this->input->post('url_link');
        $form_captcha = $this->input->post('g-recaptcha-response');
        $captcha_response = json_decode(
        file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".
            $this->config->item('google-recaptcha-secret-key')."&response=".
            $form_captcha ."&remoteip=". $_SERVER['REMOTE_ADDR']),
            true
        );
        if ($captcha_response['success']) {

            // Input Validation
            $this->form_validation->set_rules('sendername', 'Name', 'trim|required|regex_match[/^[A-Za-z\s]+$/]');
            $this->form_validation->set_rules('emailaddress', 'Email', 'trim|required|valid_email');
            $this->form_validation->set_rules('telephone', 'Phone', 'trim|required|numeric|min_length[9]|max_length[12]');
            $this->form_validation->set_rules('sendertype', 'Type', 'trim|required');

            $inserted_quotation_id = null;

            if ($this->form_validation->run() == false) {
                $message = $this->form_validation->error_array();
                //$message = validation_errors("<p class='alert alert-danger'>", "</p>");
                $status_header = 500;
                $type = 'error';
                $msg = 'please Provide Valid Data';
                $this->session->set_flashdata('message_name', $msg);
                echo "<script>location.href='$url'</script>";
                
            } else {
                // Insert Record
                $inserted_quotation_id = $this->page_model->storeHorizontalQuotation();
            }

            if (!is_null($inserted_quotation_id)) {

                $message = "You have received a quotation from<br/>
                            Name: {$this->input->post('sendername')}<br/>
                            Email: {$this->input->post('emailaddress')}<br/>
                            Phone: {$this->input->post('telephone')} <br/>
                            Looking for: {$this->input->post('sendertype')}";

                // Send Confirmation Mail
                $this->load->library('email');
                $this->email->initialize(['mailtype' => 'html']);
                $this->email->from('quotations@crayotech.com', 'Crayotech Quotations');
                $this->email->to($this->config->item('quotations-email-address'));
                $this->email->subject('Quotation');
                $this->email->message($message);
                $this->email->send();
                $message = 'Thank you for your interest in Crayo Techs services. We will be in touch with you very soon..';
                $status_header = 200;
                $type = 'success';
                $this->session->set_flashdata('message_name', $message);
                echo "<script>location.href='$url'</script>";
                
            }
        } else {
            $this->session->set_flashdata('sendername',$this->input->post('sendername'));
            $this->session->set_flashdata('emailaddress',$this->input->post('emailaddress'));
            $this->session->set_flashdata('telephone',$this->input->post('telephone'));
            $this->session->set_flashdata('sendertype',$this->input->post('sendertype'));
            $this->session->set_flashdata('country-code',$this->input->post('country-code'));
           
            $message = ['captcha_response' => "Invalid Captcha!"];
            $status_header = 500;
            $type = 'error';
            $this->session->set_flashdata('message_name', 'Inavalid Captcha Try again !');
            //echo "<script language='javascript' type='text/javascript'>";
            //echo "alert('Invalid Captcha! Kindly try Again!---')";
            //echo "</script>";
            echo "<script>location.href='$url'</script>";
            
        }
        
    }
    
    /**
     * Process the Career Applications.
     *
     * @return JSON Returns the json with status and message.
     */
    public function processCareerApplication()
    {
        // Captcha Verification
        $form_captcha = $this->input->post('g-recaptcha-response');
        $captcha_response = json_decode(
        file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".
            $this->config->item('google-recaptcha-secret-key')."&response=".
            $form_captcha ."&remoteip=". $_SERVER['REMOTE_ADDR']),
            true
        );

        if ($captcha_response['success']) {

            // Input Validation
            $this->form_validation->set_rules('name', 'Name', 'trim|required|regex_match[/^[A-Za-z\s]+$/]|callback_unique_job_application');
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
            $this->form_validation->set_rules('phone', 'Mobile Number', 'trim|required|numeric');
            $this->form_validation->set_rules('exp', 'Experience', 'trim|required');
            $this->form_validation->set_rules('resume', 'Resume', 'callback_resume_check');

            $inserted_application_id = null;

            if ($this->form_validation->run() == false) {
                $message = $this->form_validation->error_array();
                //$message = validation_errors("<p class='alert alert-danger'>", "</p>");
                $status_header = 500;
                $type = 'error';
            } else {
                // Upload Resume
                $config['upload_path'] = 'assets/uploads/resume';
                $config['allowed_types'] = 'pdf|doc|docx';
                $config['max_size'] = 1000;
                $this->load->library('upload', $config);
                $this->upload->initialize($config);

                if (! $this->upload->do_upload('resume')) {
                    $message = $this->upload->display_errors("<p class='alert alert-danger'>", "</p>");
                    $status_header = 500;
                    $type = 'error';
                } else {
                    $resume_name = $this->upload->data()['file_name'];
                    $inserted_application_id = $this->page_model->storeCareerApplication($resume_name);
                    if (!is_null($inserted_application_id)) {

                        // Send Confirmation Mail

                        $message = "<p>Application to Job: <b>{$this->input->post('job_code')}</b> from<br/><br/>
                        Name: {$this->input->post('name')} <br/><br/>
                        Email Address: {$this->input->post('email')} <br/><br/>
                        Phone: {$this->input->post('phone')} <br/><br/>
                        Experience in Years: {$this->input->post('exp')}
                        ";
                        $this->load->library('email');
                        $this->email->initialize(['mailtype' => 'html']);
                        $this->email->from('careers@crayotech.com', 'Crayotech Job Application - '.$this->input->post('job_code'));
                        $this->email->to($this->config->item('hr-email-address'));
                        $this->email->subject('Crayotech Job Application - '.$this->input->post('job_code'));
                        $this->email->message($message);
                        $this->email->attach(FCPATH.'assets/uploads/resume/'.$resume_name);
                        $this->email->send();

                        $message = "<p class='alert alert-success'>Your Application has been submitted successfully!</p>";
                        $status_header = 200;
                        $type = 'success';
                    }
                }
            }

        } else {
            $message = ['g-recaptcha' => "Invalid Captcha!"];
            $status_header = 500;
            $type = 'error';
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header($status_header)
            ->set_output(json_encode(array(
                    'text' => $message,
                    'type' => $type
            )));
    }

    /**
     * Callback function to Check Resume.
     *
     * @return boolean Returns the validation flag.
     */
    public function resume_check()
    {
        $allowed_extn = ['doc', 'docx', 'pdf'];
        $max_permissible_size = 1000000;
        $flag = true;
        if (empty($_FILES['resume']['name'])) {
            $this->form_validation->set_message('resume_check', 'Resume Not Uploaded!');
            $flag = false;
        } elseif ($_FILES['resume']['size'] > $max_permissible_size) {
            $this->form_validation->set_message('resume_check', 'Uploaded File too big!');
            $flag = false;
        } elseif (!in_array(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION), $allowed_extn)) {
            $this->form_validation->set_message('resume_check', 'Resume should be either in PDF or Word Format!');
            $flag = false;
        }
        return $flag;
    }

    /**
     * Callback function to check verify Unique Application.
     *
     * @return boolean Returns the flag
     */
    public function unique_job_application()
    {
        $record_flag = $this->page_model->isUniqueCareerApplicant();
        if (!$record_flag) {
            $this->form_validation->set_message('unique_job_application', 'You have already applied for this job!');
        }
        return $record_flag;
    }

    /**
     * Function for Admin Dashbaord
     */
    public function dashboard()
    {
        $sess_page = array(
            'current_menu_id'   => 'dashboard',
            'current_page_name' => 'Dashboard',
            'small_page'        => 'Statistics'
        );
        $this->session->set_userdata($sess_page);
        $isLoggedIn  = $this->session->userdata('is_logged_in');
        if ($isLoggedIn == true) {

            // Calculate Bounce Rate
            $bounce_rate_payload = $this->getBounceRateStats();
            $bounce_rate_body = json_decode($bounce_rate_payload->body);
            if ($bounce_rate_body->totalResults > 0) {
                $ga_bounce = $ga_session = 0;
                foreach ($bounce_rate_body->rows as $row) {
                    $ga_bounce  += $row[0];
                    $ga_session += $row[1];
                }
                $bounce_rate = round(($ga_bounce / $ga_session) * 100);
            }

            // Calculate New Visitors
            $new_visitors_payload = $this->getNewVisitorsStats();
            $new_visitors_body    = json_decode($new_visitors_payload->body);
            if ($new_visitors_body->totalResults > 0) {
                foreach ($new_visitors_body->rows as $row) {
                    $new_visitors = $row[0];
                }
            }

            $total_enquiries = $this->admin_model->getEnquiriesCount();
            $total_quotations = $this->admin_model->getQuotationsCount();
            $this->load->view('admin/dashboard', compact('bounce_rate', 'new_visitors', 'total_enquiries', 'total_quotations'));
        } else {
            redirect('crayo-admin');
        }
    }

    public function getQuotationsStats()
    {
        $results = $this->admin_model->getQuotationStats();
        return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(200)
                    ->set_output(json_encode($results));
    }

    /**
     * Get Geographical Site Visitor Statistics
     *
     * @return mixed Returns the site statistics in JSON format.
     */
    public function getSiteTrafficStats()
    {
        if (empty($this->session->tempdata('access_token'))) {
            $access_token = $this->getAccessToken();
        } else {
            $access_token = $this->session->tempdata('access_token');
        }

        $start_date = '7daysAgo';
        $end_date   = 'today';
        if (! empty($this->input->post('start-date'))) {
            $start_date = $this->input->post('start-date');
        }
        if (! empty($this->input->post('end-date'))) {
            $start_date = $this->input->post('end-date');
        }


        $payload = [
            'ids'          => 'ga:162748062',
            'start-date'   => $start_date,
            'end-date'     => $end_date,
            'metrics'      => 'ga:users',
            'dimensions'   => 'ga:countryIsoCode',
            'access_token' => $access_token
        ];

        $this->load->library('PHPRequests');

        $response_payload = Requests::get('https://www.googleapis.com/analytics/v3/data/ga?'.http_build_query($payload));

        return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(200)
                    ->set_output(json_encode($response_payload));
    }

    /**
     * Get Bounce Rate Statistics
     *
     * @return mixed Returns the Bounce Rate Statistics
     */
    public function getBounceRateStats()
    {
        if (empty($this->session->tempdata('access_token'))) {
            $access_token = $this->getAccessToken();
        } else {
            $access_token = $this->session->tempdata('access_token');
        }

        $start_date = '7daysAgo';

        $payload = [
            'ids'          => 'ga:162748062',
            'start-date'   => $start_date,
            'end-date'     => 'today',
            'metrics'      => 'ga:bounces,ga:sessions',
            'access_token' => $access_token
        ];

        $this->load->library('PHPRequests');

        $response_payload = Requests::get('https://www.googleapis.com/analytics/v3/data/ga?'.http_build_query($payload));

        return $response_payload;
    }

    /**
     * Return New Visitors Statistics.
     *
     * @return mixed Returns the New Visitor Statistics as an object.
     */
    public function getNewVisitorsStats()
    {
        if (empty($this->session->tempdata('access_token'))) {
            $access_token = $this->getAccessToken();
        } else {
            $access_token = $this->session->tempdata('access_token');
        }

        $start_date = '7daysAgo';

        $payload = [
            'ids'          => 'ga:162748062',
            'start-date'   => $start_date,
            'end-date'     => 'today',
            'metrics'      => 'ga:newUsers',
            'access_token' => $access_token
        ];

        $this->load->library('PHPRequests');

        $response_payload = Requests::get('https://www.googleapis.com/analytics/v3/data/ga?'.http_build_query($payload));

        return $response_payload;
    }

    /**
     * Get Access Token From Google API
     *
     * @return string Returns the Access Token.
     */
    public function getAccessToken()
    {
        $isLoggedIn  = $this->session->userdata('is_logged_in');
        if ($isLoggedIn == true) {
            $this->load->library('jWT');

            // Obtain Private Key
            $this->db->where('ss_note', 'google-reporting-v4-private-key');
            $this->db->from('crayotech_settings');
            $query = $this->db->get();
            //$privateKey = $query->row()->ss_value;
$privateKey = "-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCkdrZ3Agqck9jF
i0rDqnzoXWvsN7uVLIzIUkZ3H2wiDRJQ8JgjheSYB6p9jM6YioKUTYy9I4alAyp6
iBXUIXJWysbFnNBSz82sg5EYqyDGXN9fdJ9g1bU6SK2wLcKQW3728b4T3kGqbII+
5blhZjoYvEeTi0x4Peu5OfNXvJ1aAdnDTbCU9zUaYD2AItc91d8P6NY68eJCdsxw
QxGY9Ic+JrQE/ZHFl7hKGrSt1T/ZXUOU4utwPVITVqhUNVtmYgOMH1hGvorftBxq
tIQfXGVDAk7hLImgKz/dLydWK/003Yu3+PqFOYPBUPrAthk6aArKN/URv9VReFa8
sJhnzCZBAgMBAAECggEAFSRxtoDbrl9PSa3rcZX0Msb3vck4Yyfz/TieeGfgtcvq
Y99aH47x4R1zLGqykGeV+8ywYAw2HfZ3OoNOExQtp7yvqu3K1iqCrT/Iolw1fWqp
CJNsfb1ba9+1/GUVkwtAGizfm5xB4s/KOp27pCrJIy7pIK2Sqg6DxC8P+mJz9AL+
F4c9oDGEhduU5ezCrY6jFckxgvTDRXuRQh07Ke9A0/eKcacGjrh4D+eFOZUedBsH
krC885WepX9Xpft/89wEXdGsC3taoaRMho6CWnwqeV9utP2SWmuxrdgr2QYf9JCe
d1cAJqHubk2n8tobkoGBVyRx6y90a0zXEi+FeggFdQKBgQDV0RpyHY/KOtyGWjZj
wHiTEI3CJiFoHZmWt1t0es2K4IqFeYn4euAPNwg8+tgszP6PiywQVC3wvJdEEMXi
n6A3vz+SA7m5HHIqtdpGGuEISuEhV2RLQV+tY8wEBL1Rdfg/1ke379nzBGBNXcj0
BD0UuTkrefqgpNKDBDjjOXkfUwKBgQDE6QN5ET4hwOmyi2RdiqIpb59oLz3b4zST
p4JcJeNS7ZEMUxJbpZm06eG5koawvlriakVQsVP0MI2VwH+rW5ZeNC88wvYMaEjl
vSWSPztuyhFQaIBXKlFL/kTKvNjHUKOCo1OgbQS9jMatME1NqrHg7blS4w4NbGeG
Y1bPat41mwKBgFIjOd2MragqZHzYOvym+6rDOTHUJBEzDcVwOpnMcxlemNgdkHr7
+QbfRTLnyhOrzD6oYA0FyYApcpKbPumIGKxRs8IL3FTYMKNnqKo1uSzOVx3PzJ98
RZz+MWl5yKB6iRs83eAzK67Hh5cw5/mVh0FmxieFfFop9xSNkqz3bygxAoGBAILH
m+Xh3NlALjbGO6+g7tkEaOQiVDxhp77Vh1A6XcPrQuXjmpLB5tdwatn8hnaGxwgP
ut/AFtldEfw+8MxXtQP2NtVtS0usS5ZCTNzYIFoR3PQBpBPAaGRHiVr5mCSw2xhd
76HiJj7aa+4E4aLO1vtkCC0T3nZt5EguXC7AdLs3AoGBAIHTw3NMdlreppZ9gYuU
QNrxG/z1USgrDY4qDiTqqiKBcVF5Cn0mFCAq+EOIJxtAr5hQF0mwAGKR1nDZUtmP
2C4ItDpeIKnVFCyk98910Jt4mSoKb/zMrmH+wTGIvJ4XWlY9rMh1vgjnh9lVQgUo
PLno4GgABAiwOqqmgCy0iCLA
-----END PRIVATE KEY-----";

            $issued_time = time();
            $exp_time = $issued_time + 3600;

            $token = [
              "iss"   => "test-crayotech@crayotech-test.iam.gserviceaccount.com",
              "scope" => "https://www.googleapis.com/auth/analytics.readonly",
              "aud"   => "https://www.googleapis.com/oauth2/v4/token",
              "exp"   => $exp_time,
              "iat"   => $issued_time
            ];

            $head = [
                "alg" => "RS256",
                "typ" => "JWT"
            ];

            $jwt = JWT::encode($token, $privateKey, 'RS256', null, $head);

            $this->load->library('PHPRequests');

            $response = Requests::post('https://www.googleapis.com/oauth2/v4/token', [], [
                "grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
                "assertion" => $jwt
            ]);

            $response = json_decode($response->body);

            $this->session->set_tempdata('access_token', $response->access_token, 3600);

            return $access_token = $response->access_token;

        } else {
            return $access_token = '';
        }
    }

    /**
     * Function to Handle Logout
     *
     * @return redirect Redirects the user back to the Login Page
     */
    public function logout()
    {
        $this->session->sess_destroy();
        $this->session->set_flashdata('msg', 'Logged out successfully!');
        redirect('crayo-admin', 'refresh');
    }

    /**
     * Function to toggle Language used in dashboard.
     *
     * @return void
     */
    public function toggleLanguage()
    {
        $lang = $this->input->post('lang');
        if ($lang == 'ar') {
            $this->session->set_userdata('language_id', 2);
        } else {
            $this->session->set_userdata('language_id', 1);
        }
    }


    /**
     * Function to upload Files via ckEditor
     *
     * @return string Returns File Upload Status Message.
     */
    public function roxyUploadHandler()
    {
        $config['upload_path'] = 'assets/uploads/';
        $config['allowed_types'] = 'gif|jpg|png|jpeg|pdf|doc|docx|zip|mp4';
        $config['max_size'] = '10000';
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        foreach ($_FILES as $file) {
            if ($this->upload->do_upload('upload')) {
                echo '<p style="color:green;">Files Uploaded Successfully!</p>';
            } else {
                echo '<p style="color:red;">'.$this->upload->display_errors().'</p>';
            }
        }
    }


    /**
     * Callback Function of Grocery CRUD.
     * To apply right align with direction ltr to phone numbers in arabic locale.
     *
     * @param  mixed $value Value of the Field.
     * @param  object $row Instance of the Row of the Table.
     * @return string Returns the value with wrapped html.
     */
    public function _right_align_phone($value, $row)
    {
        return "<span style=\"float:right;direction:ltr;\">".$value."</span>";
    }

    /**
     * Grocery CRUD User Defined utility function to increase wordwrap
     *
     * @return string Returns the wrapped content
     */
    public function _full_text_en($value, $row)
    {
        return $value = wordwrap($row->events_title, 300, "<br>", true);
    }

    /**
     * Grocery CRUD User Defined utility function to increase wordwrap (Arabic)
     *
     * @return string Returns the wrapped content
     */
    public function _full_text_ar($value, $row)
    {
        return $value = wordwrap($row->events_title_ar, 300, "<br>", true);
    }

    /**
     * Grocery CRUD User Defined utility function to return post type
     *
     * @return string Returns the wrapped content
     */
    public function _type($value, $row)
    {
        if ($row->type == 'revision') {
            return 'Draft';
        } else {
            return 'Published';
        }
    }

    /**
     * Function to manage Enquiries via Contact Form.
     *
     * @return view Returns the view with all the enquiries.
     */
    public function manage_enquiries()
    {
        $current_page_name = [
            'arabic'  => "إدارة الشركاء",
            'english' => "Manage Enquiries"
        ];

        $current_menu_id = 'manage_enquiries';

        $columns = [
            'name'  => 'Name',
            'email' => 'Email',
            'type'  => 'Type',
            'phone' => 'Phone'
        ];

        $subject = 'Enquiry Details';

        $table_name = 'crayotech_enquiries';

        $edit_link = 'crayo-admin/addedit_enquiry';

        $crud = $this->getGroceryCrudListings(
            $current_page_name,
            $current_menu_id,
            $columns,
            $subject,
            $table_name,
            $edit_link
        );
        $crud->add_action('View', '', $edit_link, 'bg-orange fa fa-eye');
        $output = $crud->render();
        $this->load->view('admin/manage_enquiries', $output);
    }

    /**
     * Provide an Interface to list and edit Enquiries that came through contact form.
     *
     * @return view Returns the view with the list of enquiries.
     */
    public function addedit_enquiry()
    {
        $userTypeId = $this->session->userdata('user_type_id');
        $isLoggedIn = $this->session->userdata('is_logged_in');
        if ($isLoggedIn == true) {
            $enquiry_id = $this->uri->segment(3);
            $enquiry_details = $this->page_model->getEnquiry($enquiry_id);
            $action = 'view';
            return $this->load->view('admin/editadd_enquiry', compact('enquiry_details', 'action'));
        } else {
            redirect('crayo-admin');
        }
    }

    /**
     * Get Sub Services of Selected Service.
     *
     * @return mixed Returns the Sub Services as JSON to the AJAX call.
     */
    public function getSubServicesOfService()
    {
        $service_id = $this->input->post('service_id');
        $sub_services = $this->page_model->getSubServicesOfService($service_id);
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode($sub_services));
    }



    /**
     * View the list of submitted quotations.
     *
     * @return view Returns the view with the submitted quotations.
     */
    public function manage_quotations()
    {
        $current_page_name = [
            'arabic'  => "إدارة الشركاء",
            'english' => "Manage Quotations"
        ];

        $current_menu_id = 'manage_quotations';

        $columns = [
            'name'  => 'Name',
            'email' => 'Link',
            'phone' => 'Phone'
        ];

        $subject = 'Quotation Details';

        $table_name = 'crayotech_quotations';

        $edit_link = 'crayo-admin/addedit_quotation';

        $crud = $this->getGroceryCrudListings(
            $current_page_name,
            $current_menu_id,
            $columns,
            $subject,
            $table_name,
            $edit_link
        );

        $output = $crud->render();
        $this->load->view('admin/manage_quotations', $output);
    }

    /**
     * View the Details of the Quotations
     *
     * @return view Returns the view with the quotation details.
     */
    public function addedit_quotation()
    {
        $userTypeId = $this->session->userdata('user_type_id');
        $isLoggedIn = $this->session->userdata('is_logged_in');
        if ($isLoggedIn == true) {
            $quotation_id = $this->uri->segment(3);
            $quotation_details = $this->page_model->getQuotation($quotation_id);
            $action = 'view';
            return $this->load->view('admin/editadd_quotation', compact('quotation_details', 'action'));
        } else {
            redirect('crayo-admin');
        }
    }

    public function ga_test()
    {
        $this->session->set_userdata([
            'current_menu_id'    => 'ga_test',
            'current_page_name'  => 'Analytics'
        ]);

        $userTypeId = $this->session->userdata('user_type_id');
        $isLoggedIn = $this->session->userdata('is_logged_in');

        if ($isLoggedIn == true) {
            $this->load->view('admin/crayotech_analytics');
        } else {
            redirect('crayo-admin');
        }
    }

    /**
     * Function to view all Uploaded Files in Dashboard.
     *
     * @return view Returns the view with all the list of uploaded files.
     */
    public function manage_files()
    {
        if ($_SESSION['language_id'] == 2 || !isset($_SESSION['language_id'])) {
            $current_page_name = "إدارة الملفات";
        } else {
            $current_page_name = "Manage Files";
        }
        $this->session->set_userdata([
            'current_menu_id'    => 'manage_files',
            'current_page_name'  => $current_page_name,
        ]);
        $userTypeId = $this->session->userdata('user_type_id');
        $isLoggedIn = $this->session->userdata('is_logged_in');
        if ($isLoggedIn == true) {
            switch ($userTypeId) {
                case 1:
                case 2:
                    redirect('crayo-admin');
                    break;
                case 3:
                    if ($_SESSION['language_id'] == 2 || !isset($_SESSION['language_id'])) {
                        $crud->set_language("arabic");
                    }
                    $this->load->view('admin/manage_files');
            }
        } else {
            redirect('crayo-admin');
        }
    }

}
