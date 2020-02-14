<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

session_start();

require_once __DIR__."/server-api/response.php";
require_once __DIR__."/server-api/session.php";

API\Session\log_client_out();

exit(API\Response::code(303)->redirect_to("/rallysport-content/"));
