<?php
/**
 * Класс для взаимодействия с api Evo
 *
 * @author u_mulder <m264695502@gmail.com>
 */
class EvoApi
{

    /**
     * @var string GET request type
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    const REQUEST_TYPE_GET = 'get';

    /**
     * @var string POST request type
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    const REQUEST_TYPE_POST = 'post';

    /**
     * @var object Параметры для работы с API
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    protected $params;

    /**
     * @var string Токен авторизации
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    protected $token = '';

    /**
     * @param object $params
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function __construct(stdClass $params)
    {
        $this->params = $params;
    }


    /**
     *
     * @param array $data
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function addTask(array $data)    // TODO
    {
        /*if (empty($this->token)) {
            $this->setToken();
        }*/

        $data = http_build_query([
            'token' => $this->token,
            'name' => $data['name'],
            'date' => $data['date'],        // dd.mm.yyyy
            'hours' => $data['hours'],      // float
            'comment' => $data['comment'],
            'employee_id' => $this->params->apiUserID,
            'project_id' => $data['project_id'],    // int
        ]);

        $r = $this->request('/api/task', $data, self::REQUEST_TYPE_POST);
        $r = json_decode($r);

        echo'<pre>',print_r($r),'</pre>';    // TODO
        //[status] => auth_failed


        if (is_object($r)) {
            if ($r->success) {
                // TODO - eveything is OK
            } else {
                $errors = [];
                foreach ($r->errors as $e) {
                    $errors[] = 'ID: '  .  $e->id . ', текст: ' . $e->msg;
                }
            }
        } else {
            // error here // TODO
        }
        die('Dead in ' . __FILE__ . ' on line '. __LINE__ . PHP_EOL);    // TODO

        /*do {

        } while ();*/


        if ($results = '') {
            /*$this->setToken();
            // post again

            /*{
                "success": true,
                "token": "ktxria6yootgmphk3nlqhkiew58nj7q3"
            }

            {
                "status": "auth_failed"
            }

            {
                "status": "internal_error",
                "message": "User Error"
            }*/
        }

        return '100500';
    }


    /**
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
                // ERROR HERE // TODO
            }
        } else {
            // ERROR HERE // TODO
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
        $ch = curl_init('http://' . $this->params->apiBaseUrl . $url);
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
