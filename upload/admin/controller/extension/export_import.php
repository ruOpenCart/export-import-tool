<?php
class ControllerExtensionExportImport extends Controller { 
	private $error = array();
	private $ssl = 'SSL';

	public function __construct( $registry ) {
		parent::__construct( $registry );
		$this->ssl = true;
	}


	public function index() {
		$this->load->language('extension/export_import');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/export_import');
		$this->getForm();
	}


	public function upload() {
		$this->load->language('extension/export_import');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/export_import');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validateUploadForm())) {
			if ((isset( $this->request->files['upload'] )) && (is_uploaded_file($this->request->files['upload']['tmp_name']))) {
				$file = $this->request->files['upload']['tmp_name'];
				$incremental = ($this->request->post['incremental']) ? true : false;
				if ($this->model_extension_export_import->upload($file,$this->request->post['incremental'])==true) {
					$this->session->data['success'] = $this->language->get('text_success');
					$this->response->redirect($this->url->link('extension/export_import', 'user_token=' . $this->session->data['user_token'], $this->ssl));
				}
				else {
					$this->session->data['warning'] = $this->language->get('error_upload');
					$href = $this->url->link( 'tool/log', 'user_token='.$this->session->data['user_token'], $this->ssl );
					$this->session->data['warning'] .= "<br />\n".str_replace('%1',$href,$this->language->get( 'text_log_details_3_x' ));
					$this->response->redirect($this->url->link('extension/export_import', 'user_token=' . $this->session->data['user_token'], $this->ssl));
				}
			}
		}

