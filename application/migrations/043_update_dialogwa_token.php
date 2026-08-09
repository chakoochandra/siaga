<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Update_dialogwa_token extends CI_Migration {

	public function up()
	{
		$this->db->where('key', 'DIALOGWA_TOKEN')
			->where('value', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY1ZjNiMjIyZWY1MmJjMzc4MDYxM2U1OSIsInVzZXJuYW1lIjoiY2hhbmRyYSIsImlhdCI6MTcxNzc0Nzc4NywiZXhwIjo0ODczNTA3Nzg3fQ.KIqEs7rELJzVj2hk6WJqCiYy0T0Mz7G5vbiy4gFLRQ0')
			->update('tmst_configs', array('value' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY1ZjNiMjIyZWY1MmJjMzc4MDYxM2U1OSIsImVtYWlsIjoicGVyZGFuYTFjZWxsQGdtYWlsLmNvbSIsImlhdCI6MTc4NDM1OTEyMywiZXhwIjo0OTQwMTE5MTIzfQ.Du01JZ8vbqHOiKUp_UARBtZH6MVJZQmiIVll1rzXgkw'));
	}

	public function down()
	{
		// $this->db->where('key', 'DIALOGWA_TOKEN')
		// 	->where('value', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY1ZjNiMjIyZWY1MmJjMzc4MDYxM2U1OSIsImVtYWlsIjoicGVyZGFuYTFjZWxsQGdtYWlsLmNvbSIsImlhdCI6MTc4NDM1OTEyMywiZXhwIjo0OTQwMTE5MTIzfQ.Du01JZ8vbqHOiKUp_UARBtZH6MVJZQmiIVll1rzXgkw')
		// 	->update('tmst_configs', array('value' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY1ZjNiMjIyZWY1MmJjMzc4MDYxM2U1OSIsInVzZXJuYW1lIjoiY2hhbmRyYSIsImlhdCI6MTcxNzc0Nzc4NywiZXhwIjo0ODczNTA3Nzg3fQ.KIqEs7rELJzVj2hk6WJqCiYy0T0Mz7G5vbiy4gFLRQ0'));
	}
}
