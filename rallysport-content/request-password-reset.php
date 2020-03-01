<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * A script used for resetting a user's password. Expects to be called
 * as a POST request, with the 'email' parameter set to the target
 * user's email address, and the $_FILES["rallysported_track_file"]
 * element reflecting an uploaded RallySportED-js track file, which
 * matches the track file the user registered with. 
 * 
 */

require_once __DIR__."/api/response.php";
require_once __DIR__."/api/emailer.php";
require_once __DIR__."/api/common-scripts/is-valid-uploaded-file.php";
require_once __DIR__."/api/common-scripts/resource/resource-id.php";
require_once __DIR__."/api/common-scripts/resource/track-resource.php";
require_once __DIR__."/api/common-scripts/database-connection/user-database.php";
require_once __DIR__."/api/common-scripts/rallysported-track-data/rallysported-track-data.php";

$email = ($_POST["email"] ?? NULL);
$trackFileUploadInfo = ($_FILES["rallysported_track_file"] ?? NULL);

if (!isset($email) ||
    !isset($trackFileUploadInfo))
{
    exit(API\Response::code(303)->load_form_with_error("/rallysport-content/?form=request-password-reset",
                                                       "Missing the email address or track file"));
}

// Verify that the uploaded file is a valid RallySportED track file.
{
    if (!\RSC\is_valid_uploaded_file($trackFileUploadInfo, \RSC\RallySportEDTrackData::MAX_BYTE_SIZE))
    {
        exit(API\Response::code(303)->load_form_with_error("/rallysport-content/?form=request-password-reset",
                                                           "Invalid track file"));
    }

    $track = Resource\TrackResource::with(\RSC\RallySportEDTrackData::from_zip_file(($trackFileUploadInfo["tmp_name"] ?? "")),
                                          time(),
                                          0,
                                          Resource\TrackResourceID::random(),
                                          Resource\UserResourceID::random(),
                                          Resource\ResourceVisibility::HIDDEN);

    // If this isn't a valid RallySportED track file.
    if (!$track)
    {
        exit(API\Response::code(303)->load_form_with_error("/rallysport-content/?form=request-password-reset",
                                                           "Invalid track file"));
    }

    $trackDataHash = DatabaseConnection\TrackDatabase::generate_hash_of_track_data($track);

    if (!$trackDataHash)
    {
        exit(API\Response::code(303)->load_form_with_error("/rallysport-content/?form=request-password-reset",
                                                           "Invalid track file"));
    }
}

$userDB = new DatabaseConnection\UserDatabase();

// Find the user resource ID matching the given track data hash and email
// address. Note that if we fail to find such a user, i.e. if the data provided
// are incorrect, we'll nonetheless pretend like we succeeded - so we don't leak
// out information like what's the correct track file for a given email address.
// (If we succeeded for real, the user will receive an email with instructions
// for resetting their password; while if we fail, they just don't receive that
// email.)
{
    $userID = $userDB->get_owner_id_of_registration_track_hash($trackDataHash);

    if (!$userID ||
        !$userDB->is_correct_user_email_address($email, $userID))
    {
        exit(API\Response::code(303)->redirect_to("/rallysport-content/?form=password-reset-request-done"));
    }
}

$resetToken = $userDB->generate_token_for_password_reset($userID);

if (!is_array($resetToken) ||
    !isset($resetToken["value"]) ||
    !isset($resetToken["expires"]))
{
    exit(API\Response::code(303)->load_form_with_error("/rallysport-content/?form=request-password-reset",
                                                       "The request for a password reset failed"));
}

RallySportContentEmailer::send_password_reset_link($email, $resetToken);

exit(API\Response::code(303)->redirect_to("/rallysport-content/?form=password-reset-request-done"));
