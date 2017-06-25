<?php
const CONFIG_FILE_PATH = './config.json';

/**
 * Проверяем наличие конфига и наличие в нем нужных переменных
 *
 * @return bool true - если с фалом все в порядке, в противном случае false
 *
 * @author u_mulder <m264695502@gmail.com>
 */
function checkConfigFile()
{
    $err = '';

    if (!file_exists(CONFIG_FILE_PATH)) {
        $err = 'Файл отсутствует';
    } else {
        $fc = file_get_contents(CONFIG_FILE_PATH);
        if ($fc) {
            $decoded = json_decode($fc, true);
            if ($decoded) {
                $diff_keys = array_diff(
                    array_keys(getConfigKeys()),
                    array_keys($decoded)
                );
                if ($diff_keys) {
                    $err = 'В массиве параметров отсутствуют ключи: '
                        . implode(', ', $diff_keys);
                }
            } else {
                $err = 'Не удается декодировать json';
                if (function_exists('json_last_error_msg')) {
                    $err .= ': ' . json_last_error_msg();
                }
            }
        } else {
            $err = 'Не удается прочитать содержимое файла';
        }
    }

    return $err;
}


/**
 * Спрашиваем параметры и записываем их в конфигурационный файл
 *
 * @return array Массив произошедших ошибок
 *
 * @author u_mulder <m264695502@gmail.com>
 */
function saveConfig($data)
{
    $errs = [];
    $params = [];

    foreach (getConfigKeys() as $k => $v) {
        $value = isset($data[$k]) ? trim($data[$k]) : '';
        if ($value) {
            $params[$k] = $value;
        } else {
            $errs[] = $k;
        }
    }

    if (empty($errs)) {
        $r = file_put_contents(
            CONFIG_FILE_PATH,
            json_encode($params, JSON_PRETTY_PRINT)
        );
        if (!$r) {
            $errs[] = 'Ошибка записи в файл config.json';
        }
    }

    return $errs;
}


/**
 * Получаем список необходимых ключей конфиг-файла
 *
 * @return array
 *
 * @author u_mulder <m264695502@gmail.com>
 */
function getConfigKeys()    // TODO
{
    return [
        'apiBaseUrl' => [
            'caption' => 'API URL',
            'default' => 'sub.doma.in',
        ],
        'apiLogin' => [
            'caption' => 'API Login',
        ],
        'apiPassword' => [
            'caption' => 'API Password',
        ],
        'apiUserID' => [
            'caption' => 'ИД пользователя в EVO',
        ],
        'dbPath' => [
            'caption' => 'Путь к базе данных',
        ],
        //'projectLookup' => '' // TODO
    ];
}


/**
 *
 * @author u_mulder <m264695502@gmail.com>
 */
