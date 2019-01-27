<?php
class DatabaseHandler {
    private $connection;

    function __construct($connection) {
        $this->connection = $connection;
    }

    function setConnection($connection) {
        $this->connection = $connection;
    }
    function getConnection() {
        return $this->connection;
    }
    
    function closeConnection() {
        $this->connection->close();
    }

    function executeSQL($sql) {
        if ($this->connection->query($sql) === TRUE) {
            return "";
        }
        return $this->connection->error;
    }
}
?>