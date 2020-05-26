<?php

namespace Stanford\RepeatingReportRenderer;

/** @var \Stanford\RepeatingReportRenderer\RepeatingReportRenderer $this */

?>
<!--<input type="hidden" id="csv-export-url" value="--><?php //echo $this->getUrl('view/csv_export.php')
?><!--">-->
<!--<input type="hidden" id="csv-export-session" value="">-->
<script src="<?php echo $this->getUrl('asset/js/button.js') ?>"></script>
<script>
    Button.url = "<?php echo $this->getUrl('view/table.php', false, false) ?>"
    Button.report_id = "<?php echo $this->getReportId() ?>"
    Button.project_id = "<?php echo $this->getProjectId() ?>"
    Button.exportURL = "<?php echo $this->getUrl('view/csv_export.php', false, true) ?>"
</script>
