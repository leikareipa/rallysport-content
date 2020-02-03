<?php namespace RSC\DatabaseConnection;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * Provides base functionality for interacting with the Rally-Sport Content database.
 * 
 * Usage: You are not expected to use this class directory. Instead, its the derived
 * classes, like TrackDatabaseConnection and UserDatabaseConnection, depending on
 * what type of data you want to access.
 * 
 */

require_once __DIR__."/../response.php";
require_once __DIR__."/../resource-id.php";
require_once __DIR__."/../create-zip.php";

abstract class DatabaseConnection
{
    // An object returned from mysqli_connect() for accessing the database. Will be
    // initialized by the class constructor.
    private $database;

    // Set to true while we're connected to the database.
    private $isConnected;

    // Establishes a connection to the database. Returns true on success; false
    // otherwise.
    public function __construct()
    {
        $this->isConnected = false;
        
        $databaseCredentials = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../rsc-sql.json"), true);

        if (!$databaseCredentials ||
            !isset($databaseCredentials["host"]) ||
            !isset($databaseCredentials["user"]) ||
            !isset($databaseCredentials["password"]) ||
            !isset($databaseCredentials["database"]))
        {
            $this->isConnected = false;
            return;
        }

        $this->database = mysqli_connect($databaseCredentials["host"],
                                         $databaseCredentials["user"],
                                         $databaseCredentials["password"],
                                         $databaseCredentials["database"]);

        $this->isConnected = (bool)($this->database && !mysqli_connect_error());
        return;
    }

    public function is_connected() : bool
    {
        return $this->isConnected;
    }

    // Wrapper function for sending queries to the database such that data is
    // expected in response. E.g. database_query("SELECT * FROM table WHERE x = ?",
    // [10]) returns such columns' values where x = 10. An empty array may be
    // returned either if there was no data to return or if an error occurred.
    protected function issue_db_query(string $queryString, array $parameters = NULL): array
    {
        $stmt = mysqli_prepare($this->database, $queryString);

        if ($parameters)
        {
            mysqli_stmt_bind_param($stmt, str_repeat("s", count($parameters)), ...$parameters);
        }

        $execute = mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (!$stmt || !$execute || !$result || (mysqli_errno($this->database) !== 0))
        {
            return [];
        }

        $returnObject = [];
        while ($row = mysqli_fetch_assoc($result))
        {
            $returnObject[] = $row;
        }

        return $returnObject;
    }

    // Wrapper function for sending queries to the database such that the database
    // is expected to return no data in reponse. E.g. database_command("UPDATE table
    // SET x = ? WHERE id = ?", [1, 2]) modifies the database but returns no data
    // in response.
    //
    // Returns the last error code associated with executing the command; or 0 if
    // no error occurred.
    //
    protected function issue_db_command(string $commandString, array $parameters): int
    {
        $stmt = mysqli_prepare($this->database, $commandString);

        mysqli_stmt_bind_param($stmt, str_repeat("s", count($parameters)), ...$parameters);

        mysqli_stmt_execute($stmt);

        return mysqli_errno($this->database);
    }
}
