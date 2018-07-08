<?php
namespace controllers;
use model\data\Content;
use model\db\DB;
use model\data\Question;
use model\assets\Assist;
session_start();

class Questions
{
    public function controllerQuestions ()
    {
        $assist = new Assist();
        $error = '';
        $categories = [];
        $answers = [];
        $order = 1;
        $filter = -1;
        $loader = new \Twig_Loader_Filesystem('templates');
        $twig = new \Twig_Environment($loader, array(
            'cache' => 'compilation_cache',
            'auto_reload' => true
        ));

        try {
            $pdo = (new DB())->getDBConnect();

            if ($assist->isGet()) {
                if (!empty($_GET['id']) && isset($_GET['action'])) {
                    switch ($_GET['action']) {
                        case 'delete':
                            $temp = new Question();
                            $temp->setQuestionId($_GET['id']);
                            $temp->deleteQuestion($pdo);
                            break;
                        case 'filter':
                            $filter = $_GET['id'];
                            setcookie('filter', $_GET['id']);
                            $order = !empty($_COOKIE['sort']) ? $_COOKIE['sort'] : $order;
                            break;
                        case 'sort':
                            $order = $_GET['id'];
                            $filter = !empty($_COOKIE['filter']) ? $_COOKIE['filter'] : $filter;
                            setcookie('sort', $_GET['id']);
                            break;
                    }
                }
            } else {
                $order = !empty($_COOKIE['sort']) ? $_COOKIE['sort'] : $order;
                $filter = !empty($_COOKIE['filter']) ? $_COOKIE['filter'] : $filter;
            }

            $content = new Content();
            $categories = $content->getCategories($pdo);
            $answers = $content->getCategoriesQuestionsAnswers($pdo, 0, $filter, $order);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        echo $twig->render('question_admin.twig', [
            'categories' => $categories,
            'answers' => $answers,
            'error' => $error,
            'tab' => 3
        ]);
    }
}
