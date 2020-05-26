<?php

namespace Stanford\RepeatingReportRenderer;

/** @var \Stanford\RepeatingReportRenderer\RepeatingReportRenderer $this */

?>
<!--<input type="hidden" id="csv-export-url" value="--><?php //echo $this->getUrl('view/index.php')
?><!--">-->
<script src="<?php echo $this->getUrl('asset/js/list.js') ?>"></script>
<script>
    List.indexURL = "<?php echo $this->getUrl('view/index.php') ?>"
    List.url = "<?php echo $this->getUrl('view/table.php', false, false) ?>"
    List.report_id = "<?php echo $this->getReportId() ?>"
    List.project_id = "<?php echo $this->getProjectId() ?>"
</script>
