<?php
// Models/Model.php

require_once __DIR__ . '/../config.php';

abstract class Model
{
    protected static $db = null;

    public function __construct()
    {
        if (!(self::$db instanceof PDO)) {
            self::$db = config::getConnexion();
        }
    }

    protected function query($sql, array $params = [])
    {
        $statement = self::$db->prepare($sql);
        $statement->execute($params);
        return $statement;
    }

    protected function findAllRecords($table, $orderBy = '')
    {
        $sql = "SELECT * FROM {$table}";

        if ($orderBy !== '') {
            $sql .= " ORDER BY {$orderBy}";
        }

        return $this->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function findRecordById($table, $id)
    {
        $statement = $this->query("SELECT * FROM {$table} WHERE id = ? LIMIT 1", [$id]);
        $record = $statement->fetch(PDO::FETCH_ASSOC);
        return $record ?: null;
    }
}
