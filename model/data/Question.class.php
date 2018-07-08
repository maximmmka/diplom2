<?php

namespace model\data;

class Question extends Category
{
    private $questionId;
    private $questionName;
    private $date;
    private $user;
    private $status;

    /**
     * Question constructor.
     * @param int $categoryId
     * @param string $categoryName
     * @param int $questionId
     * @param string $questionName
     * @param \DateTime $date
     * @param User $user
     * @param Status $status
     */
    public function __construct(
        $categoryId = 0,
        $categoryName = '',
        $questionId = 0,
        $questionName = '',
        $date = null,
        User $user = null,
        Status $status = null
    ) {
        parent::__construct ($categoryId, $categoryName);
        $this->questionId = $questionId;
        $this->questionName = $questionName;
        $this->date = $date;
        $this->user = $user;
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getQuestionId()
    {
        return $this->questionId;
    }

    /**
     * @param int $questionId
     * @return $this
     */
    public function setQuestionId($questionId)
    {
        $this->questionId = $questionId;
        return $this;
    }

    /**
     * @return string
     */
    public function getQuestionName()
    {
        return $this->questionName;
    }

    /**
     * @param string $questionName
     * @return $this
     */
    public function setQuestionName($questionName)
    {
        $this->questionName = $questionName;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Status|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param Status $status
     * @return $this
     */
    public function setStatus(Status $status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     *
     */
    public function checkUpdate()
    {
        try {
            if (null === $this->user || null === $this->user->getName() || trim($this->user->getName()) === '' ||
                null === $this->user->getEmail() || trim($this->user->getEmail()) === '' || trim($this->questionName) === '' ||
                null === $this->status  || null === $this->status->getId() || null === $this->getCategoryId()) {
                throw new \LogicException('При записи вопроса все поля должны быть заполнены');
            }
        } catch (\LogicException $e) {
            throw $e;
        } catch (\RuntimeException $e) {
            throw new \RuntimeException('Ошибка при валидации записи вопроса', 0, $e);
        }
    }

    /**
     * @param \PDO $pdo
     */
    public function updateQuestion(\PDO $pdo)
    {
        try {
            $sql = '
                UPDATE 
                    questions 
                SET 
                    questions.id_categories = :id_categories, 
                    questions.name = :name, 
                    questions.id_status = :id_status 
                WHERE questions.id = :id;';

            $statement = $pdo->prepare($sql);
            $statement->execute(['id'=>$this->questionId, 'id_categories'=>$this->getCategoryId(), 'name'=>$this->questionName, 'id_status'=>$this->status->getId()]);
            $sql = 'UPDATE users SET users.name = :name, users.email = :email WHERE users.id = :id;';
            $statement = $pdo->prepare($sql);
            $statement->execute(['id'=>$this->user->getId(), 'name'=>$this->user->getName(), 'email'=>$this->user->getEmail()]);
        } catch (\Exception $e) {
            throw new \RuntimeException('Ошибка изменения вопроса', 0, $e);
        }
    }

    /**
     * @param \PDO $pdo
     */
    public function deleteQuestion(\PDO $pdo)
    {
        try {
            $sql = 'DELETE FROM questions WHERE questions.id=:id;';
            $statement = $pdo->prepare($sql);
            $statement->execute(['id'=>$this->questionId]);
        } catch (\Exception $e) {
            throw new \RuntimeException('Ошибка удаления вопроса', 0, $e);
        }
    }

    /**
     *
     */
    public function checkInsert()
    {
        try {

            if (!empty($_SESSION['admin']) || !empty($_SESSION['userId'])) {

                if (trim($this->questionName) === '' || null === $this->getCategoryId()) {
                    throw new \LogicException('При записи вопроса все поля должны быть заполнены');
                }

            } elseif (null === $this->user || null === $this->user->getName() || trim($this->user->getName()) === '' || null === $this->user->getEmail() ||
                    trim($this->user->getEmail()) === '' || trim($this->questionName) === '' || null === $this->getCategoryId()) {
                    throw new \LogicException('При записи вопроса все поля должны быть заполнены');
            }
        } catch (\RuntimeException $e) {
            throw new \RuntimeException('Ошибка при валидации записи вопроса', 0, $e);
        } catch (\LogicException $e) {
            throw $e;
        }
    }

    /**
     * @param \PDO $pdo
     */
    public function insertQuestion(\PDO $pdo)
    {
        try {

            if (!empty($_SESSION['userId'])) {
                $userId = $_SESSION['userId'];
            } else {

                $sql = 'SELECT users.id FROM users WHERE users.name = :name and users.email = :email;';
                $statement = $pdo->prepare($sql);
                $statement->execute(['name' => $this->user->getName(), 'email' => $this->user->getEmail()]);
                $row = $statement->fetch(\PDO::FETCH_OBJ);

                if (!$row) {
                    $sql = 'INSERT INTO users ( name, email ) VALUES (:name, :email);';
                    $statement = $pdo->prepare($sql);
                    $statement->execute(['name' => $this->user->getName(), 'email' => $this->user->getEmail()]);
                    $userId = $pdo->lastInsertId('users');
                } else {
                    $userId = $row->id;
                }
            }

            $sql = 'INSERT INTO questions ( id_categories, id_users, name ) VALUES (:id_categories, :id_user, :name);';
            $statement = $pdo->prepare($sql);
            $statement->execute(['id_categories'=>$this->getCategoryId(), 'id_user'=>$userId, 'name'=>$this->questionName]);
            $_SESSION['userId'] = $userId;
            $_SESSION['userName'] = $this->user->getName();
            $_SESSION['userEmail'] = $this->user->getEmail();
        } catch (\Exception $e) {
            throw new \RuntimeException('Ошибка добавления вопроса', 0, $e);
        }
    }
}