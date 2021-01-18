<?php

// for infinite time of execution
ini_set('max_execution_time', '0');

/**
 * run: php -S localhost:8000
 */

require __DIR__ . '/vendor/autoload.php';

$uploaddir = 'public/tmp/';

$errorMessages = [];
$uploadedFiles = [];

#die('<pre>' . print_r($_FILES["files"], true) . '</pre>');

if ($_FILES["files"]) {
    foreach ($_FILES["files"]["error"] as $key => $error) {

        $tmp_name = $_FILES["files"]["tmp_name"][$key];

        if (!$tmp_name) {
            continue;
        }

        $filename = basename($_FILES["files"]["name"][$key]);
        $uploadfile = $uploaddir . basename($filename);

        if ($error === UPLOAD_ERR_OK) {
            if (move_uploaded_file($tmp_name, $uploadfile)) {
                $uploadedFiles[] = $filename;
            } else {
                $errorMessages[] = "Could not move uploaded file '" . $tmp_name . "' to '" . $filename;
            }
        } else {
            $errorMessages[] = "Upload error. [" . $error . "] on file '" . $filename;
        }
    }
}

/** @var \CarstenWalther\XliffGen\Extractor $extractor */
$extractor = new \CarstenWalther\XliffGen\Extractor();

/** @var \Twig\Loader\FilesystemLoader $loader */
$loader = new \Twig\Loader\FilesystemLoader('public/templates');

/** @var \Twig\Environment $twig */
$twig = new \Twig\Environment($loader, []);

echo $twig->render('index.html', [
    'errorMessages' => $errorMessages,
    'uploadedFiles' => $uploadedFiles
]);
exit();
