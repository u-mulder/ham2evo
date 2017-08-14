<?php
/**
 * Класс для взаимодействия с api Evo
 *
 * @author u_mulder <m264695502@gmail.com>
 */
class EvoApi extends BaseApi
{

    /**
     * @var string Токен авторизации
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    protected $token = '';

    /**
     * Отсылаем в API данные о новом таске
     *
     * @param array $data Данные таска
     *
     * @return string Строка с сообщением об ошибке или пустая строка если все прошло бещ ошибок
     *
     * @see EvoApi::request
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function addTask(array $data)
    {
        $err = '';
        if (empty($this->token)) {
            $this->setToken();
        }

        $data = http_build_query([
            'token' => $this->token,
            'name' => $data['name'],
            'date' => $data['date'],
            'hours' => $data['hours'],
            'comment' => $data['comment'],
            'employee_id' => $this->params->apiUserID,
            'project_id' => $data['project_id'],
        ]);

        $r = $this->request('/api/task', $data, self::REQUEST_TYPE_POST);
        $r = json_decode($r);
        if (is_object($r)) {
            switch ($r->status) {
                case "error":
                    $err = 'Ошибки в переданных данных: ';
                    foreach ($r->errors as $e) {
                        $err .= PHP_EOL . 'ID: '  .  $e->id . ', текст: ' . $e->msg;
                    }
                    break;

                case 'ok':
                    /**
                     * Всё хорошо, в ответе имеется поле `success`,
                     * не знаю может ли оно иметь falsy-значение
                     */
                    break;

                case 'auth_failed':
                    $err = 'Ошибка авторизации';
                    break;

                default:
                    $err = 'Неизвестный статус ответа: '. $r->status;
                    break;
            }
        } else {
            $err = 'Ошибка декодирования ответа API.';
        }

        return $err;
    }


    /**
     * Получаем список текущих проектов в EVO
     *
     * @return object|false Объект со списком проектов или false если что-то пошло не так
     *
     * @see EvoApi::request
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function getProjects()
    {
        if (empty($this->token)) {
            $this->setToken();
        }
        $r = $this->request('/api/project?token=' . $this->token, false, self::REQUEST_TYPE_GET);
        $r = json_decode($r);

        return !empty($r->data) ? $r->data : false;
    }


    /**
     * // TODO - process errors
     *
     * @see EvoApi::request
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function setToken()
    {
        $data = http_build_query([
            'username' => $this->params->apiLogin,
            'password' => $this->params->apiPassword,
        ]);
        $r = $this->request('/api/auth', $data, self::REQUEST_TYPE_POST);
        $r = json_decode($r);

        if (is_object($r)) {
            if (!empty($r->success)) {
                $this->token = $r->token;
            } else {
                // ERROR HERE
            }
        } else {
            // ERROR HERE
        }
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
        $ch = curl_init('https://' . $this->params->apiBaseUrl . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($type == self::REQUEST_TYPE_POST) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $r = curl_exec($ch);
        curl_close($ch);

        return $r;
    }
}
