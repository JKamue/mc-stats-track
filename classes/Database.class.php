<?php

/**
 * Class Database
 * Changed Version of Mike's solution:
 * https://stackoverflow.com/a/6743773/10875857
 */

class Database
{

    /** @var PDO */
    private $dbh;
    /** @var PDOStatement */
    private $stmt;

    public function __construct(string $user, string $pass, string $dbname)
    {
        $this->dbh = new PDO(
            "mysql:host=localhost;dbname=$dbname",
            $user,
            $pass,
            array(PDO::ATTR_PERSISTENT => true)
        );
    }

    public function query($query)
    {
        $this->stmt = $this->dbh->prepare($query);
        return $this;
    }

    public function bind($pos, $value, $type = null)
    {

        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }

        $this->stmt->bindValue($pos, $value, $type);
        return $this;
    }

    public function execute()
    {
        $this->stmt->execute();
        print_r($this->stmt->errorInfo());
    }

    public function all()
    {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    public function single()
    {
        $this->execute();
        return $this->stmt->fetch();
    }

    public function count()
    {
        $this->execute();
        return $this->stmt->rowCount();
    }
}