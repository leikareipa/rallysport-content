<?php namespace RSC\DatabaseConnection;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * Provides functionality for accessing the RSC user database. (Note that
 * for now, the user database is in fact just a table in the general RSC
 * database rather than a database of its own.)
 * 
 * Usage:
 * 
 *  1. Create a new UserDatabase instance: $trackDB = new DatabaseConnection\UserDatabase().
 * 
 *  2. Use the methods provided by the UserDatabase class for manipulating
 *     the contents of the user database.
 * 
 */

require_once __DIR__."/database-connection.php";
require_once __DIR__."/../resource/resource-id.php";

class UserDatabase extends DatabaseConnection
{
    /*
     * The user database is currently a table ("rsc_users") in the general
     * RSC database, to which this class gains access via the DatabaseConnection
     * class.
     * 
     */

    function __construct()
    {
        parent::__construct();
        return;
    }

    // Returns true if the given password is that of the given user; false
    // otherwise.
    function validate_credentials(\RSC\ResourceID $resourceID, string $plaintextPassword) : bool
    {
        if (!$this->is_connected() ||
            !$resourceID)
        {
            return false;
        }

        $userInfo = $this->issue_db_query("SELECT php_password_hash
                                           FROM rsc_users
                                           WHERE resource_id = ?",
                                          [$resourceID->string()]);

        if (!is_array($userInfo) || (count($userInfo) != 1))
        {
            return false;
        }

        return password_verify($plaintextPassword, $userInfo[0]["php_password_hash"]);
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
    function create_new_user(\RSC\ResourceID $resourceID,
                             string $plaintextPassword,
                             string $plaintextEmail) : bool
    {
        if (!$this->is_connected())
        {
            return false;
        }

        /// TODO: Validate the password.

        $passwordHash = password_hash($plaintextPassword, PASSWORD_DEFAULT);
        $emailHash = password_hash($plaintextEmail, PASSWORD_DEFAULT);

        $databaseReturnValue = $this->issue_db_command(
                                 "INSERT INTO rsc_users
                                   (resource_id,
                                    resource_visibility,
                                    php_password_hash,
                                    php_password_hash_email,
                                    creation_timestamp)
                                  VALUES (?, ?, ?, ?, ?)",
                                  [$resourceID->string(),
                                   \RSC\ResourceVisibility::PUBLIC,
                                   $passwordHash,
                                   $emailHash,
                                   time()]);

        return (($databaseReturnValue == 0)? true : false);
    }

    // Returns public information about the given track. If a null resource ID
    // is given, the information of all tracks in the database will be returned.
    // On failure, FALSE is returned.
    function get_user_information(\RSC\ResourceID $resourceID = NULL)
    {
        if (!$this->is_connected())
        {
            return false;
        }

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
            return false;
        }

        // Simplify some parameter names, etc.
        $returnObject = [];
        foreach ($userInfo as $user)
        {
            $returnObject[] =
            [
                "resourceID" => $user["resource_id"],
            ];
        }

        return $returnObject;
    }
}
