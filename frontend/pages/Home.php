<?php

require_once 'Page.php';
require_once 'Header.php';
require_once 'Footer.php';

class Home
{
    public static function getPage()
    {
        return Header::getPage() . Page::renderFragment('home.html') . Footer::getPage();
    }
}