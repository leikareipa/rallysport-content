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
 *  4. Optionally, call $db->disconnect() to close the database connection.
 * 
 */

require_once "return.php";
require_once "resource-id.php";
require_once "create-zip.php";

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
    // TRUE on success; FALSE otherwise. The 'trackDataZIP' parameter is a string
    // representing the byte data of a zip file containing the track's end-user
    // data (container, manifesto, and HITABLE files).
    function add_new_track(TrackResourceID $resourceID,
                           UserResourceID $creatorID,
                           string $internalName,
                           string $displayName,
                           int $width,
                           int $height,
                           string $containerData,
                           string $manifestoData,
                           string $hitableData) : bool
    {
        /// TODO: Validate the input parameters.

        $trackDataZIP = create_zip_from_file_data(["{$internalName}.DTA"  => $containerData,
                                                   "{$internalName}.\$FT" => $manifestoData,
                                                   "HITABLE.TXT"          => $hitableData],
                                                   $internalName);
        if (!$trackDataZIP)
        {
            return false;
        }

        $trackDataJSON = json_encode([
            "hitable"      => base64_encode($hitableData),
            "container"    => base64_encode($containerData),
            "manifesto"    => $manifestoData,
            "internalName" => $internalName,
            "displayName"  => $displayName,
            "width"        => $width,
            "height"       => $height,
            "contentID"    => $resourceID->string(),
            "creatorID"    => $creatorID->string(),
        ]);

        $databaseReturnValue = $this->issue_db_command(
                                 "INSERT INTO rsc_tracks
                                   (resource_id,
                                    track_name_internal,
                                    track_name_display,
                                    track_width,
                                    track_height,
                                    track_data_zip,
                                    track_data_json,
                                    creation_timestamp,
                                    creator_resource_id)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                                  [$resourceID->string(),
                                   $internalName,
                                   $displayName,
                                   $width,
                                   $height,
                                   $trackDataZIP,
                                   $trackDataJSON,
                                   time(),
                                   "unknown"]);

        return (($databaseReturnValue == 0)? true : false);
    }

    // Adds into the USERS table a new user with the given password. The plaintext
    // password will not be entered into the database; instead, it will be ignored
    // once a salted hash has been derived from it, and the hash will be stored
    // instead, along with the salt.
    //
    // By defalt, each account will be created in a suspended state, where it cannot
    // yet be used to create new content etc. The person who registered the account
    // will go through a separate process of email verification or the like to have
    // the initial suspension lifted.
    //
    // Returns TRUE on success; FALSE otherwise.
    //
    function create_new_user(UserResourceID $resourceID,
                             string $plaintextPassword,
                             string $plaintextEmail) : bool
    {
        /// TODO: Validate the password.

        $passwordHash = password_hash($plaintextPassword, PASSWORD_DEFAULT);
        $emailHash = password_hash($plaintextEmail, PASSWORD_DEFAULT);

        $databaseReturnValue = $this->issue_db_command(
                                 "INSERT INTO rsc_users
                                   (resource_id,
                                    php_password_hash,
                                    php_password_hash_email,
                                    account_creation_timestamp,
                                    account_exists,
                                    account_suspended)
                                  VALUES (?, ?, ?, ?, ?, ?)",
                                  [$resourceID->string(),
                                   $passwordHash,
                                   $emailHash,
                                   time(),
                                   1,
                                   1]);

        return (($databaseReturnValue == 0)? true : false);
    }

    // Returns public information about the given track. If a null resource ID
    // is given, the information of all tracks in the database will be returned.
    function get_user_information(UserResourceID $resourceID = NULL) : array
    {
        // If no resource ID is provided, we'll return info for all tracks
        // in the database.
        $resourceIDRowSelector = ($resourceID? "AND resource_id = ?" : "");

        $userInfo = $this->issue_db_query(
                        "SELECT resource_id
                         FROM rsc_users
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
            $returnObject[$user["resource_id"]] =
            [
            ];
        }

        return $returnObject;
    }

    // Returns public information about the given track. If a null resource ID
    // is given, the information of all tracks in the database will be returned.
    function get_track_public_metadata(TrackResourceID $resourceID = NULL) : array
    {
        // If no resource ID is provided, we'll return info for all tracks
        // in the database.
        $rowSelector = ($resourceID? "WHERE resource_id = ?" : "");

        $trackInfo = $this->issue_db_query(
                        "SELECT
                          resource_id,
                          creator_resource_id,
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
            $returnObject[$track["resource_id"]] =
            [
                "internalName"=>$track["track_name_internal"],
                "displayName"=>$track["track_name_display"],
                "width"=>$track["track_width"],
                "height"=>$track["track_height"],
                "creatorID"=>$track["creator_resource_id"],
                "creationTimestamp"=>$track["creation_timestamp"],
            ];
        }

        return $returnObject;
    }

    // Returns the given track's data as a zip file. The zip file will contain
    // the track's container, manifesto, and HITABLE files; and is thus suitable
    // for serving the track to end-users.
    //
    // On success, the return value will be an array of the following form:
    //
    //   [
    //       "filename": string,
    //       "data": string
    //   ]
    //
    //   - The 'filename' parameter gives the filename associated with the data
    //     (with the .zip extension). Generally, this will match the project's
    //     internal name; e.g. a project called "Suorundi" would have the
    //     filename "SUORUNDI.ZIP".
    //
    //   - The 'data' parameter contains as a string the zip file's raw bytes.
    //
    // On failure, FALSE is returned.
    //
    function get_track_data_as_zip_file(TrackResourceID $resourceID = NULL)
    {
        // For the moment, we can't return multiple tracks' data.
        if (!$resourceID)
        {
            return false;
        }

        $trackZipFile = $this->issue_db_query(
                            "SELECT
                              track_data_zip,
                              track_name_internal
                             FROM rsc_tracks
                             WHERE resource_id = ?",
                            [$resourceID->string()]);

        // We should receive an array with exactly one element: the given
        // track's data.
        if (!is_array($trackZipFile) ||
            (count($trackZipFile) != 1) ||
            !isset($trackZipFile[0]["track_data_zip"]) ||
            !isset($trackZipFile[0]["track_name_internal"]))
        {
            return false;
        }

        return ["filename" => "{$trackZipFile[0]['track_name_internal']}.ZIP",
                "data"     => $trackZipFile[0]["track_data_zip"]];
    }

    function get_track_data_as_json(TrackResourceID $resourceID = NULL)
    {
        // For the moment, we can't return multiple tracks' data.
        if (!$resourceID)
        {
            return false;
        }

        $trackJSON = $this->issue_db_query(
                        "SELECT
                          track_data_json
                         FROM rsc_tracks
                         WHERE resource_id = ?",
                        [$resourceID->string()]);

        // We should receive an array with exactly one element: the given
        // track's data.
        if (!is_array($trackJSON) ||
            (count($trackJSON) != 1) ||
            !isset($trackJSON[0]["track_data_json"]))
        {
            return false;
        }

        return $trackJSON[0]["track_data_json"];
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
