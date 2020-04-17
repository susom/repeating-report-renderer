<?php

namespace Stanford\RepeatingReportRenderer;

/** @var \Stanford\RepeatingReportRenderer\RepeatingReportRenderer $this */

?>
<script src="<?php echo $this->getUrl('asset/js/button.js') ?>"></script>
<script>
    Button.url = "<?php echo $this->getUrl('view/table.php', false, false) ?>"
    Button.report_id = "<?php echo $this->getReportId() ?>"
    Button.project_id = "<?php echo $this->getProjectId() ?>"
</script>
