<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Config_Model extends Crud_Model
{
	protected $sipp_is_defined = false;

	public function __construct()
	{
		parent::__construct();
		$this->tableName = $this->config->item('tbl_configs');

		$db_config = array();
		if (file_exists(APPPATH . 'config/' . ENVIRONMENT . '/database.php')) {
			include(APPPATH . 'config/' . ENVIRONMENT . '/database.php');
			if (isset($db)) {
				$db_config = $db;
			}
		} elseif (file_exists(APPPATH . 'config/database.php')) {
			include(APPPATH . 'config/database.php');
			if (isset($db)) {
				$db_config = $db;
			}
		}

		if (isset($db_config['db_sipp']) && !empty($db_config['db_sipp']['hostname'])) {
			$this->sipp_is_defined = true;
		}
	}

	protected function list_query($where, $do_filter = true)
	{
		$this->colSearch = ['key', 'value'];
		$this->colOrder = ['key' => 'asc'];

		$this->db->from($this->tableName);

		parent::list_query($where, $do_filter);
	}

	/**
	 * Fetch merged configs from db_sipp (sys_config) and main DB (tbl_configs).
	 * db_sipp rows come first, main DB rows appended after.
	 *
	 * @return array
	 */
	public function get_all()
	{
		return array_merge(
			$this->_get_sipp_configs(),
			$this->_get_app_configs()
		);
	}

    // -------------------------------------------------------------------------

	/**
	 * @return array
	 */
	private function _get_sipp_configs()
	{
		if (!$this->sipp_is_defined) {
			return array();
		}

		$db = $this->load->database('db_sipp', TRUE);
		if (!$db instanceof CI_DB) {
			return array();
		}

		$query = $db->get('sys_config');

		return $query ? $query->result() : array();
	}

	/**
	 * @return array
	 */
	private function _get_app_configs()
	{
		$table = $this->db->database . '.' . $this->config->item('tbl_configs');
		$query = $this->db
			->order_by('category ASC, key ASC, value ASC')
			->get($table);

		return $query ? $query->result() : array();
	}
}
