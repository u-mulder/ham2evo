<?php
require_once './app/functions.php';
require_once './app/evoapi.php';
require_once './app/redmineapi.php';

$r = new stdClass;

if (!empty($_POST)) {
    switch (true) {
        case !empty($_POST['config']):
            $errs = saveConfig($_POST['config']);
            $r->success = empty($errs);
            $r->errors = $errs;
            break;

        case !empty($_POST['filter']):
            $r = getRecords($_POST['filter']);
            break;

        case !empty($_POST['evo_records']):
            $params = getConfigParams();
            $ea = new EvoApi($params);

            $r->failed_ids = [];
            foreach ($_POST['evo_records'] as $r_id => $task) {
                $task = json_decode(base64_decode($task), true);
                if ($task) {
                    $subr = $ea->addTask($task);
                    if ($subr) {
                        $r->failed_ids[$r_id] = $subr;
                    }
                } else {
                    $r->failed_ids[$r_id] = 'Отсутствуют данные';
                }
            }
            break;


        case !empty($_POST['redmine_records']):
            $params = getConfigParams();
            $ra = new RedmineApi($params);

            $r->failed_ids = [];
            foreach ($_POST['redmine_records'] as $r_id => $task) {
                $task = json_decode(base64_decode($task), true);
                if ($task) {
                    $subr = $ra->addTimeEntry($task);
                    if ($subr) {
                        $r->failed_ids[$r_id] = $subr;
                    }
                } else {
                    $r->failed_ids[$r_id] = 'Отсутствуют данные';
                }
            }
            break;

        case (!empty($_POST['eId']) && !empty($_POST['tId'])):
            $r = setProjectLookUp($_POST['tId'], $_POST['eId']);
            break;
    }
}

echo json_encode($r);
