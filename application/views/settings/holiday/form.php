<?php echo $this->config->item('enable_cuti') ? warning_message('<h5 class="font-weight-bold"><i class="fa fa-warning" aria-hidden="true"></i> PERHATIAN!</h5>Cuti Bersama ini akan mengurangi jumlah cuti tahunan semua pegawai bila disimpan.', 'warning', 'text-perhatian collapse small') : '' ?>

<?php $this->load->view('widgets/form') ?>

<script>
    $(document).ready(function() {
        $('#jenis_libur_id').parent().after($('.text-perhatian'));

        if (<?php echo $isNewRecord ? 1 : 0 ?>) {
            $('#jenis_libur_id').on('change', function() {
                if (!this.value || this.value != <?php echo $this->config->item('libur_cuti_bersama_mengurangi_cuti_tahunan') ?>) {
                    $('.text-perhatian').slideUp();
                } else {
                    $('.text-perhatian').slideDown();
                }
            });
        }
    })
</script>
