<?php

namespace Stanford\RepeatingReportRenderer;

use mysql_xdevapi\Exception;

require_once "emLoggerTrait.php";

define("REPEAT_INSTANCES", "repeat_instances");

/**
 * Class RepeatingReportRenderer
 * @package Stanford\RepeatingReportRenderer
 * @property int $reportId
 * @property array $report
 * @property array $arms
 * @property array $events
 * @property array $instruments
 * @property array $headerColumns
 * @property array $finalData
 * @property int $primaryKey
 * @property \Project $project
 */
class RepeatingReportRenderer extends \ExternalModules\AbstractExternalModule
{


    use emLoggerTrait;

    private $reportId;

    private $report;

    private $arms;

    private $events;

    private $instruments;

    private $headerColumns;

    private $finalData;

    private $primaryKey;

    private $project;


    public function __construct() {
        try {
            parent::__construct();


            if (isset($_GET['report_id']) || isset($_POST['report_id'])) {
                $reportId = isset($_GET['report_id']) ? $_GET['report_id'] : $_POST['report_id'];
                $this->setReport(\REDCap::getReport(filter_var($reportId, FILTER_SANITIZE_NUMBER_INT),
                    'array'));

                $this->setReportId(filter_var($reportId, FILTER_SANITIZE_NUMBER_INT));

                $this->setPrimaryKey(\REDCap::getRecordIdField());
            }

            if (isset($_GET['pid']) || isset($_POST['pid'])) {
                $projectId = isset($_GET['pid']) ? $_GET['pid'] : $_POST['pid'];
                $this->setProject(new \Project(filter_var($projectId, FILTER_SANITIZE_NUMBER_INT)));
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function processReport()
    {
        try {
            if (!$this->getReport()) {
                throw new \Exception("Report does not exist");
            }

            // each element in the report array is redcap record. which can from different arms/events etc...
            foreach ($this->getReport() as $record) {
                $this->processRecord($record);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    private function processRecord($record)
    {

        // this row will contain the non-repeating data to be attached with repeating data.
        $rows = array();

        /**
         * each record will contain two sections. first the non-repeating records demographics etc ...
         * then repeat_instance if exist which will include repeating instances.
         */
        foreach ($record as $eventId => $instance) {


            if ($eventId != REPEAT_INSTANCES) {
                $fields = $this->getInstrumentsFields($eventId);

                $this->processHeaderColumns($fields);

                // we need this because to get the permutation of all non-repeating
                $row = end($rows);
                foreach ($fields as $ins => $fieldArray) {
                    // in case event has repeating instrument we need to skip them
                    if ($this->getProject()->isRepeatingForm($eventId, $ins)) {
                        continue;
                    }

                    foreach ($fieldArray as $field) {
                        // no need to include record_id field
//                        if($field == $this->getPrimaryKey()){
//                            continue;
//                        }

                        if ($this->endsWith($field, '_complete')) {
                            continue;
                        }
                        // if the value does not exist then add it.
                        if ($row && !array_key_exists($field, $row)) {
                            $row[$field] = $instance[$field];

                            $row = $this->addEventTorRow($row, $eventId);
                        } else {

                            // field is repeated over multiple events so we need to delete the event that does not belong here.
                            if ($row) {
                                $eid = $this->getEventIdFromThatHadRepeatedField($field, $eventId);
                                $index = array_search($eid, $row['events']);
                                unset($row['events'][$index]);
                                unset($eid);
                            }
                            // otherwise copy last row and update the repeated value then add it to rows.
                            if (!$tempRow) {
                                $tempRow = $row;
                            }

                            $tempRow[$field] = $instance[$field];
                            $tempRow = $this->addEventTorRow($tempRow, $eventId);
                        }
                    }

                }

                if ($tempRow) {
                    $rows[] = $tempRow;
                    unset($tempRow);
                } else {
                    $rows[count($rows) - 1] = $row;
                }
            } elseif ($eventId == REPEAT_INSTANCES) {
                $this->processRepeatInstances($instance, $rows);
            } elseif (!array_key_exists(REPEAT_INSTANCES, $record)) {
                // todo when no repeat instance exist.
            }

        }
    }

    private function getEventIdFromThatHadRepeatedField($field, $currentEventId)
    {
        $events = $this->getEvents();
        foreach ($events as $id => $event) {
            foreach ($event as $item) {
                if (!is_array($item)) {
                    continue;
                } else {
                    // if the id is not current event and the field in the event instruments.
                    if ($id != $currentEventId && in_array($field, $item)) {
                        return $id;
                    }
                }
            }
        }
    }

    private function addEventTorRow($row, $eventId)
    {
        if (!in_array($eventId, $row['events'])) {
            $row['events'][] = $eventId;
        }
        return $row;
    }

    private function processRepeatInstances($repeatingInstances, $nonRepeatingInstances)
    {
        foreach ($repeatingInstances as $eventId => $instance) {
            $fields = $this->getInstrumentsFields($eventId);

            $this->processHeaderColumns($fields);
            foreach ($fields as $ins => $fieldArray) {


                // now its opposite from above we need only repeating instruments
                if (!$this->getProject()->isRepeatingForm($eventId, $ins)) {
                    continue;
                }

                $rows = array();
                foreach ($fieldArray as $field) {
                    // remove the ones end with complete
                    if ($this->endsWith($field, '_complete')) {
                        continue;
                    }

                    foreach ($instance[$ins] as $key => $item) {
                        $rows[$key][$field] = $item[$field];
                    }
                }

                $finalData = array();
                //now merge non-repeating with repeating
                foreach ($nonRepeatingInstances as $nonrinstance) {
                    // only add if the event id is part of non-repeat event id.
                    if (in_array($eventId, $nonrinstance['events'])) {
                        foreach ($rows as $row) {
                            $finalData[] = array_merge($nonrinstance, $row);
                        }
                    }
                }

                //finally set the resulted records into finaldata to be displayed
                $this->setRepeatingRecordIntoFinal($finalData);
            }
        }
    }

    private function setRepeatingRecordIntoFinal($finalData)
    {
        if ($final = $this->getFinalData()) {
            $final = array_merge($final, $finalData);
            $this->setFinalData($final);
        } else {
            $this->setFinalData($finalData);
        }
    }

    private function processHeaderColumns($fields)
    {
        // add these fields to headerColumns if they do not exist
        $columns = $this->getHeaderColumns();

        foreach ($fields as $ins => $field) {
            if ($columns) {
                $columns = array_merge($columns, $field);
            } else {
                $columns = $field;
            }
        }
        // make sure no duplication
        $columns = array_unique($columns);

        $headerColumns = array();
        foreach ($columns as $column) {
            if ($this->endsWith($column, '_complete')) {
                continue;
            }
            $headerColumns[] = $column;
        }
        $this->setHeaderColumns($headerColumns);
    }

    private function getInstrumentsFields($eventId)
    {
        $instruments = $this->getEventInstruments($eventId);
        $events = $this->getEvents();
        $result = array();
        if ($instruments) {
            foreach ($instruments as $instrument) {
                // if we already processed this instrument and got its fields ignore it. just get the cached fields
                if ($events[$eventId][$instrument]) {
                    $result[$instrument] = $events[$eventId][$instrument];
                    continue;
                } else {
                    $fields = \REDCap::getFieldNames($instrument);
                    $events[$eventId][$instrument] = $result[$instrument] = $fields;
                }
            }
            $this->setEvents($events);
        }
        return $result;
    }

    private function getEventInstruments($eventId)
    {
        $events = $this->getEvents();
        if (!$events[$eventId]) {
            $sql = "SELECT * FROM redcap_events_forms WHERE event_id = $eventId";

            $q = db_query($sql);
            $result = array();
            if (db_num_rows($q) > 0) {

                while ($row = db_fetch_assoc($q)) {
                    $result[] = $row['form_name'];
                }
            }
            $events[$eventId] = $result;
            $this->setEvents($events);
        }
        return $events[$eventId];
    }

    public function redcap_every_page_top(int $project_id)
    {
        if (strpos($_SERVER['SCRIPT_NAME'], 'DataExport/index') !== false) {
            $report = $this->getReport();
            // each element in the report array is redcap record. which can from different arms/events etc...
            foreach ($this->getReport() as $record) {
                $this->processRecord($record);
            }

            $this->includeFile("view/button.php");
        }
    }

    /**
     * @return array
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param array $report
     */
    public function setReport($report)
    {
        $this->report = $report;
    }

    /**
     * @return array
     */
    public function getArms()
    {
        return $this->arms;
    }

    /**
     * @param array $arms
     */
    public function setArms($arms)
    {
        $this->arms = $arms;
    }

    /**
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param array $events
     */
    public function setEvents($events)
    {
        $this->events = $events;
    }

    /**
     * @return array
     */
    public function getInstruments()
    {
        return $this->instruments;
    }

    /**
     * @param array $instruments
     */
    public function setInstruments($instruments)
    {
        $this->instruments = $instruments;
    }

    /**
     * @return array
     */
    public function getHeaderColumns()
    {
        return $this->headerColumns;
    }

    /**
     * @param array $headerColumns
     */
    public function setHeaderColumns($headerColumns)
    {
        $this->headerColumns = $headerColumns;
    }

    /**
     * @return array
     */
    public function getFinalData()
    {
        return $this->finalData;
    }

    /**
     * @param array $finalData
     */
    public function setFinalData($finalData)
    {
        $this->finalData = $finalData;
    }

    /**
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @param int $primaryKey
     */
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;
    }

    /**
     * @return \Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param \Project $project
     */
    public function setProject(\Project $project)
    {
        $this->project = $project;
    }

    /**
     * @param string $path
     */
    public function includeFile($path)
    {
        include_once $path;
    }

    /**
     * @return int
     */
    public function getReportId()
    {
        return $this->reportId;
    }

    /**
     * @param int $reportId
     */
    public function setReportId(int $reportId)
    {
        $this->reportId = $reportId;
    }

    public function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }
}
