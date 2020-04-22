<?php

namespace Stanford\RepeatingReportRenderer;

/** @var \Stanford\RepeatingReportRenderer\RepeatingReportRenderer $module */

try {

    if (!isset($_GET['report_id'])) {
        throw new \LogicException('No report id was provided');
    }

} catch (\LogicException $e) {
    echo '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
} catch (\Exception $e) {
    echo '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
}

?>


<!doctype html>
<html lang="en">
<head>
    <title>PVD Correlation Report</title>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" type="text/css"
          href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"></link>

    <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <!-- DataTable Implementation -->
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
            integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
            crossorigin="anonymous"></script>
    <style>
        body {
            word-wrap: break-word;
        }
    </style>
</head>
<body>

<div id="app" class="container">

    <div class="row p-1">
        <div id="report_parent_div"></div>
    </div>
</div>
<div class="loader"><!-- Place at bottom of page --></div>
<input type="hidden" id="csv-export-url" value="<?php echo $module->getUrl('view/csv_export.php') ?>">
<input type="hidden" id="csv-export-session" value="">
<script src="<?php echo $module->getUrl('asset/js/button.js') ?>"></script>
<script>
    Button.url = "<?php echo $module->getUrl('view/table.php', false, false) ?>"
    Button.report_id = "<?php echo $module->getReportId() ?>"
    Button.project_id = "<?php echo $module->getProjectId() ?>"

    // manually submit this
    Button.submitReport()
</script>
</body>
</html>