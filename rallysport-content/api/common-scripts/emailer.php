<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

abstract class RallySportContentEmailer
{
    public const SENDER_ADDRESS = "Rally-Sport Content <rsc@tarpeeksihyvaesoft.com>";

    private static function send_email(string $to, string $title, string $message) : bool
    {
        $acceptedForDelivery = mail($to, $title, $message,
                                    "From: {static::SENDER_ADDRESS}\r\n
                                     Reply-To: {static::SENDER_ADDRESS}");

        return $acceptedForDelivery;
    }
}
