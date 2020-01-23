<?php namespace RallySportContent;

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
    
    // Adds into the TRACKS table a new track with the given parameters. Returns
    // TRUE on success; FALSE otherwise.
    function add_new_track(TrackResourceID $resourceID,
                           string $internalName,
                           string $displayName,
                           int $width,
                           int $height) : bool
    {
        /// TODO: Validate the input parameters.

        $databaseReturnValue = $this->issue_db_command(
                                 "INSERT INTO rsc_tracks
                                   (track_resource_id,
                                    track_name_internal,
                                    track_name_display,
                                    track_width,
                                    track_height,
                                    creation_timestamp,
                                    creator_user_resource_id)
                                  VALUES (?, ?, ?, ?, ?, ?, ?)",
                                  [$resourceID->string(),
                                   $internalName,
                                   $displayName,
                                   $width,
                                   $height,
                                   time(),
                                   "unknown"]);

        return (($databaseReturnValue == 0)? true : false);
    }

    // Adds into the USERS table a new user with the given username and password.
    // The plaintext password will not be entered into the database; instead, it
    // will be ignored once a salted hash has been derived from it, and the hash
    // will be stored instead, along with the salt.
    //
    // Returns TRUE on success; FALSE otherwise.
    //
    function create_new_user(UserResourceID $resourceID,
                             string $username,
                             string $plaintextPassword) : bool
    {
        /// TODO: Validate the username and password.

        $passwordHash = password_hash($plaintextPassword, PASSWORD_DEFAULT);

        $databaseReturnValue = $this->issue_db_command(
                                 "INSERT INTO rsc_users
                                   (user_resource_id,
                                    username,
                                    php_password_hash,
                                    account_creation_timestamp,
                                    account_exists)
                                  VALUES (?, ?, ?, ?, ?)",
                                  [$resourceID->string(),
                                   $username,
                                   $passwordHash,
                                   time(),
                                   1]);

        return (($databaseReturnValue == 0)? true : false);
    }

    // Returns public information about the given track. If a null resource ID
    // is given, the information of all tracks in the database will be returned.
    function get_user_information(UserResourceID $resourceID = NULL) : array
    {
        // If no resource ID is provided, we'll return info for all tracks
        // in the database.
        $resourceIDRowSelector = ($resourceID? "AND user_resource_id = ?" : "");

        $userInfo = $this->issue_db_query(
                        "SELECT
                          user_resource_id
                         FROM
                          rsc_users
                         WHERE
                          account_suspended = 0
                          AND account_exists = 1
                          {$resourceIDRowSelector}",
                         ($resourceID? [$resourceID->string()] : NULL));

        if (!is_array($userInfo) || !count($userInfo))
        {
            return [];
        }

        // Simplify some parameter names, etc.
        $returnObject = [];
        foreach ($userInfo as $user)
        {
            $returnObject[$user["user_resource_id"]] =
            [
            ];
        }

        return $returnObject;
    }

    // Returns public information about the given track. If a null resource ID
    // is given, the information of all tracks in the database will be returned.
    function get_track_information(TrackResourceID $resourceID = NULL) : array
    {
        // If no resource ID is provided, we'll return info for all tracks
        // in the database.
        $rowSelector = ($resourceID? "WHERE track_resource_id = ?" : "");

        $trackInfo = $this->issue_db_query(
                        "SELECT
                          track_resource_id,
                          creator_user_resource_id,
                          creation_timestamp,
                          track_name_internal,
                          track_name_display,
                          track_width,
                          track_height
                         FROM rsc_tracks
                         {$rowSelector}",
                         ($resourceID? [$resourceID->string()] : NULL));

        if (!is_array($trackInfo) || !count($trackInfo))
        {
            return [];
        }

        // Simplify some parameter names, etc.
        $returnObject = [];
        foreach ($trackInfo as $track)
        {
            $returnObject[$track["track_resource_id"]] =
            [
                "internalName"=>$track["track_name_internal"],
                "displayName"=>$track["track_name_display"],
                "width"=>$track["track_width"],
                "height"=>$track["track_height"],
                "creatorUserID"=>$track["creator_user_resource_id"],
                "creationTimestamp"=>$track["creation_timestamp"],
            ];
        }

        return $returnObject;
    }

    // Wrapper function for sending queries to the database such that data is
    // expected in response. E.g. database_query("SELECT * FROM table WHERE x = ?",
    // [10]) returns such columns' values where x = 10. An empty array may be
    // returned either if there was no data to return or if an error occurred.
    private function issue_db_query(string $queryString, array $parameters = NULL): array
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
    private function issue_db_command(string $commandString, array $parameters): int
    {
        $stmt = mysqli_prepare($this->database, $commandString);

        mysqli_stmt_bind_param($stmt, str_repeat("s", count($parameters)), ...$parameters);

        mysqli_stmt_execute($stmt);

        return mysqli_errno($this->database);
    }
}
