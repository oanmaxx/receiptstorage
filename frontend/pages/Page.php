<?php

class Page
{
    public static function renderFragment($fileFragment)
    {
        return file_get_contents(__DIR__ . '/' . $fileFragment);
    }
}