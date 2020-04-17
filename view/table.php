<?php

namespace Stanford\RepeatingReportRenderer;

/** @var \Stanford\RepeatingReportRenderer\RepeatingReportRenderer $module */

try {
    $module->processReport();
    echo json_encode(array(
        'status' => 'success',
        'data' => $module->getFinalData(),
        'columns' => $module->getHeaderColumns()
    ));
} catch (\LogicException $e) {
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
} catch (\Exception $e) {
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
}