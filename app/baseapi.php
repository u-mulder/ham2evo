<?php
/**
 * Базовый класс для взаимодействия с api
 *
 * @author u_mulder <m264695502@gmail.com>
 */
class BaseApi
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
     * @param object $params Параметры из конфигурационного файла
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function __construct(stdClass $params)
    {
        $this->params = $params;
    }
}
