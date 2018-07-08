<?php
//require_once '../vendor/autoload.php';
namespace controllers;
use model\data\Content;
use model\db\DB;
use model\data\Category;
use model\assets\Assist;
session_start();

class Categories
{
    public function controllerCategories ()
    {
        $assist = new Assist();
        $error = '';
        $addEdit = 'add';
        $category = null;
        $categories = null;

        $loader = new \Twig_Loader_Filesystem('templates');
        $twig = new \Twig_Environment($loader, array(
            'cache' => 'compilation_cache',
            'auto_reload' => true
        ));


        try {
            $pdo = (new DB())->getDBConnect();
            $content = new Content();
            $categories = $content->getCategories($pdo, 1);

            if (isset($_GET['action']) && $_GET['action'] === 'edit' && !isset($_POST['categoryName'])) {
                $addEdit = 'edit';
            }

            if ($assist->isGet() && isset($_GET['action']) && !empty($_GET['id'])) {
                $temp = new Category();
                $temp->setCategoryId($_GET['id']);

                if ($_GET['action'] === 'edit' && $addEdit === 'edit') {
                    $category = $temp->selectCategory($pdo);
                } elseif ($_GET['action'] === 'delete') {
                    $temp->deleteCategory($pdo);
                }

            }

            if ($assist->isPost()) {

                if ($assist->isAddEdit() && isset($_POST['categoryName'])) {
                    $temp = new Category();


                    if (isset($_POST['add_edit']) && $_POST['add_edit'] === 'edit' && !empty($_GET['id'])) {
                        $temp->setCategoryId($_GET['id'])
                            ->setCategoryName($_POST['categoryName']);
                        $category = $temp->getCategoryName();
                        $temp->checkCategory($pdo);
                        $temp->updateCategory($pdo);
                    } else {
                        $temp->setCategoryName($_POST['categoryName']);
                        $category = $temp->getCategoryName();
                        $temp->checkCategory($pdo);
                        $temp->insertCategory($pdo);
                    }

                }

            }

            if ((isset($_POST['addedit'])) || (isset($_GET['action']) && $_GET['action'] === 'delete')) {
                header('Location: index.php?mode=Categories');
                exit;
            }

        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        echo $twig->render('categories_admin.twig', [
            'categories' => $categories,
            'error' => $error,
            'category' => $category,
            'tab' => 2,
            'addEdit' => $addEdit
        ]);
    }
}