function getRecords($filter)
{
    $params = getConfigParams();

    $r = new stdClass;
    $r->success = false;
    $r->errors = [];
    $r->records = [];
    $r->hours_total = 0;

    $dbh = null;
    try {
        $dbh = new PDO('sqlite:' . $params->dbPath);
    } catch (\Exception $e) {
        $r->errors[] = 'Ошибка подключения к БД: ' . $e->getMessage();
    }

    if ($dbh) {
        $date_filter = [
            'from' => false,
            'to' => false,
        ];
        try {
            if (!empty($filter['dateFrom'])) {
                $dateFrom = new \DateTime($filter['dateFrom']);
                $date_filter['from'] = $dateFrom->format('Y-m-d 00:00:00');
            }

            if (!empty($filter['dateTo'])) {
                $dateTo = new \DateTime($filter['dateTo']);
                $date_filter['to'] = $dateTo->format('Y-m-d 23:59:59');
            }
        } catch (\Exception $e) {
            $r->errors[] = 'Ошибка распознавания времени: ' . $e->getMessage();
        }

        if (!empty($date_filter)) {
            if (empty($date_filter['to'])) {
                /* Ставим окончание того же дня что и 'from' */
                $date_filter['to'] = $dateFrom->format('Y-m-d 23:59:59');
            } elseif (empty($date_filter['from'])) {
                /* Ставим начало того же дня что и 'to' */
                $date_filter['from'] = $dateTo->format('Y-m-d 00:00:00');
            }

            $q = 'SELECT
                    f.id AS id,
                    f.start_time AS start_time,
                    f.end_time AS end_time,
                    f.description as description,
                    a.name AS name,
                    a.id as activity_id,
                    c.name as category,
                    t.name as tag,
                    t.id as tag_id
                FROM facts f
                LEFT JOIN activities a ON f.activity_id = a.id
                LEFT JOIN categories c ON a.category_id = c.id
                LEFT JOIN fact_tags ft ON ft.fact_id = f.id
                LEFT JOIN tags t ON t.id = ft.tag_id
                WHERE strftime("%s", f.start_time) >= strftime("%s", ?)
                    AND strftime("%s", f.end_time) <= strftime("%s", ?)
                ORDER BY f.start_time ASC';

            // СВЯЗЬ ПРОЕКТОВ из ево!!!

            $stmt = $dbh->prepare($q);
            if ($stmt) {
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                if ($stmt->execute(array_values($date_filter))) {
                    $r->success = true;

                    /* Общий массив задач */
                    $tasks = [];
                    while ($row = $stmt->fetch()) {
                        /* Начальные данные таска */
                        $seconds = strtotime($row['end_time']) - strtotime($row['start_time']);
                        $task_data = [
                            'name' => $row['name'],
                            'date' => date('d.m.Y', strtotime($row['start_time'])),
                            'seconds' => $seconds,
                            'comment' => (string)$row['description'],
                            'project_name' => $row['tag'],   // TODO
                            //'project_id' => $data['project_id'],    // int
                        ];

                        $date = date('d.m.Y', strtotime($row['start_time']));
                        $tag_id = $row['tag_id'];
                        $task_id = getTaskId($row['name']);
                        if (empty($task_id)) {
                            $task_id = 'task-none';
                        }

                        /* Организуем первый уровень по ДАТЕ */
                        if (!isset($tasks[$date])) {
                            $tasks[$date] = [
                                'date' => $date,
                                'items' => [],
                            ];
                        }

                        /* Организуем второй уровень по ИД проекта */
                        if (!isset($tasks[$date]['items'][$tag_id])) {
                            $tasks[$date]['items'][$tag_id] = [];
                        }

                        /* Организуем третий уровень по ИД задачи */
                        if (!isset($tasks[$date]['items'][$tag_id][$task_id])) {
                            $tasks[$date]['items'][$tag_id][$task_id] = $task_data;
                        } else {
                            /**
                             * Если данные по такой задаче в этот день по этому
                             * проекту уже есть, то добавим к ним текущие
                             */
                            if (!empty($task_data['comment'])) {
                                $tasks[$date]['items'][$tag_id][$task_id]['comment'] .= "\n" . $task_data['comment'];
                            }
                            $tasks[$date]['items'][$tag_id][$task_id]['seconds'] += $task_data['seconds'];
                        }
                    }

                    /**
                     * Теперь надо пройтись по каждой задаче в массиве
                     * $task и получить ее длительность в часах. Не
                     * стал получать длительность в часах для каждой
                     * задачи, так как сумма округленных часов может
                     * дать слишком большое значение, что неправильно
                     */
                    foreach ($tasks as $k => $day) {
                        foreach ($day['items'] as $t_id => $tag_tasks) {
                            foreach ($tag_tasks as $task_id => $task_data) {
                                $hours = round($task_data['seconds'] / 3600, 1);
                                $r->hours_total += $hours;
                                $tasks[$k]['items'][$t_id][$task_id]['hours'] = $hours;
                            }
                        }
                    }
                    $r->records = array_values($tasks);
                } else {
                    $err = $stmt->errorInfo();
                    $r->errors[] = 'Ошибка выполнения запроса к БД: ' . $err[2];
                }
            } else {
                $err = $dbh->errorInfo();
                $r->errors[] = 'Ошибка подготовки запроса к БД: ' . $err[2];
            }
        }
    }

    return $r;
}


/**
 * Получаем конфигурационные параметры в виде объекта
 *
 * @return object|false
 *
 * @author u_mulder <m264695502@gmail.com>
 */
function getConfigParams()
{
    $fc = file_get_contents(CONFIG_FILE_PATH);
    $decoded = json_decode($fc);
    return $decoded? $decoded : false;
}


/**
 * Получаем ИД задачи из описания
 *
 * @param string $str
 * @return string
 *
 * @author u_mulder <m264695502@gmail.com>
 */
function getTaskId($str)
{
    $ms = [];
    preg_match('/#\d+/', $str, $ms);

    return !empty($ms[0]) ? $ms[0] : '';
}
