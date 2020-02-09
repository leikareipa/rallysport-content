<?php

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

session_start();

require_once __DIR__."/common-scripts/response.php";

$_SESSION["user_resource_id"] = NULL;

exit(\RSC\API\Response::code(303)->redirect_to("/rallysport-content/"));
