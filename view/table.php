<?php

namespace Stanford\RepeatingReportRenderer;

/** @var \Stanford\RepeatingReportRenderer\RepeatingReportRenderer $module */

try {
    //$module->processReport();
//    $module->emLog($module->getHeaderColumns());
    echo json_encode(array(
        'status' => 'success',
        'data' => $module->getReport(),
        'columns' => array(),
        'file' => ''
    ));
} catch (\LogicException $e) {
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
} catch (\Exception $e) {
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
}