		$this->getForm();
	}


	protected function return_bytes($val)
	{
		$val = trim($val);
	
		switch (strtolower(substr($val, -1)))
		{
			case 'm': $val = (int)substr($val, 0, -1) * 1048576; break;
			case 'k': $val = (int)substr($val, 0, -1) * 1024; break;
			case 'g': $val = (int)substr($val, 0, -1) * 1073741824; break;
			case 'b':
				switch (strtolower(substr($val, -2, 1)))
				{
					case 'm': $val = (int)substr($val, 0, -2) * 1048576; break;
					case 'k': $val = (int)substr($val, 0, -2) * 1024; break;
					case 'g': $val = (int)substr($val, 0, -2) * 1073741824; break;
					default : break;
				} break;
			default: break;
		}
		return $val;
	}


	public function download() {
		$this->load->language( 'extension/export_import' );
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model( 'extension/export_import' );
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateDownloadForm()) {
			$export_type = $this->request->post['export_type'];
			switch ($export_type) {
				case 'c':
				case 'p':
				case 'u':
					$min = null;
					if (isset( $this->request->post['min'] ) && ($this->request->post['min']!='')) {
						$min = $this->request->post['min'];
					}
					$max = null;
					if (isset( $this->request->post['max'] ) && ($this->request->post['max']!='')) {
						$max = $this->request->post['max'];
					}
					if (($min==null) || ($max==null)) {
						$this->model_extension_export_import->download($export_type, null, null, null, null);
					} else if ($this->request->post['range_type'] == 'id') {
						$this->model_extension_export_import->download($export_type, null, null, $min, $max);
					} else {
						$this->model_extension_export_import->download($export_type, $min*($max-1-1), $min, null, null);
					}
					break;
				case 'o':
					$this->model_extension_export_import->download('o', null, null, null, null);
					break;
				case 'a':
					$this->model_extension_export_import->download('a', null, null, null, null);
					break;
				case 'f':
					if ($this->model_extension_export_import->existFilter()) {
						$this->model_extension_export_import->download('f', null, null, null, null);
						break;
					}
					break;
				default:
					break;
			}
			$this->response->redirect( $this->url->link( 'extension/export_import', 'user_token='.$this->request->get['user_token'], $this->ssl) );
		}

		$this->getForm();
	}


	public function settings() {
		$this->load->language('extension/export_import');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/export_import');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validateSettingsForm())) {
			if (!isset($this->request->post['export_import_settings_use_export_cache'])) {
				$this->request->post['export_import_settings_use_export_cache'] = '0';
			}
			if (!isset($this->request->post['export_import_settings_use_import_cache'])) {
				$this->request->post['export_import_settings_use_import_cache'] = '0';
			}
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('export_import', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success_settings');
			$this->response->redirect($this->url->link('extension/export_import', 'user_token=' . $this->session->data['user_token'], $this->ssl));
		}
		$this->getForm();
	}


	protected function getForm() {
		$data = array();
		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['exist_filter'] = $this->model_extension_export_import->existFilter();

		$data['text_export_type_category'] = ($data['exist_filter']) ? $this->language->get('text_export_type_category') : $this->language->get('text_export_type_category_old');
		$data['text_export_type_product'] = ($data['exist_filter']) ? $this->language->get('text_export_type_product') : $this->language->get('text_export_type_product_old');
		$data['text_export_type_poa'] = $this->language->get('text_export_type_poa');
		$data['text_export_type_option'] = $this->language->get('text_export_type_option');
		$data['text_export_type_attribute'] = $this->language->get('text_export_type_attribute');
		$data['text_export_type_filter'] = $this->language->get('text_export_type_filter');
		$data['text_export_type_customer'] = $this->language->get('text_export_type_customer');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_loading_notifications'] = $this->language->get( 'text_loading_notifications' );
		$data['text_retry'] = $this->language->get('text_retry');
		$data['text_used_category_ids'] = $this->language->get('text_used_category_ids');
		$data['text_used_product_ids'] = $this->language->get('text_used_product_ids');

		$data['entry_export'] = $this->language->get( 'entry_export' );
		$data['entry_import'] = $this->language->get( 'entry_import' );
		$data['entry_export_type'] = $this->language->get( 'entry_export_type' );
		$data['entry_range_type'] = $this->language->get( 'entry_range_type' );
		$data['entry_category_filter'] = $this->language->get( 'entry_category_filter' );
		$data['entry_category'] = $this->language->get( 'entry_category' );
		$data['entry_start_id'] = $this->language->get( 'entry_start_id' );
		$data['entry_start_index'] = $this->language->get( 'entry_start_index' );
		$data['entry_end_id'] = $this->language->get( 'entry_end_id' );
		$data['entry_end_index'] = $this->language->get( 'entry_end_index' );
		$data['entry_incremental'] = $this->language->get( 'entry_incremental' );
		$data['entry_upload'] = $this->language->get( 'entry_upload' );
		$data['entry_settings_use_option_id'] = $this->language->get( 'entry_settings_use_option_id' );
		$data['entry_settings_use_option_value_id'] = $this->language->get( 'entry_settings_use_option_value_id' );
		$data['entry_settings_use_attribute_group_id'] = $this->language->get( 'entry_settings_use_attribute_group_id' );
		$data['entry_settings_use_attribute_id'] = $this->language->get( 'entry_settings_use_attribute_id' );
		$data['entry_settings_use_filter_group_id'] = $this->language->get( 'entry_settings_use_filter_group_id' );
		$data['entry_settings_use_filter_id'] = $this->language->get( 'entry_settings_use_filter_id' );
		$data['entry_settings_use_export_cache'] = $this->language->get( 'entry_settings_use_export_cache' );
		$data['entry_settings_use_import_cache'] = $this->language->get( 'entry_settings_use_import_cache' );

		$data['tab_export'] = $this->language->get( 'tab_export' );
		$data['tab_import'] = $this->language->get( 'tab_import' );
		$data['tab_settings'] = $this->language->get( 'tab_settings' );

		$data['button_export'] = $this->language->get( 'button_export' );
		$data['button_import'] = $this->language->get( 'button_import' );
		$data['button_settings'] = $this->language->get( 'button_settings' );
		$data['button_export_id'] = $this->language->get( 'button_export_id' );
		$data['button_export_page'] = $this->language->get( 'button_export_page' );

		$data['help_range_type'] = $this->language->get( 'help_range_type' );
		$data['help_category_filter'] = $this->language->get( 'help_category_filter' );
		$data['help_incremental_yes'] = $this->language->get( 'help_incremental_yes' );
		$data['help_incremental_no'] = $this->language->get( 'help_incremental_no' );
		$data['help_import'] = ($data['exist_filter']) ? $this->language->get( 'help_import' ) : $this->language->get( 'help_import_old' );
		$data['help_format'] = $this->language->get( 'help_format' );

		$data['error_select_file'] = $this->language->get('error_select_file');
		$data['error_post_max_size'] = str_replace( '%1', ini_get('post_max_size'), $this->language->get('error_post_max_size') );
		$data['error_upload_max_filesize'] = str_replace( '%1', ini_get('upload_max_filesize'), $this->language->get('error_upload_max_filesize') );
		$data['error_id_no_data'] = $this->language->get('error_id_no_data');
		$data['error_page_no_data'] = $this->language->get('error_page_no_data');
		$data['error_param_not_number'] = $this->language->get('error_param_not_number');
		$data['error_notifications'] = $this->language->get('error_notifications');
		$data['error_no_news'] = $this->language->get('error_no_news');
		$data['error_batch_number'] = $this->language->get('error_batch_number');
		$data['error_min_item_id'] = $this->language->get('error_min_item_id');

		if (!empty($this->session->data['export_import_error']['errstr'])) {
			$this->error['warning'] = $this->session->data['export_import_error']['errstr'];
		} else if (isset($this->session->data['warning'])) {
			$this->error['warning'] = $this->session->data['warning'];
		}

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
			if (!empty($this->session->data['export_import_nochange'])) {
				$data['error_warning'] .= "<br />\n".$this->language->get( 'text_nochange' );
			}
		} else {
			$data['error_warning'] = '';
		}

		unset($this->session->data['warning']);
		unset($this->session->data['export_import_error']);
		unset($this->session->data['export_import_nochange']);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], $this->ssl)
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/export_import', 'user_token=' . $this->session->data['user_token'], $this->ssl)
		);

		$data['back'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], $this->ssl);
		$data['button_back'] = $this->language->get( 'button_back' );
		$data['import'] = $this->url->link('extension/export_import/upload', 'user_token=' . $this->session->data['user_token'], $this->ssl);
		$data['export'] = $this->url->link('extension/export_import/download', 'user_token=' . $this->session->data['user_token'], $this->ssl);
		$data['settings'] = $this->url->link('extension/export_import/settings', 'user_token=' . $this->session->data['user_token'], $this->ssl);
		$data['post_max_size'] = $this->return_bytes( ini_get('post_max_size') );
		$data['upload_max_filesize'] = $this->return_bytes( ini_get('upload_max_filesize') );

		if (isset($this->request->post['export_type'])) {
			$data['export_type'] = $this->request->post['export_type'];
		} else {
			$data['export_type'] = 'p';
		}

		if (isset($this->request->post['range_type'])) {
			$data['range_type'] = $this->request->post['range_type'];
		} else {
			$data['range_type'] = 'id';
		}

		if (isset($this->request->post['min'])) {
			$data['min'] = $this->request->post['min'];
		} else {
			$data['min'] = '';
		}

		if (isset($this->request->post['max'])) {
			$data['max'] = $this->request->post['max'];
		} else {
			$data['max'] = '';
		}

		if (isset($this->request->post['incremental'])) {
			$data['incremental'] = $this->request->post['incremental'];
		} else {
			$data['incremental'] = '1';
		}

		if (isset($this->request->post['export_import_settings_use_option_id'])) {
			$data['settings_use_option_id'] = $this->request->post['export_import_settings_use_option_id'];
		} else if ($this->config->get( 'export_import_settings_use_option_id' )) {
			$data['settings_use_option_id'] = '1';
		} else {
			$data['settings_use_option_id'] = '0';
		}

		if (isset($this->request->post['export_import_settings_use_option_value_id'])) {
			$data['settings_use_option_value_id'] = $this->request->post['export_import_settings_use_option_value_id'];
		} else if ($this->config->get( 'export_import_settings_use_option_value_id' )) {
			$data['settings_use_option_value_id'] = '1';
		} else {
			$data['settings_use_option_value_id'] = '0';
		}

		if (isset($this->request->post['export_import_settings_use_attribute_group_id'])) {
			$data['settings_use_attribute_group_id'] = $this->request->post['export_import_settings_use_attribute_group_id'];
		} else if ($this->config->get( 'export_import_settings_use_attribute_group_id' )) {
			$data['settings_use_attribute_group_id'] = '1';
		} else {
			$data['settings_use_attribute_group_id'] = '0';
		}

		if (isset($this->request->post['export_import_settings_use_attribute_id'])) {
			$data['settings_use_attribute_id'] = $this->request->post['export_import_settings_use_attribute_id'];
		} else if ($this->config->get( 'export_import_settings_use_attribute_id' )) {
			$data['settings_use_attribute_id'] = '1';
		} else {
			$data['settings_use_attribute_id'] = '0';
		}

		if (isset($this->request->post['export_import_settings_use_filter_group_id'])) {
			$data['settings_use_filter_group_id'] = $this->request->post['export_import_settings_use_filter_group_id'];
		} else if ($this->config->get( 'export_import_settings_use_filter_group_id' )) {
			$data['settings_use_filter_group_id'] = '1';
		} else {
			$data['settings_use_filter_group_id'] = '0';
		}

		if (isset($this->request->post['export_import_settings_use_filter_id'])) {
			$data['settings_use_filter_id'] = $this->request->post['export_import_settings_use_filter_id'];
		} else if ($this->config->get( 'export_import_settings_use_filter_id' )) {
			$data['settings_use_filter_id'] = '1';
		} else {
			$data['settings_use_filter_id'] = '0';
		}

		if (isset($this->request->post['export_import_settings_use_export_cache'])) {
			$data['settings_use_export_cache'] = $this->request->post['export_import_settings_use_export_cache'];
		} else if ($this->config->get( 'export_import_settings_use_export_cache' )) {
			$data['settings_use_export_cache'] = '1';
		} else {
			$data['settings_use_export_cache'] = '0';
		}

		if (isset($this->request->post['export_import_settings_use_import_cache'])) {
			$data['settings_use_import_cache'] = $this->request->post['export_import_settings_use_import_cache'];
		} else if ($this->config->get( 'export_import_settings_use_import_cache' )) {
			$data['settings_use_import_cache'] = '1';
		} else {
			$data['settings_use_import_cache'] = '0';
		}

		$data['categories'] = array();

		$min_product_id = $this->model_extension_export_import->getMinProductId();
		$max_product_id = $this->model_extension_export_import->getMaxProductId();
		$count_product = $this->model_extension_export_import->getCountProduct();
		$min_category_id = $this->model_extension_export_import->getMinCategoryId();
		$max_category_id = $this->model_extension_export_import->getMaxCategoryId();
		$count_category = $this->model_extension_export_import->getCountCategory();
		$min_customer_id = $this->model_extension_export_import->getMinCustomerId();
		$max_customer_id = $this->model_extension_export_import->getMaxCustomerId();
		$count_customer = $this->model_extension_export_import->getCountCustomer();
		
		$data['text_used_category_ids'] = str_replace('%1',$min_category_id,$data['text_used_category_ids']);
		$data['text_used_category_ids'] = str_replace('%2',$max_category_id,$data['text_used_category_ids']);
		$data['text_used_product_ids'] = str_replace('%1',$min_product_id,$data['text_used_product_ids']);
		$data['text_used_product_ids'] = str_replace('%2',$max_product_id,$data['text_used_product_ids']);

		$data['min_product_id'] = $min_product_id;
		$data['max_product_id'] = $max_product_id;
		$data['count_product'] = $count_product;
		$data['min_category_id'] = $min_category_id;
		$data['max_category_id'] = $max_category_id;
		$data['count_category'] = $count_category;
		$data['min_customer_id'] = $min_customer_id;
		$data['max_customer_id'] = $max_customer_id;
		$data['count_customer'] = $count_customer;

		$data['user_token'] = $this->session->data['user_token'];

		$this->document->addStyle('view/stylesheet/export_import.css');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view( 'extension/export_import', $data));
	}


	protected function validateDownloadForm() {
		if (!$this->user->hasPermission('access', 'extension/export_import')) {
			$this->error['warning'] = $this->language->get('error_permission');
			return false;
		}

		if (!$this->config->get( 'export_import_settings_use_option_id' )) {
			$option_names = $this->model_extension_export_import->getOptionNameCounts();
			foreach ($option_names as $option_name) {
				if ($option_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $option_name['name'], $this->language->get( 'error_option_name' ) );
					return false;
				}
			}
		}

		if (!$this->config->get( 'export_import_settings_use_option_value_id' )) {
			$option_value_names = $this->model_extension_export_import->getOptionValueNameCounts();
			foreach ($option_value_names as $option_value_name) {
				if ($option_value_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $option_value_name['name'], $this->language->get( 'error_option_value_name' ) );
					return false;
				}
			}
		}

		if (!$this->config->get( 'export_import_settings_use_attribute_group_id' )) {
			$attribute_group_names = $this->model_extension_export_import->getAttributeGroupNameCounts();
			foreach ($attribute_group_names as $attribute_group_name) {
				if ($attribute_group_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $attribute_group_name['name'], $this->language->get( 'error_attribute_group_name' ) );
					return false;
				}
			}
		}

		if (!$this->config->get( 'export_import_settings_use_attribute_id' )) {
			$attribute_names = $this->model_extension_export_import->getAttributeNameCounts();
			foreach ($attribute_names as $attribute_name) {
				if ($attribute_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $attribute_name['name'], $this->language->get( 'error_attribute_name' ) );
					return false;
				}
			}
		}

		if (!$this->config->get( 'export_import_settings_use_filter_group_id' )) {
			$filter_group_names = $this->model_extension_export_import->getFilterGroupNameCounts();
			foreach ($filter_group_names as $filter_group_name) {
				if ($filter_group_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $filter_group_name['name'], $this->language->get( 'error_filter_group_name' ) );
					return false;
				}
			}
		}

		if (!$this->config->get( 'export_import_settings_use_filter_id' )) {
			$filter_names = $this->model_extension_export_import->getFilterNameCounts();
			foreach ($filter_names as $filter_name) {
				if ($filter_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $filter_name['name'], $this->language->get( 'error_filter_name' ) );
					return false;
				}
			}
		}

		return true;
	}


	protected function validateUploadForm() {
		if (!$this->user->hasPermission('modify', 'extension/export_import')) {
			$this->error['warning'] = $this->language->get('error_permission');
		} else if (!isset( $this->request->post['incremental'] )) {
			$this->error['warning'] = $this->language->get( 'error_incremental' );
		} else if ($this->request->post['incremental'] != '0') {
			if ($this->request->post['incremental'] != '1') {
				$this->error['warning'] = $this->language->get( 'error_incremental' );
			}
		}

		if (!isset($this->request->files['upload']['name'])) {
			if (isset($this->error['warning'])) {
				$this->error['warning'] .= "<br /\n" . $this->language->get( 'error_upload_name' );
			} else {
				$this->error['warning'] = $this->language->get( 'error_upload_name' );
			}
		} else {
			$ext = strtolower(pathinfo($this->request->files['upload']['name'], PATHINFO_EXTENSION));
			if (($ext != 'xls') && ($ext != 'xlsx') && ($ext != 'ods')) {
				if (isset($this->error['warning'])) {
					$this->error['warning'] .= "<br /\n" . $this->language->get( 'error_upload_ext' );
				} else {
					$this->error['warning'] = $this->language->get( 'error_upload_ext' );
				}
			}
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}


	protected function validateSettingsForm() {
		if (!$this->user->hasPermission('access', 'extension/export_import')) {
			$this->error['warning'] = $this->language->get('error_permission');
			return false;
		}

		if (empty($this->request->post['export_import_settings_use_option_id'])) {
			$option_names = $this->model_extension_export_import->getOptionNameCounts();
			foreach ($option_names as $option_name) {
				if ($option_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $option_name['name'], $this->language->get( 'error_option_name' ) );
					return false;
				}
			}
		}

		if (empty($this->request->post['export_import_settings_use_option_value_id'])) {
			$option_value_names = $this->model_extension_export_import->getOptionValueNameCounts();
			foreach ($option_value_names as $option_value_name) {
				if ($option_value_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $option_value_name['name'], $this->language->get( 'error_option_value_name' ) );
					return false;
				}
			}
		}

		if (empty($this->request->post['export_import_settings_use_attribute_group_id'])) {
			$attribute_group_names = $this->model_extension_export_import->getAttributeGroupNameCounts();
			foreach ($attribute_group_names as $attribute_group_name) {
				if ($attribute_group_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $attribute_group_name['name'], $this->language->get( 'error_attribute_group_name' ) );
					return false;
				}
			}
		}

		if (empty($this->request->post['export_import_settings_use_attribute_id'])) {
			$attribute_names = $this->model_extension_export_import->getAttributeNameCounts();
			foreach ($attribute_names as $attribute_name) {
				if ($attribute_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $attribute_name['name'], $this->language->get( 'error_attribute_name' ) );
					return false;
				}
			}
		}

		if (empty($this->request->post['export_import_settings_use_filter_group_id'])) {
			$filter_group_names = $this->model_extension_export_import->getFilterGroupNameCounts();
			foreach ($filter_group_names as $filter_group_name) {
				if ($filter_group_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $filter_group_name['name'], $this->language->get( 'error_filter_group_name' ) );
					return false;
				}
			}
		}

		if (empty($this->request->post['export_import_settings_use_filter_id'])) {
			$filter_names = $this->model_extension_export_import->getFilterNameCounts();
			foreach ($filter_names as $filter_name) {
				if ($filter_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $filter_name['name'], $this->language->get( 'error_filter_name' ) );
					return false;
				}
			}
		}

		return true;
	}


	public function getNotifications() {
		sleep(1); // give the data some "feel" that its not in our system
		$this->load->model('extension/export_import');
		$this->load->language( 'extension/export_import' );
		$response = $this->model_extension_export_import->getNotifications();
		$json = array();
		if ($response===false) {
			$json['message'] = '';
			$json['error'] = $this->language->get( 'error_notifications' );
		} else {
			$json['message'] = $response;
			$json['error'] = '';
		}
		$this->response->setOutput(json_encode($json));
	}


	public function getCountProduct() {
		$this->load->model('extension/export_import');
		$count = $this->model_extension_export_import->getCountProduct();
		$json = array( 'count'=>$count );
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
?>