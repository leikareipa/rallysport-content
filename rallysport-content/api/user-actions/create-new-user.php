<?php namespace RSC\API\Users;
      use RSC\DatabaseConnection;
      use RSC\API;
      use RSC\Resource;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script attempts to create and populate a new entry in the database's
 * table of users.
 * 
 */

require_once __DIR__."/../response.php";
require_once __DIR__."/../common-scripts/resource/resource-id.php";
require_once __DIR__."/../common-scripts/is-valid-uploaded-file.php";
require_once __DIR__."/../common-scripts/rallysported-track-data/rallysported-track-data.php";
require_once __DIR__."/../common-scripts/resource/track-resource.php";
require_once __DIR__."/../common-scripts/database-connection/user-database.php";
require_once __DIR__."/../common-scripts/database-connection/track-database.php";

// Attempts to add to the Rally-Sport Content database a new user.
//
// New user registration requires three parameters: email, password, and a
// RallySportED track file - the latter as an anti-spam measure.
//
// Note: The function should always return using exit() together with a
// Response object, e.g. exit(API\Response::code(200)->json([...]).
//
function create_new_user(string $email, string $plaintextPassword, array $uploadedFileInfo) : void
{
    /// TODO: Make sure the password and email are of the appropriate length, etc.

    // Verify that the uploaded file is a valid RallySportED track file.
    {
        if (!$uploadedFileInfo ||
            !\RSC\is_valid_uploaded_file($uploadedFileInfo, \RSC\RallySportEDTrackData::MAX_BYTE_SIZE))
        {
            exit(API\Response::code(303)->load_form_with_error("/rallysport-content/users/?form=add",
                                                               "Invalid track file"));
        }

        $track = Resource\TrackResource::with(\RSC\RallySportEDTrackData::from_zip_file($uploadedFileInfo["tmp_name"]),
                                              time(),
                                              0,
                                              Resource\TrackResourceID::random(),
                                              Resource\UserResourceID::random(),
                                              Resource\ResourceVisibility::HIDDEN);

        // If this isn't a valid RallySportED track file.
        if (!$track)
        {
            exit(API\Response::code(303)->load_form_with_error("/rallysport-content/users/?form=add",
                                                               "Please select a different track file"));
        }

        // All tracks used to register with should be unique wrt. previous
        // registrations, so verify that a track matching this one's hash hasn't
        // already been registered with.
        {
            $registrationHash = DatabaseConnection\TrackDatabase::hash_of_track_data($track);

            // Hashing the data would fail if the track resource instance contains
            // only metadata. That shouldn't be the case in the instances we've
            // got, but...
            if (!$registrationHash)
            {
                exit(API\Response::code(500)->error_message("Internal server error. Registration failed."));
            }

            if (!(new DatabaseConnection\UserDatabase())->is_resource_hash_unique($registrationHash))
            {
                exit(API\Response::code(303)->load_form_with_error("/rallysport-content/users/?form=add",
                                                                   "Please select a different track file"));
            }
        }
    }

    $userResourceID = Resource\UserResourceID::random();

    if (!$userResourceID ||
        !(new DatabaseConnection\UserDatabase())->create_new_user($userResourceID,
                                                                  $plaintextPassword,
                                                                  $email,
                                                                  $registrationHash))
    {
        /// TODO: If the email is a duplicate, we should give a precise error
        /// message about it.
        
        exit(API\Response::code(303)->load_form_with_error("/rallysport-content/users/?form=add",
                                                           "Database error (please try again or with different email)"));
    }

    exit(API\Response::code(303)->redirect_to("/rallysport-content/users/?form=new-account-created&new-user-id=".$userResourceID->string()));
}
