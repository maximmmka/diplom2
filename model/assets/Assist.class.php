<?php

namespace model\assets;

class Assist
{
    /**
     * @return bool
     */
    public function isPost()
    {
        return !empty($_POST);
    }

    /**
     * @return bool
     */
    public function isGet()
    {
        return !empty($_GET);
    }

    /**
     * @return bool
     */
    public function isAddEdit()
    {
        return isset($_POST['addedit']);
    }

    /**
     * @return bool
     */
    public function isEditQuestion()
    {
        return isset($_POST['editQuestion']);
    }

    /**
     * @return bool
     */
    public function isCancel()
    {
        return isset($_POST['cancel']);
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        if (!empty($_SESSION['admin'])) {
            return true;
        }

        return false;
    }

}