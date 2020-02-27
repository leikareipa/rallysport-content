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

        $emailHash = hash("sha256", $this->peppered($email));

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

        $emailHash = hash("sha256", $this->peppered($email));

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

        $emailHash = hash("sha256", $this->peppered($email));

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
    public function is_email_hash_unique(string $mailHashPepperedSHA256) : bool
    {
        if (!$this->is_connected())
        {
            return false;
        }

        $dbResponse = $this->issue_db_query("SELECT COUNT(*)
                                             FROM rsc_users
                                             WHERE email_hash_sha256 = ?",
                                            [$mailHashPepperedSHA256]);

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

        $passwordHash = password_hash($plaintextPassword, PASSWORD_DEFAULT);
        $emailHash = hash("sha256", $this->peppered($plaintextEmail));

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

    // Returns the resource IDs (as an array of strings) of all public users.
    // On error, returns FALSE.
    public function get_ids_of_all_public_users()
    {
        if (!$this->is_connected())
        {
            return false;
        }

        $queryResults = $this->issue_db_query("SELECT resource_id
                                               FROM rsc_users
                                               WHERE resource_visibility = ?",
                                              [Resource\ResourceVisibility::PUBLIC]);

        if (!is_array($queryResults) || !count($queryResults))
        {
            return false;
        }

        return array_reduce($queryResults, function($acc, $element)
        {
            $acc[] = $element["resource_id"];
            return $acc;
        }, []);
    }

    // Returns as an array of UserResource elements all public users in the
    // database. On error, returns FALSE.
    public function get_all_public_user_resources()
    {
        $userIDs = $this->get_ids_of_all_public_users();

        if (!is_array($userIDs) || !count($userIDs))
        {
            return false;
        }

        // Fetch the user data.
        $users = array_reduce($userIDs, function($acc, $element)
        {
            if (($userResource = Resource\UserResource::from_database($element)))
            {
                $acc[] = $userResource;
            }

            return $acc;
        }, []);

        if (count($users) !== count($userIDs))
        {
            return false;
        }

        return $users;
    }

    // Returns the given user's data as a UserResourceID object. The given
    // visibility level must match the actual visibility level of the user
    // in the database; or an error will be returned.On error, returns FALSE.
    public function get_user_resource(Resource\UserResourceID $userResourceID = NULL,
                                      int /*Resource\ResourceVisibility*/ $expectedVisibility = Resource\ResourceVisibility::PUBLIC)
    {
        if (!$this->is_connected())
        {
            return false;
        }

        if (!$userResourceID)
        {
            return false;
        }

        $dbResponse = $this->issue_db_query("SELECT resource_id,
                                                    resource_visibility,
                                                    creation_timestamp
                                             FROM rsc_users
                                             WHERE resource_id = ?
                                             AND resource_visibility = ?",
                                            [$userResourceID->string(),
                                             $expectedVisibility]);

        // User resource IDs should be unique, so we should find no more than
        // one element in the response array (or 0 elements if the ID doesn't
        // exist).
        if (!is_array($dbResponse) || count($dbResponse) != 1)
        {
            return false;
        }

        $userResource = \RSC\Resource\UserResource::with(Resource\UserResourceID::from_string($dbResponse[0]["resource_id"]),
                                                         $dbResponse[0]["creation_timestamp"],
                                                         $dbResponse[0]["resource_visibility"]);

        if (!$userResource)
        {
            return false;
        }

        return $userResource;
    }
}
