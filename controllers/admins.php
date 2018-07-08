<?php
namespace controllers;
use model\data\Content;
use model\db\DB;
use model\data\Admin;
use model\assets\Assist;
session_start();

class Admins
{
    public function controllerAdmins ()
    {
        $assist = new Assist();
        $error = '';
        $addEdit = 'add';
        $admin = null;
        $admins = null;

        $loader = new \Twig_Loader_Filesystem('templates');
        $twig = new \Twig_Environment($loader, array(
            'cache' => 'compilation_cache',
            'auto_reload' => true
        ));


        try {
            $pdo = (new DB())->getDBConnect();
            $content = new Content();
            $admins = $content->getAdmins($pdo);

            if (isset($_GET['action']) && $_GET['action'] === 'edit' && !isset($_POST['login'])) {
                $addEdit = 'edit';
            }

            if ($assist->isGet() && isset($_GET['action']) && !empty($_GET['id'])) {
                $temp = new Admin();
                $temp->setId($_GET['id']);

                if ($_GET['action'] === 'edit' && $addEdit === 'edit') {
                    $admin = $temp->selectAdmin($pdo);
                } elseif ($_GET['action'] === 'delete') {
                    $temp->deleteAdmin($pdo);
                }

            }

            if ($assist->isPost()) {

                if ($assist->isAddEdit() && isset($_POST['login'], $_POST['password'])) {
                    $temp = new Admin();

                    if (isset($_POST['add_edit']) && $_POST['add_edit'] === 'edit' && !empty($_GET['id'])) {
                        $temp->setId($_GET['id'])
                            ->setLogin($_POST['login'])
                            ->setPassword($_POST['password']);
                        $admin = $temp;
                        $temp->checkAdmin($pdo, true, true, $_GET['id']);
                        $temp->updateAdmin($pdo);
                    } else {
                        $temp->setLogin($_POST['login'])
                            ->setPassword($_POST['password']);
                        $admin = $temp;
                        $temp->checkAdmin($pdo, true);
                        $temp->insertAdmin($pdo);
                    }

                }

            }

            if ((!empty($_POST) && isset($_POST['addedit'])) || (isset($_GET['action']) && $_GET['action'] === 'delete')) {
                header('Location: index.php?mode=Admins');
                exit;
            }

        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        echo $twig->render('admins_admin.twig', [
            'admins' => $admins,
            'error' => $error,
            'admin' => $admin,
            'tab' => 1,
            'addEdit' => $addEdit,
            'adminId' => isset($_SESSION['admin']) ? $_SESSION['admin'] : null
        ]);
    }
}