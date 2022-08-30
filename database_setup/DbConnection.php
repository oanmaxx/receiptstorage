<?php

require_once ('db_connect.php');

class DbConnection
{
    private $connection = null;

    public function __construct()
    {
    }

    public function connect()
    {
        global $dbServerName, $dbUserName, $dbPassword, $dbName;
        $this->connection = new mysqli($dbServerName, $dbUserName, $dbPassword, $dbName, 3306);

        return empty($this->connection->connect_error);
    }

    public function disconnect()
    {
        $this->connection->close();
    }

    public function lastError()
    {
        return !empty($this->connection->connect_error)
            ? $this->connection->connect_error
            : $this->connection->error;
    }

    public function query($sql)
    {
        return $this->connection->query($sql);
    }

    public function queryPrepared($table, $columns, $queries, $types)
    {
        $colList = implode(", ", $columns);
        $typeList = implode("", $types);
        $whereQry = [];
        $values = [];
        foreach ($queries as $key => $val) {
            $filter = empty($whereQry) ? '' : ' AND';
            $whereQry[] = "$filter $key = ?";
            $values[] = $val;
        }
        $whereQry = implode(",", $whereQry);

        $selectStmt = "SELECT $colList FROM $table WHERE $whereQry";
        $stmt = $this->connection->prepare($selectStmt);
        $stmt->bind_param($typeList, ...$values);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function insertPrepared($table, $columns, $values, $types)
    {
        $colList = implode(", ", $columns);
        $typeList = implode("", $types);
        $valueParam = [];
        foreach ($values as $val) {
            $valueParam[] = "?";
        }
        $valueParam = implode(",", $valueParam);

        $insertStmt = "INSERT INTO $table ($colList) VALUES ($valueParam)";
        $stmt = $this->connection->prepare($insertStmt);
        $stmt->bind_param($typeList, ...$values);
        $stmt->execute();
        $stmt->close();
    }

    public function executePrepared($sql, $values, $typeList)
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param($typeList, ...$values);
        $stmt->execute();
        return $stmt->get_result();
    }
}