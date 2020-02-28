<?php namespace RSC\DatabaseConnection;
      use RSC\Resource;

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
 *  1. Create a new UserDatabase instance: $userDB = new DatabaseConnection\UserDatabase().
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

    // Utility function for creating a hash of the given password that can be
    // stored in the user database.
    public static function generate_hash_of_user_password(string $plaintextPassword) : string
    {
        return password_hash($plaintextPassword, PASSWORD_DEFAULT);
    }

    // Utility function for creating a hash of the given email address that can
    // be stored in the user database. On error, returns NULL. Note that we
    // apply a stable salt (pepper) to the email before hashing - a random salt
    // would be used, but Rally-Sport Content wants to be able to tell whether
    // two hashed emails are duplicates of each other.
    public static function generate_hash_of_user_email_address(string $plaintextEmail) : string
    {
        $dbCredentials = json_decode(file_get_contents(self::database_credentials_filename()), true);

        if (!is_array($dbCredentials))
        {
            return NULL;
        }

        $pepper = ($dbCredentials["pepper"] ?? NULL);

        // Some sanity checks to ensure we have a reasonable pepper string.
        if (!is_string($pepper) ||
            strlen($pepper) < 20)
        {
            return NULL;
        }

        return hash("sha256", ($pepper . $plaintextEmail));
    }

    // Returns the count of users in the database; or FALSE on error. The
    // 'visibilityLevels' array provides ResourceVisibility elements such that
    // only users whose visibility level is one of these will be included in
    // the count.
    public function users_count(array $visibilityLevels = [Resource\ResourceVisibility::PUBLIC]) : int
    {
        // Assert that we received valid parameter values.
        {
            foreach ($visibilityLevels as $visibilityLevel)
            {
                if (!Resource\ResourceVisibility::is_valid_visibility_level($visibilityLevel))
                {
                    return false;
                }
            }
        }

        $resourceVisibilityConditional = empty($visibilityLevels)
                                         ? "1"
                                         : "resource_visibility IN ('".implode("','", $visibilityLevels)."')";

        $dbResponse = $this->issue_db_query("SELECT COUNT(*)
                                             FROM rsc_users
                                             WHERE {$resourceVisibilityConditional}");

        
        if (!is_array($dbResponse) ||
            !count($dbResponse) ||
            !isset($dbResponse[0]["COUNT(*)"]))
        {
            return false;
        }
                                                
        return $dbResponse[0]["COUNT(*)"];
    }

    // Returns TRUE if the given user's account is active, i.e. not deleted or
    // in some other way disabled; FALSE otherwise.
    public function is_active_user_account(Resource\UserResourceID $resourceID) : bool
    {
        if (!$this->is_connected() ||
            !$resourceID)
        {
            return false;
        }

        $userInfo = $this->issue_db_query("SELECT COUNT(*)
                                           FROM rsc_users
                                           WHERE resource_id = ?
                                           AND resource_visibility = ?",
                                          [$resourceID->string(),
                                           Resource\ResourceVisibility::PUBLIC]);

        if (!is_array($userInfo) ||
            !count($userInfo) ||
            !isset($userInfo[0]["COUNT(*)"]))
        {
            return false;
        }

        return (($userInfo[0]["COUNT(*)"] == 0)? false : true);
    }

    // Resets the password of the user whose current reset token matches the
    // one given. The reset token will have been generated when the user
    // requested a password reset. Returns TRUE on success; FALSE otherwise.
    public function reset_user_password(string $email,
                                        string $resetToken,
                                        string $newPassword) : bool
    {
        if (!$this->is_connected())
        {
            return false;
        }

        $userInfo = $this->issue_db_query("SELECT resource_id,
                                                  email_hash_sha256,
                                                  password_reset_token,
                                                  password_reset_token_expires
                                           FROM rsc_users
                                           WHERE password_reset_token = ?",
                                          [$resetToken]);

        if (!is_array($userInfo) || (count($userInfo) != 1))
        {
            return false;
        }

        if (!isset($userInfo[0]["resource_id"]) ||
            !isset($userInfo[0]["email_hash_sha256"]) ||
            !isset($userInfo[0]["password_reset_token"]) ||
            !isset($userInfo[0]["password_reset_token_expires"]))
        {
            return false;
        }

        // If the password reset info for this user hasn't been set - which
        // would indicate the user hasn't requested a password reset.
        if (($userInfo[0]["password_reset_token"] === NULL) ||
            ($userInfo[0]["password_reset_token_expires"] === NULL))
        {
            return false;
        }

        $emailHash = self::generate_hash_of_user_email_address($email);

        if (!$emailHash)
        {
            return false;
        }

        if (!hash_equals($userInfo[0]["email_hash_sha256"], $emailHash) ||
            !hash_equals($userInfo[0]["password_reset_token"], $resetToken))
        {
            return false;
        }

        if (time() > $userInfo[0]["password_reset_token_expires"])
        {
            return false;
        }

        // Reset the user's password.
        $dbResponse = $this->issue_db_command("UPDATE rsc_users
                                               SET password_hash_php = ?,
                                                   password_reset_token = NULL,
                                                   password_reset_token_expires = NULL
                                               WHERE resource_id = ?",
                                              [password_hash($newPassword, PASSWORD_DEFAULT),
                                               $userInfo[0]["resource_id"]]);

        // If the DB query failed.
        if ($dbResponse !== 0)
        {
            return false;
        }

        return true;
    }

    // Generates a random string of characters that can be used to request
    // the resetting of the user's password. This will both return a token
    // and prepare the user's database entry for the password reset. The
    // token is returned as an array of the form ["value"=>..., "expires"=>...],
    // where 'value' is a random code with which the password can be reset,
    // and 'expires' provides a timestamp for when the token's code is no
    // longer valid.
    public function generate_token_for_password_reset(Resource\UserResourceID $userResourceID)
    {
        if (!$this->is_connected())
        {
            return NULL;
        }

        $token = [
            "value"   => bin2hex(random_bytes(32)),
            "expires" => (time() + (24 * 60 * 60)) // Expires in 24 hours.
        ];

        $dbResponse = $this->issue_db_command("UPDATE rsc_users
                                               SET password_reset_token = ?,
                                                   password_reset_token_expires = ?
                                               WHERE resource_id = ?",
                                              [$token["value"],
                                               $token["expires"],
                                               $userResourceID->string()]);

        // If the DB query failed.
        if ($dbResponse !== 0)
        {
            return NULL;
        }

        return $token;
    }

    // Returns true if the given email matches the email of the given user;
    // FALSE otherwise.
    public function is_correct_user_email(string $email, Resource\UserResourceID $userResourceID)
    {
        if (!$this->is_connected())
        {
            return false;
        }

        $userInfo = $this->issue_db_query("SELECT email_hash_sha256
                                           FROM rsc_users
                                           WHERE resource_id = ?",
                                          [$userResourceID->string()]);

        if (!is_array($userInfo) || !count($userInfo))
        {
            return false;
        }

        if (!isset($userInfo[0]["email_hash_sha256"]))
        {
            return NULL;
        }

        $emailHash = self::generate_hash_of_user_email_address($email);

        if (!$emailHash)
        {
            return false;
        }

        if (!hash_equals($userInfo[0]["email_hash_sha256"], $emailHash))
        {
            return false;
        }

        return true;
    }

    // Returns the user ID associated with the given email and password (both
    // given in plaintext); or NULL on error.
    public function get_user_id_with_credentials(string $email, string $password)
    {
        if (!$this->is_connected())
        {
            return NULL;
        }

        $emailHash = self::generate_hash_of_user_email_address($email);

        if (!$emailHash)
        {
            return false;
        }

        $userInfo = $this->issue_db_query("SELECT password_hash_php,
                                                  resource_id
                                           FROM rsc_users
                                           WHERE email_hash_sha256 = ?",
                                          [$emailHash]);

        if (!is_array($userInfo) || (count($userInfo) != 1))
        {
            return NULL;
        }

        if (!isset($userInfo[0]["password_hash_php"]) ||
            !isset($userInfo[0]["resource_id"]))
        {
            return NULL;
        }

        if (!password_verify($password, $userInfo[0]["password_hash_php"]))
        {
            return NULL;
        }

        return Resource\UserResourceID::from_string($userInfo[0]["resource_id"]);
    }

    // Returns TRUE if a user account has not yet been registered using the
    // given hash; FALSE otherwise. Note that FALSE will also be returned if
    // an error is encountered.
    public function is_resource_hash_unique(string $resourceHash) : bool
    {
        if (!$this->is_connected())
        {
            return false;
        }

        $dbResponse = $this->issue_db_query("SELECT COUNT(*)
                                             FROM rsc_users
                                             WHERE resource_data_hash_sha256 = ?",
                                            [$resourceHash]);

        if (!is_array($dbResponse) ||
            !count($dbResponse) ||
            !isset($dbResponse[0]["COUNT(*)"]))
        {
            return false;
        }

        return (($dbResponse[0]["COUNT(*)"] == 0)? true : false);
    }

    // Returns true if the given peppered email hash matches such a hash of
    // a previously-added user.
    public function is_email_hash_unique(string $emailHashPepperedSHA256) : bool
    {
        if (!$this->is_connected())
        {
            return false;
        }

        $dbResponse = $this->issue_db_query("SELECT COUNT(*)
                                             FROM rsc_users
                                             WHERE email_hash_sha256 = ?",
                                            [$emailHashPepperedSHA256]);

        if (!is_array($dbResponse) ||
            !count($dbResponse) ||
            !isset($dbResponse[0]["COUNT(*)"]))
        {
            return false;
        }

        return (($dbResponse[0]["COUNT(*)"] == 0)? true : false);
    }

    // Adds into the USERS table a new user with the given password and email.
    // Note that each new user requires a unique registration hash to be provided;
    // it's assumed here that the uniqueness of that hash has already been
    // verified by the caller.
    //
    // Returns TRUE on success; FALSE otherwise.
    //
    public function create_new_user(Resource\UserResourceID $resourceID,
                                    string $plaintextPassword,
                                    string $plaintextEmail,
                                    string $resourceHash) : bool
    {
        if (!$this->is_connected())
        {
            return false;
        }

        $passwordHash = self::generate_hash_of_user_password($plaintextPassword);
        $emailHash = self::generate_hash_of_user_email_address($plaintextEmail);

        if (!$passwordHash ||
            !$emailHash)
        {
            return false;
        }

        /// TODO: If the email hash isn't unique, the database won't accept this
        /// user and an error will be returned. But ideally, the user would be
        /// told that the email address is a duplicate, rather than being given
        /// a generic error from which the case can't easily be deduced. We
        /// could do this e.g. with return error codes.

        $databaseReturnValue = $this->issue_db_command("INSERT INTO rsc_users
                                                         (resource_id,
                                                          resource_visibility,
                                                          resource_data_hash_sha256,
                                                          password_hash_php,
                                                          email_hash_sha256,
                                                          creation_timestamp)
                                                        VALUES (?, ?, ?, ?, ?, ?)",
                                                       [$resourceID->string(),
                                                        Resource\ResourceVisibility::PUBLIC,
                                                        $resourceHash,
                                                        $passwordHash,
                                                        $emailHash,
                                                        time()]);

        return (($databaseReturnValue == 0)? true : false);
    }

    // Returns one or more user resources as an array of UserResource elements;
    // or FALSE on error. The users will be sorted by creation date in descending
    // order.
    //
    // $count = defines the number of users to return at most (if 0, all will
    // be returned).
    //
    // $offset = sets the starting offset in the full list of users from which
    // to extract the desired number of users.
    //
    // $visibilityLevels = an array of ResourceVisibility elements such that
    // only users whose visibility level is one of these will be included in
    // the return array.
    //
    // $userIDs = an array of user ID strings such that if non-empty, only
    // users whose resource ID matches one of these strings will be included
    // in the return array.
    //
    // $sort = sets the property by which to sort the users; but can only be
    // "timestamp" at this time.
    //
    public function get_users(int $count = 0,
                              int $offset = 0,
                              array $visibilityLevels = [Resource\ResourceVisibility::PUBLIC],
                              array $userIDs = [],
                              string $sort = "timestamp")
    {
        // Assert that we received valid parameter values.
        {
            if (($offset < 0) ||
                ($count < 0))
            {
                return false;
            }

            // We only support sorting by timestamp, for now.
            if ($sort !== "timestamp")
            {
                return false;
            }

            foreach ($visibilityLevels as $visibilityLevel)
            {
                if (!Resource\ResourceVisibility::is_valid_visibility_level($visibilityLevel))
                {
                    return false;
                }
            }

            foreach ($userIDs as $userIDString)
            {
                if (!Resource\UserResourceID::from_string($userIDString))
                {
                    return false;
                }
            }
        }

        $resourceVisibilityConditional = empty($visibilityLevels)
                                         ? "1"
                                         : "resource_visibility IN ('".implode("','", $visibilityLevels)."')";

        $userIDConditional = empty($userIDs)
                             ? "1"
                             : "resource_id IN ('".implode("','", $userIDs)."')";

        // A count of 0 will return all matching users.
        $limitConditional = ($count <= 0)
                            ? ""
                            : "LIMIT {$offset},{$count}";

        $dbResponse = $this->issue_db_query("SELECT resource_id,
                                                    resource_visibility,
                                                    creation_timestamp
                                             FROM rsc_users
                                             WHERE {$resourceVisibilityConditional}
                                             AND {$userIDConditional}
                                             ORDER BY creation_timestamp DESC
                                             {$limitConditional}");

        // If the query failed.
        if (!is_array($dbResponse))
        {
            return false;
        }

        // Combine the discrete user variables to form user resource objects.
        $users = [];
        foreach ($dbResponse as $userParameters)
        {
            // Verify that we have all the required parameters for a user resource.
            if (!isset($userParameters["resource_id"]) ||
                !isset($userParameters["resource_visibility"]) ||
                !isset($userParameters["creation_timestamp"]))
            {
                return false;
            }

            $userResource = Resource\UserResource::with(Resource\UserResourceID::from_string($userParameters["resource_id"]),
                                                        $userParameters["creation_timestamp"],
                                                        $userParameters["resource_visibility"]);
    
            if (!$userResource)
            {
                return false;
            }

            $users[] = $userResource;
        }

        return $users;
    }
}
