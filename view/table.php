<?php

namespace Stanford\RepeatingReportRenderer;

/** @var \Stanford\RepeatingReportRenderer\RepeatingReportRenderer $module */

try {
    $module->processReport();
//    $module->emLog($module->getHeaderColumns());

    $headers = $module->getHeaderColumns();
    $data = $module->getFinalData();
    # some special cases no header available then we need to get the header from
    if (empty($headers)) {
        $headers = array_keys($data[0]);
    }

    echo json_encode(array(
        'status' => 'success',
        'data' => $data,
        'columns' => $headers,
        'file' => $module->getFileName()
    ));
} catch (\LogicException $e) {
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
} catch (\Exception $e) {
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
}