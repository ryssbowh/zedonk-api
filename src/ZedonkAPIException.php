<?php

namespace ZedonkAPI;

class ZedonkAPIException extends \Exception
{
    public static function connectorNotSet()
    {
        return new static("You must set your credentials before using the zedonk api");
    }

    public static function connection($apiName)
    {
        return new static("API call error ($apiName) : Couldn't connect");
    }

    public static function apiError($apiName, $error)
    {
        return new static("API call error ($apiName) : ".$error);
    }

    public static function apiHttpError($apiName, $infos)
    {
        return new static("API http error ($apiName) : code ".$infos['http_code']);
    }
}