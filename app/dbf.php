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
    }


    /**
     * Функция выполнения подготовленного запроса
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function executeStmt()
    {
        $r = $this->stmt->execute();

        return $r;
    }


    /**
     * Функция привязки параметров подготовленного запроса
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
     * Абстрактная функция получения ошибки при подготовке / выполнении запроса
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    abstract public function setError();


    /**
     * Функция, возвращающая константы типов данных в хависимости от типа подключения
     *
     * @param string $type Original value type
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    abstract public function getValueType($type);


    /**
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
        $this->dbh = new \PDO('sqlite:' . $db_path);
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
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function setError()
    {
        // TODO
    }


    /**
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
        $this->dbh = new \SQLite3($db_path);
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
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function executeStmt()
    {
        $this->results = $this->stmt->execute();

        return true;    // TODO fix
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
     *
     * @author u_mulder <m264695502@gmail.com>
     */
    public function setError()
    {
        // TODO
    }


    /**
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
