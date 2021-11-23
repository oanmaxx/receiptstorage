<?php

require_once 'Page.php';

class Footer
{
    private const HTML_FRAGMENT = 'footer.html';

    public static function getPage()
    {
        return Page::renderFragment(self::HTML_FRAGMENT);
    }
}