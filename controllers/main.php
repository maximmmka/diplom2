<?php

namespace controllers;
use model\data\Content;
use model\db\DB;
use model\data\Question;
use model\data\User;
use model\data\Admin;
use model\assets\Assist;
session_start();

class Main
{
    public function controllerMain ()
    {
        $assist = new Assist();

        $loader = new \Twig_Loader_Filesystem('templates');
        $twig = new \Twig_Environment($loader, array(
            'cache' => 'compilation_cache',
            'auto_reload' => true
        ));

        $categories = [];
        $questions = [];
        $answers = [];
        $error = '';
        $formAdminHide = null;
        $admin = null;
        $mode = ['question' => 'guest',
            'role' => 'guest',
            'userName' => ''];

        try {
            $pdo = (new DB())->getDBConnect();
            $content = new Content();

            if ($assist->isPost()) {

                if (isset($_POST['saveMain'])) {
                    $temp = new Question();
                    $userName = '';
                    $userEmail = '';

                    if (isset($_POST['name'], $_POST['email'])) {
                        $userName = $_POST['name'];
                        $userEmail = $_POST['email'];
                    } elseif
                    (isset($_SESSION['userName'], $_SESSION['userEmail'])) {
                        $userName = $_SESSION['userName'];
                        $userEmail = $_SESSION['userEmail'];
                    }

                    $temp->setUser(new User(0, $userName, $userEmail))
                        ->setQuestionName(isset($_POST['question']) ? $_POST['question'] : '')
                        ->setCategoryId(isset($_POST['category']) ? $_POST['category'] : null);
                    $temp->checkInsert();
                    $temp->insertQuestion($pdo);
                } elseif
                (isset($_POST['adminMain'], $_POST['login'], $_POST['password'])) {
                    $formAdminHide = false;
                    $temp = new Admin(0, $_POST['login'], $_POST['password']);
                    $admin = $temp;
                    $temp->checkAdmin($pdo);
                    $temp->validateAdmin($pdo);
                    $formAdminHide = true;
                }
            }

            if ($assist->isPost()) {
                header('Location: index.php');
                exit;
            }

            $categories = $content->getCategories($pdo);
            $questions = $content->getQuestionsWithAnswersPublic($pdo);
            $answers = $content->getAnswersPublic($pdo);

            if (!empty($_SESSION['admin'])) {
                $mode['role'] = 'admin';
            }

            if (!empty($_SESSION['userId'])) {
                $mode['question'] = 'user';
                $mode['userName'] = $_SESSION['userName'];
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        echo $twig->render('main.twig', [
            'categories' => $categories,
            'questions' => $questions,
            'answers' => $answers,
            'admin' => $admin,
            'error' => $error,
            'mode' => $mode,
            'formAdminHide' => $formAdminHide
        ]);
    }
}