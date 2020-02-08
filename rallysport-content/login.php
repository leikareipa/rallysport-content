<?php namespace RSC;
      use RSC\API;

session_start();

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/common-scripts/response.php";
require_once __DIR__."/common-scripts/resource/resource-id.php";
require_once __DIR__."/common-scripts/resource/resource-type.php";
require_once __DIR__."/common-scripts/database-connection/user-database.php";

if (isset($_SESSION["user_resource_id"]))
{
    exit(API\Response::code(303)->redirect_to("/rallysport-content/?form=login&error=Already logged in"));
}

if (!isset($_POST["user_id"]) ||
    !isset($_POST["password"]))
{
    exit(API\Response::code(303)->redirect_to("/rallysport-content/?form=login&error=Missing the user ID or password"));
}

$userResourceID = ResourceID::from_string($_POST["user_id"], ResourceType::USER);
if (!$userResourceID)
{
    exit(API\Response::code(303)->redirect_to("/rallysport-content/?form=login&error=Invalid user ID or password"));
}

if (!(new DatabaseConnection\UserDatabase())->validate_credentials($userResourceID, $_POST["password"]))
{
    exit(API\Response::code(303)->redirect_to("/rallysport-content/?form=login&error=Invalid user ID or password"));
}
else // Successful login.
{
    $_SESSION["user_resource_id"] = $userResourceID;

    exit(API\Response::code(303)->redirect_to("/rallysport-content/"));
}
