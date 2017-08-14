<?php
/**
 * Класс для взаимодействия с api Redmine
 *
 * @author u_mulder <m264695502@gmail.com>
 */
class RedmineApi extends BaseApi
{

    /**
     * @var string Название заголовка для передачи ключа
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    const API_KEY_HEADER_NAME = 'X-Redmine-API-Key';

    /**
     * Добавляем запись о затраченном времени
     *
     * @param array $data данные о времени
     *
     * @return string $err Строка с сообщением об ошибке или пустая строка, если ошибок нет
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function addTimeEntry(array $data)
    {
        $err = '';

        if (!empty($data['rm_issue_id'])) {
            $url = '/time_entries.json';
            $entry_data = [
                'time_entry' => [
                    'issue_id' => $data['rm_issue_id'],
                    /**
                     * The date the time was spent (default to the current date)
                     * Именно в таком формате - Y-m-d
                     */
                    'spent_on' => date('Y-m-d', strtotime($data['date'])),
                    /**
                     * (required) The number of spent hours
                     */
                    'hours' => $data['hours'],
                    /**
                     * Short description for the entry (255 characters max)
                     * Так как в $data['comment'] - номер задачи, то не пишу его в коммент)
                     */
                    'comments' => '',
                ]
            ];
            /**
             * The id of the time activity.
             * This parameter is required unless a default activity is defined in Redmine.
             */
            if (0 < (int)$this->params->redmineActivityId) {
                $entry_data['time_entry']['activity_id'] = $this->params->redmineActivityId;
            }

            $r = $this->request(
                $url,
                http_build_query($entry_data),
                self::REQUEST_TYPE_POST
            );
            $r = json_decode($r);
            if (is_object($r)) {
                if (isset($r->errors)) {
                    $err = 'Ошибки создания записи: ' . implode(PHP_EOL, $r->errors);
                }
            } else {
                $err = 'Неизвестный ответ от сервера';
            }
        } else {
            $err = 'Не удалось получить номер задачи из строки "' . $data['name'] . '"';
        }

        return $err;
    }


    /**
     * Функиця реализует cURL-запрос к API
     *
     * @param string $url URL запроса
     * @param mixed $data Передаваемые данные
     * @param string $type Тип запроса (GET/POST)
     *
     * @return mixed JSON-ответ от API
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    protected function request($url, $data, $type)
    {
        $ch = curl_init('https://' . $this->params->redmineApiBaseUrl . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        /* Специальный заголовок для аутентификации */
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [self::API_KEY_HEADER_NAME . ': ' . $this->params->redmineApiKey]
        );
        if ($type == self::REQUEST_TYPE_POST) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $r = curl_exec($ch);
        curl_close($ch);

        return $r;
    }
}
