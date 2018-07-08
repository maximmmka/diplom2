<?php
use controllers\Main;
use controllers\Admins;
use controllers\Categories;
use controllers\Questions;
use controllers\Questions_Edit;
use model\assets\Assist;
require_once 'vendor/autoload.php';

$assist = new Assist();

if ((empty($_POST) && empty($_GET)) ||
    !empty($_POST['saveMain']) ||
    !empty($_POST['adminMain'])) {
    (new Main())->controllerMain();
} elseif (!empty($_GET['mode']) && $_GET['mode'] === 'Admins') {
    (new Admins())->controllerAdmins();
} elseif (!empty($_GET['mode']) && $_GET['mode'] === 'Categories') {
    (new Categories())->controllerCategories();
} elseif (!empty($_GET['mode']) && $_GET['mode'] === 'Questions') {
    (new Questions())->controllerQuestions();
} elseif (!empty($_GET['mode']) && $_GET['mode'] === 'Questions_edit') {
    (new Questions_Edit())->controllerQuestionsEdit();
} else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
    echo '400 Bad Request';
    exit;
}