<?php
 require_once 'backend/ErrorHandler.php';
 require_once 'backend/Application.php';
 
 $app = new ApplicationInterface();
 $app->handleRequest();
