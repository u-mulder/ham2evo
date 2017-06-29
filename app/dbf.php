<?php
/**
 * Фабрика для генерации подключений к БД
 *
 * @author u_mulder <m264695502@gmail.com>
 */
abstract class DbFactory
{
    /**
     * @var object $dbh Подключение определенного типа
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    protected $dbh;

    /**
     * @var string $last_error Текст последней ошибки
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    protected $last_error;

    /**
     * @var object $results Результаты выполнения запроса
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    protected $results;

    /**
     * @var object $stmt Подготовленный запрос
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    protected $stmt;

    /**
     * Фабричная функция, генерирующая нужный объект-обертку над подключением к БД
     *
     * @param object $params Объект с настройками
     *
     * @return object Объект-обертка над нужным подключением к БД
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public static function init($params)
    {
        $r = null;

        switch ($params->dbDriver) {
            case 'pdo':
                $r = new \PDOWrapper($params->dbPath);
                break;

            /* 'sqlite' is here */
            default:
                $r = new \SQLiteWrapper($params->dbPath);
                break;
        }

        return $r;
    }


    /**
     * Функция выполнения неподготовленного запроса
     *
     * @param string $q Текст запроса
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function query($q)
    {
        $this->results = $this->dbh->query($q);
        if (!$this->results) {
            $this->setError($this->getErrorString());
        }
    }


    /**
     * Функция подготовки запроса
     *
     * @param string $q Текст запроса
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function prepare($q)
    {
        $this->stmt = $this->dbh->prepare($q);
        if (!$this->stmt) {
            $this->setError($this->getErrorString());
        }
    }


    /**
     * Функция выполнения подготовленного запроса
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function executeStmt()
    {
        $r = true;

        $this->results = $this->stmt->execute();
        if (!$this->results) {
            $this->setError($this->getErrorString());
            $r = false;
        }

        return $r;
    }


    /**
     * Функция привязки параметров подготовленного запроса
     *
     * Пока что ошибки данной функции не обрабатываются.
     *
     * @param int $index Индекс параметра
     * @param mixed $value Значение параметра
     * @param string $type Тип данных параметра
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function bindValue($index, $value, $type)
    {
        $this->stmt->bindValue(
            $index,
            $value,
            $this->getValueType($type)
        );

        return $this;
    }


    /**
     * Функция возвращает текст последней ошибки
     *
     * @return string Текст последней ошибки
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function getError()
    {
        return $this->last_error;
    }


    /**
     * Абстрактная функция получения следующего результата
     *
     * @param int $fetchMode Режим получения результата
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    abstract public function fetchResult($fetchMode);


    /**
     * Абстрактная функция получения следующего результата подготовленного выражения
     *
     * @param int $fetchMode Режим получения результата
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    abstract public function fetchStmtResult($fetchMode);

    /**
     * Абстрактная функция получения сообщения об ошибке
     *
     * @param int $fetchMode Режим получения результата
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    abstract public function getErrorString();


    /**
     * Функция установки ошибки при подготовке / выполнении запроса
     *
     * @param string $err_str Текст ошибки
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function setError($err_str)
    {
        $this->last_error = $err_str;
    }


    /**
     * Функция, возвращающая константы типов данных в хависимости от типа подключения
     *
     * @param string $type Original value type
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    abstract public function getValueType($type);


    /**
     * Уничтожаем соединение с БД
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function __destruct()
    {
        $this->dbh = null;
    }
}


/**
 * Класс-обертка над PDO
 *
 * @author u_mulder <m264695502@gmail.com>
 */
class PDOWrapper extends DbFactory
{

    /**
     * Инициализируем `$this->dbh` новым подключением PDO
     *
     * @param string $db_path Путь к sqlite-БД
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function __construct($db_path)
    {
        try {
            $this->dbh = new \PDO('sqlite:' . $db_path);
        } catch (\Exception $e) {
            $this->setError('Ошибка соединения с БД: ' . $e->GetMessage());
        }
    }


    /**
     * Получаем следующий результат запроса
     *
     * @param int $fetchMode Режим получения результата
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function fetchResult($fetchMode = 0)
    {
        $fetchMode = (int)$fetchMode;
        if (!$fetchMode) {
            $fetchMode = \PDO::FETCH_ASSOC;
        }

        return $this->results->fetch($fetchMode);
    }


    /**
     * Получаем следующий результат запроса для подготовленного выражения
     *
     * @param int $fetchMode Режим получения результата
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function fetchStmtResult($fetchMode = 0)
    {
        $fetchMode = (int)$fetchMode;
        if (!$fetchMode) {
            $fetchMode = \PDO::FETCH_ASSOC;
        }

        return $this->stmt->fetch($fetchMode);
    }


    /**
     * Функция получения текста ошибки
     *
     * @return string Текст ошибки или пустая строка
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function getErrorString()
    {
        $err = $this->dbh->errorInfo();
        return !empty($err[2]) ? $err[2] : '';
    }


    /**
     * Функция возвращает сооветствующее значение типа данных
     *
     * @param string $type Тип данных в виде строки
     *
     * @return int Тип данных в виде значения константы класса
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function getValueType($type)
    {
        $r = \PDO::PARAM_STR;
        $type = strtolower(trim($type));

        switch ($type) {
            case "bool":
            case "boolean":
                $r = \PDO::PARAM_BOOL;
                break;

            case "int":
            case "integer":
                $r = \PDO::PARAM_INT;
                break;
        }

        return $r;
    }
}


/**
 * Класс-обертка над SQLite
 *
 * @author u_mulder <m264695502@gmail.com>
 */
class SqliteWrapper extends DbFactory
{

    /**
     * Инициализируем `$this->dbh` новым подключением Sqlite3
     *
     * @param string $db_path Путь к sqlite-БД
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function __construct($db_path)
    {
        try {
            $this->dbh = new \SQLite3($db_path);
        } catch (\Exception $e) {
            $this->setError('Ошибка соединения с БД: ' . $e->GetMessage());
        }
    }


    /**
     * Получаем следующий результат запроса
     *
     * @param int $fetchMode Режим получения результата
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function fetchResult($fetchMode = 0)
    {
        $fetchMode = (int)$fetchMode;
        if (!$fetchMode) {
            $fetchMode = \SQLITE3_ASSOC;
        }

        return $this->results->fetchArray($fetchMode);
    }


    /**
     * Получаем следующий результат запроса подготовленного выражения
     *
     * @param int $fetchMode Режим получения результата
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function fetchStmtResult($fetchMode = 0)
    {
        $fetchMode = (int)$fetchMode;
        if (!$fetchMode) {
            $fetchMode = \SQLITE3_ASSOC;
        }

        return $this->results->fetchArray($fetchMode);
    }


    /**
     * Функция получения текста ошибки
     *
     * @return string Текст ошибки или пустая строка
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function getErrorString()
    {
        return $this->dbh->lastErrorMsg();
    }


    /**
     * Функция возвращает сооветствующее значение типа данных
     *
     * @param string $type Тип данных в виде строки
     *
     * @return int Тип данных в виде значения константы класса
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function getValueType($type)
    {
        $r = \SQLITE3_TEXT;
        $type = strtolower(trim($type));

        switch ($type) {
            case "bool":
            case "boolean":
            case "int":
            case "integer":
                $r = \SQLITE3_INTEGER;
                break;
        }

        return $r;
    }
}
