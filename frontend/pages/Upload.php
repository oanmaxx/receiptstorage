<?php

require_once 'Page.php';
require_once 'Header.php';
require_once 'Footer.php';
require_once __DIR__ . '/../../engine/Engine.php';

class Upload
{
    public static function getPage()
    {
        $content = Page::renderFragment('upload.html');

        $result = self::process();

        $content = str_replace("RECEIPT_PLACEHOLDER", $result, $content);

        return Header::getPage() . $content . Footer::getPage();
    }

    private static function process() {
        $result = "";
        if(isset($_FILES)) {            
            $FileType = strtolower(pathinfo($_FILES["attachment"]["name"],PATHINFO_EXTENSION));

            if ($_FILES["attachment"]["size"] > 1048576) {
                $result = "Eroare. Fisierul este mai mare de 1MB.";
            }
            if($FileType != "pdf" && $FileType != "png" && $FileType != "jpg") {
                $result = "Eroare. Fisierul trebuie sa fie pdf, png sau jpg.";
            }

            if (empty($result)) {
                $target_dir = __DIR__ . "/../../uploads/";
                $target_file = $target_dir . self::generateRandomString() . '.' . $FileType;                

                if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_file)) {
                    $result = Engine::uploadToApi($target_file);
                } else {
                    $result = "Eroare la incarcarea fisierului. Va rugam reincercati.";
                }
            } 
        } else {
            $result = "Eroare. Va rugam incarcati un fisier PDF.";
        }

        return $result;
    }

    private static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}