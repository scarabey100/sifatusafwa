<script type="text/javascript">
    $(document).ready(function() {
        $('#form-opartstat_sessions .panel-heading').click(function() {
            // Vérifie si #table-opartstat_sessions existe
            if ($('#table-opartstat_sessions').length > 0) {
                $('#table-opartstat_sessions').parent().toggle("fast");
            }
            else{
               if ($('#form-opartstat_sessions .table-responsive-row').length > 0) {
                $('#form-opartstat_sessions .table-responsive-row').toggle("fast");
                } 
            }
            
            $(this).toggleClass('open');
        });
    });
</script>
