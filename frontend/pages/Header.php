<?php

require_once 'Page.php';

class Header
{
    private const HTML_FRAGMENT = 'header.html';

    public static function getPage()
    {
        return Page::renderFragment(self::HTML_FRAGMENT);
    }
}