<?php

require_once 'pages/Home.php';
require_once 'pages/Upload.php';
require_once 'pages/ConversionPersist.php';
require_once 'pages/Statistics.php';

class Application
{
    ///
    /// Public functions
    ///

    public function handleRequest()
    {
        // Prevent internal output
        //ob_start();

        $pageContent = $this->htmlStart();

        if (isset($_POST['action']) && $_POST['action'] == 'uploadReceipt') {
            $pageContent .= Upload::getPage();
        } else if (isset($_POST['action']) && $_POST['action'] == 'confirmConversion') {
            $pageContent .= ConversionPersist::getPage();
        } else if (isset($_GET['search']) || (isset($_GET['action']) && $_GET['action'] == 'productStatistics')) {
            $pageContent .= Statistics::getPage();
        }
        else {
            $pageContent .= Home::getPage();
        }

        $pageContent .= $this->htmlEnd();

        
        // Prevent any output until here
        //$data = ob_get_clean();
        //unset($data);

        echo htmlspecialchars_decode($pageContent);
    }

    ///
    /// Private functions
    ///

    private function htmlStart()
    {
        return '
<!DOCTYPE html>
<html>
<head>
    <title>Easy Shopping</title>
    <script src="js/jquery-3.6.0.min.js"></script>
</head>
<body>
';
    }

    private function htmlEnd()
    {
        return '
</body>
</html>';
    }
}
