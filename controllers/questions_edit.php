<?php
namespace  controllers;
use model\data\Content;
use model\db\DB;
use model\data\Answer;
use model\data\Question;
use model\data\User;
use model\data\Status;
use model\assets\Assist;
session_start();

class Questions_Edit
{
    public function controllerQuestionsEdit ()
    {
        $assist = new Assist();

        $loader = new \Twig_Loader_Filesystem('templates');
        $twig = new \Twig_Environment($loader, array(
            'cache' => 'compilation_cache',
            'auto_reload' => true
        ));

        $answers = [];
        $answer = null;
        $categories = [];
        $statuses = [];
        $error = '';
        $addEdit = 'add';
        $editAnswer = '';

        try {
            $pdo = (new DB())->getDBConnect();
            $content = new Content();
            $categories = $content->getCategories($pdo);
            $statuses = $content->getStatuses($pdo);

            if (isset($_GET['action']) && $_GET['action'] === 'edit' && !isset($_POST['answer'])) {
                $addEdit = 'edit';
            }

            if ($assist->isGet()) {
                if (empty($_GET['id'])) {
                    throw new \Exception('Ошибка Get-запроса');
                }

                $answers = $content->getCategoriesQuestionsAnswers($pdo, $_GET['id'], 0, 0);

                if (empty($answers)) {
                    throw new \Exception('Вопрос не найден, возможно он удален другим администратором');
                }

                $answer = $answers[0];

                if (isset($_GET['action']) && !empty($_GET['answerId'])) {
                    $temp = new Answer();
                    $temp->setAnswerId($_GET['answerId']);

                    if ($_GET['action'] === 'edit' && $addEdit === 'edit') {
                        $editAnswer = $temp->selectAnswer($pdo);
                    } elseif ($_GET['action'] === 'delete') {
                        $temp->deleteAnswer($pdo);
                    }

                }

            } else {
                throw new \Exception('Ошибка: не установлены Get-параметры');
            }

            if ($assist->isPost()) {

                if ($assist->isAddEdit() && !empty($_POST['answer'])) {
                    $temp = new Answer();

                    if (isset($_POST['add_edit']) && $_POST['add_edit'] === 'edit' && !empty($_GET['answerId'])) {
                        $temp->setAnswerId($_GET['answerId'])
                            ->setAnswerName($_POST['answer']);
                        $temp->updateAnswer($pdo);
                    } else {
                        $temp->setQuestionId($_GET['id'])
                            ->setAnswerName($_POST['answer']);
                        $temp->insertAnswer($pdo);
                    }

                } elseif ($assist->isEditQuestion() && !empty($answer)) {
                    $temp = new Question();

                    $temp->setQuestionId($_GET['id'])
                        ->setUser(new User($answer->getUser()->getId(), isset($_POST['name']) ? $_POST['name'] : '',
                            isset($_POST['email']) ? $_POST['email'] : ''))
                        ->setQuestionName(isset($_POST['question']) ? $_POST['question'] : '')
                        ->setStatus(new Status(isset($_POST['status']) ? $_POST['status'] : 0, ''))
                        ->setCategoryId(isset($_POST['category']) ? $_POST['category'] : null);
                    $temp->checkUpdate();
                    $temp->updateQuestion($pdo);
                    header('Location: index.php?mode=Questions');
                } elseif ($assist->isCancel()) {
                    header('Location: index.php?mode=Questions');
                }

            }

            if ((isset($_POST['addedit'])) || (isset($_GET['action']) && $_GET['action'] === 'delete')) {
                header('Location: index.php?mode=Questions_edit&id=' . $_GET['id']);
                exit;
            }

        } catch (\Exception $e) {
            $error = $e->getMessage();

            if ($assist->isPost() && $assist->isEditQuestion()) {
                $answer->getUser()->setName(isset($_POST['name']) ? $_POST['name'] : '')
                    ->setEmail(isset($_POST['email']) ? $_POST['email'] : '');
                $answer->setQuestionName(isset($_POST['question']) ? $_POST['question'] : '')
                    ->setCategoryId(isset($_POST['category']) ? $_POST['category'] : null);
                $answer->getStatus()->setId(isset($_POST['status']) ? $_POST['status'] : 0);
            }
        }

        echo $twig->render('question_edit_admin.twig', [
            'categories' => $categories,
            'answers' => $answers,
            'answer' => $answer,
            'error' => $error,
            'statuses' => $statuses,
            'edit_answer' => $editAnswer,
            'addEdit' => $addEdit
        ]);
    }
}