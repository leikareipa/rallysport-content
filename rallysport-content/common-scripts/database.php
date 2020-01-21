<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * Provides functionality for interacting with the Rally-Sport Content database.
 * 
 * Usage:
 * 
 *  1. Create a new DatabaseAccess() object: $db = new DatabaseAccess().
 * 
 *  2. Call $db->connect() to establish the database connection.
 * 
 *  3. Perform your actions on the database via $db.
 * 
 *  4. Call $db->disconnect() to close the database connection.
 * 
 */

require_once "return.php";
require_once "resource-id.php";

class DatabaseAccess
{
    // An object returned from mysqli_connect() for accessing the database. Will be
    // initialized by the class constructor.
    private $database;

    // Closes the current connection to the database. Returns true on success;
    // false otherwise.
    function disconnect() : bool
    {
        if ($database)
        {
            return mysqli_close($database);
        }
        else
        {
            return false;
        }
    }

    // Establishes a connection to the database. Returns true on success; false
    // otherwise.
    function connect() : bool
    {
        $databaseCredentials = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../rsc-sql.json"), true);

        if (!$databaseCredentials ||
            !isset($databaseCredentials["host"]) ||
            !isset($databaseCredentials["user"]) ||
            !isset($databaseCredentials["password"]) ||
            !isset($databaseCredentials["database"]))
        {
            return false;
        }

        $this->database = mysqli_connect($databaseCredentials["host"],
                                         $databaseCredentials["user"],
                                         $databaseCredentials["password"],
                                         $databaseCredentials["database"]);

        return (bool)($this->database && !mysqli_connect_error());
    }

    // Adds into the USERS table a new user with the given username and password.
    // The plaintext password will not be entered into the database; instead, it
    // will be ignored once a salted hash has been derived from it, and the hash
    // will be stored instead, along with the salt.
    //
    // Returns TRUE on success; FALSE otherwise.
    //
    function create_new_user(ResourceID $resourceID,
                             string $username,
                             string $plaintextPassword) : bool
    {
        /// TODO: Validate the username and password.

        $passwordHash = password_hash($plaintextPassword, PASSWORD_DEFAULT);

        $databaseReturnValue = $this->issue_db_command(
                                 "INSERT INTO rsc_users" .
                                 " (user_resource_id, username, php_password_hash, account_creation_timestamp, account_exists)" .
                                 " VALUES (?, ?, ?, ?, ?)",
                                 [$resourceID->string(), $username, $passwordHash, time(), 1]);

        return (($databaseReturnValue == 0)? true : false);
    }

    // Wrapper function for sending queries to the database such that data is
    // expected in response. E.g. database_query("SELECT * FROM table WHERE x = ?",
    // [10]) returns such columns' values where x = 10. An empty array may be
    // returned either if there was no data to return or if an error occurred.
    private function issue_db_query(string $queryString, array $parameters): array
    {
        $stmt = mysqli_prepare($this->database, $queryString);

        mysqli_stmt_bind_param($stmt, str_repeat("s", count($parameters)), ...$parameters);

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
    private function issue_db_command(string $commandString, array $parameters): int
    {
        $stmt = mysqli_prepare($this->database, $commandString);

        mysqli_stmt_bind_param($stmt, str_repeat("s", count($parameters)), ...$parameters);

        mysqli_stmt_execute($stmt);

        return mysqli_errno($this->database);
    }
}
