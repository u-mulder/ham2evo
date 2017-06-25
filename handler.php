<?php
require_once './app/functions.php';

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

        case !empty($_POST['records']):
            $params = getConfigParams();
            // TODO
            /*$o = new EvoApi($params);
            foreach ($tasks as $task) {
                $r = $ea->addTask($task);
            }*/
            break;
    }
}

echo json_encode($r);
