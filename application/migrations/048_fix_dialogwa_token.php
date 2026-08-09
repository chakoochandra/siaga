<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Fix_dialogwa_token extends CI_Migration {

	public function up()
	{
		$this->db->where('key', 'DIALOGWA_TOKEN')
			->where('value', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY1ZjNiMjIyZWY1MmJjMzc4MDYxM2U1OSIsImVtYWlsIjoicGVyZGFuYTFjZWxsQGdtYWlsLmNvbSIsImlhdCI6MTc4NDM1OTEyMywiZXhwIjo0OTQwMTE5MTIzfQ.Du01JZ8vbqHOiKUp_UARBtZH6MVJZQmiIVll1rzXgkw')
			->update('tmst_configs', array('value' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY1ZjNiMjIyZWY1MmJjMzc4MDYxM2U1OSIsImVtYWlsIjoicGVyZGFuYTFjZWxsQGdtYWlsLmNvbSIsImlhdCI6MTc4NDY4NDg1NCwiZXhwIjo0OTQwNDQ0ODU0fQ.RXnHEj3Qx9Mlug-JKfN-pP0N3VW3Jn1NB84A9l8etcU'));
	}

	public function down()
	{
		// $this->db->where('key', 'DIALOGWA_TOKEN')
		// 	->where('value', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY1ZjNiMjIyZWY1MmJjMzc4MDYxM2U1OSIsImVtYWlsIjoicGVyZGFuYTFjZWxsQGdtYWlsLmNvbSIsImlhdCI6MTc4NDY4NDg1NCwiZXhwIjo0OTQwNDQ0ODU0fQ.RXnHEj3Qx9Mlug-JKfN-pP0N3VW3Jn1NB84A9l8etcU')
		// 	->update('tmst_configs', array('value' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY1ZjNiMjIyZWY1MmJjMzc4MDYxM2U1OSIsImVtYWlsIjoicGVyZGFuYTFjZWxsQGdtYWlsLmNvbSIsImlhdCI6MTc4NDM1OTEyMywiZXhwIjo0OTQwMTE5MTIzfQ.Du01JZ8vbqHOiKUp_UARBtZH6MVJZQmiIVll1rzXgkw'));
	}
}
