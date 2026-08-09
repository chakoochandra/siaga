<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_update_channel_config extends CI_Migration {

    public function up()
    {
        $this->db->insert('tmst_configs', [
            'key' => 'UPDATE_CHANNEL',
            'value' => 'stable',
            'category' => 5,
            'note' => 'string. channel update: stable, nightly, development'
        ]);
    }

    public function down()
    {
        // $this->db->where('key', 'UPDATE_CHANNEL')->delete('tmst_configs');
    }
}
