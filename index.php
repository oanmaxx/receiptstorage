<?php
 require_once 'frontend/ErrorHandler.php';
 require_once 'frontend/Application.php';

 $app = new Application();
 $app->handleRequest();
 