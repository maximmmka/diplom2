<?php

namespace model\data;

class Content
{
    private $array = [];
    private $orders = [
        '1'=>'questions.id_status, questions.date DESC',
        '2'=>'categories.name, questions.date DESC'
    ];

    /**
     * @param \PDO $pdo
     * @param int $countQuestions
     * @param int $id
     * @return Category[]
     */
    public function getCategories(\PDO $pdo, $countQuestions = 0, $id = 0)
    {
        try {
            $this->array = [];

            if ($id <> 0) {
                $sql = 'SELECT categories.id, categories.name FROM categories WHERE categories.id = :id;';
                $parameters = ['id' => $id];
            } elseif ($countQuestions <> 0) {
                $sql = '
                    SELECT 
                        categories.id, 
                        categories.name, 
                        Sum(If(questions.id is not null,1,0)) countQuestions, 
                        Sum(If(questions.id is not null And id_status=2,1,0)) countQuestionsPublic, 
                        Sum(If(questions.id is not null And id_status=1,1,0)) countQuestionsNotWork
                    FROM categories 
                    LEFT JOIN questions ON categories.id = questions.id_categories
                    GROUP BY categories.id, categories.name 
                    ORDER BY categories.name;';
                $parameters = [];
            } else {
                $sql = 'SELECT categories.id, categories.name FROM categories ORDER BY categories.name;';
                $parameters = [];
            }

            $statement = $pdo->prepare($sql);
            $statement->execute($parameters);

            while ($row = $statement->fetch(\PDO::FETCH_OBJ)) {
                $category = new Category($row->id, $row->name);

                if ($countQuestions <> 0) {
                    $category->setCount([
                        'countQuestions'=>$row->countQuestions,
                        'countQuestionsPublic'=>$row->countQuestionsPublic,
                        'countQuestionsNotWork'=>$row->countQuestionsNotWork
                        ]);
                }

                $this->array[] = $category;
            }

            return $this->array;
        } catch (\Exception $e) {
            throw new \RuntimeException('Ошибка получения списка тем', 0, $e);
        }
    }

    /**
     * @param \PDO $pdo
     * @param int $id
     * @param int $categoryId
     * @param int $order
     * @return Answer[]
     */
    public function getCategoriesQuestionsAnswers(\PDO $pdo, $id = 0, $categoryId = -1, $order = 0)
    {
        try {
            $this->array = [];
            $order_sort = '';

            if (isset($this->orders[$order])) {
                $order_sort = ' ORDER BY ' . $this->orders[$order];
            }

            $sql = '
                SELECT 
                    answers.id answerId, 
                    categories.id categoryId, 
                    questions.id, 
                    users.id userId, 
                    statuses.id statusId, 
                    categories.name category, 
                    questions.date, 
                    questions.name question, 
                    statuses.name status, 
                    users.name `user`, 
                    users.email, 
                    answers.name answer
                FROM (((categories 
                INNER JOIN questions ON categories.id = questions.id_categories) 
                LEFT JOIN answers ON questions.id = answers.id_questions) 
                INNER JOIN users ON questions.id_users = users.id) 
                INNER JOIN statuses ON questions.id_status = statuses.id';
            if ($id<>0) {
                $sql .= ' WHERE questions.id = :id' . $order_sort . ';';
                $parameters = ['id' => $id];
            } elseif ($categoryId<>-1) {
                $sql .= ' WHERE categories.id = :id' . $order_sort . ';';
                $parameters = ['id' => $categoryId];
            } else {
                $sql .=  $order_sort . ';';
                $parameters = [];
            }

            $statement = $pdo->prepare($sql);
            $statement->execute($parameters);

            while ($row = $statement->fetch(\PDO::FETCH_OBJ)) {
                $this->array[] = new Answer(
                    $row->categoryId,
                    $row->category,
                    $row->id,
                    $row->question,
                    $row->date,
                    new User( $row->userId, $row->user, $row->email),
                    new Status($row->statusId, $row->status),
                    $row->answerId,
                    $row->answer
                );
            }

            return $this->array;
        } catch (\Exception $e) {
            throw new \RuntimeException('Ошибка получения списка тем', 0, $e);
        }
    }

    /**
     * @param \PDO $pdo
     * @return Status[]
     */
    public function getStatuses(\PDO $pdo)
    {
        try {
            $this->array = [];
            $sql = 'SELECT statuses.id, statuses.name FROM statuses ORDER BY statuses.id;';

            $statement = $pdo->prepare($sql);
            $statement->execute();

            while ($row = $statement->fetch(\PDO::FETCH_OBJ)) {
                $this->array[] = new Status( $row->id, $row->name);
            }

            return $this->array;
        } catch (\Exception $e) {
            throw new \RuntimeException('Ошибка получения списка статусов', 0, $e);
        }
    }

    /**
     * @param \PDO $pdo
     * @return Question[]
     */
    public function getQuestionsWithAnswersPublic (\PDO $pdo)
    {
        try {
            $this->array = [];

            $sql = '
                SELECT 
                    categories.id categoryId, 
                    questions.id questionId, 
                    questions.name questionName
                FROM (categories 
                INNER JOIN questions ON categories.id = questions.id_categories) 
                INNER JOIN answers ON questions.id = answers.id_questions
                WHERE questions.id_status = 2
                GROUP BY categories.id, questions.id, questions.name
                ORDER BY questions.id ;';

            $statement = $pdo->prepare($sql);
            $statement->execute();

            while ($row = $statement->fetch(\PDO::FETCH_OBJ)) {
                $this->array[] = new Question( $row->categoryId, '', $row->questionId, $row->questionName);
            }

            return $this->array;
        } catch (\Exception $e) {
            throw new \RuntimeException('Ошибка получения списка опубликованных вопросов с ответами', 0, $e);
        }
    }

    /**
     * @param \PDO $pdo
     * @return array
     */
    public function getAnswersPublic (\PDO $pdo)
    {
        try {
            $this->array = [];

            $sql = '
                SELECT 
                    categories.id categoryId, 
                    questions.id questionId, 
                    answers.id answerId, 
                    answers.name answerName
                FROM (categories 
                INNER JOIN questions ON categories.id = questions.id_categories) 
                INNER JOIN answers ON questions.id = answers.id_questions
                WHERE questions.id_status = 2
                ORDER BY questions.id ;';

            $statement = $pdo->prepare($sql);
            $statement->execute();

            while ($row = $statement->fetch(\PDO::FETCH_OBJ)) {
                $this->array[] = new Answer( $row->categoryId, '', $row->questionId, '', null, null, null, $row->answerId, $row->answerName );
            }

            return $this->array;
        } catch (\Exception $e) {
            throw new \RuntimeException('Ошибка получения списка опубликованных ответов', 0, $e);
        }
    }

    /**
     * @param \PDO $pdo
     * @return array
     */
    public function getAdmins(\PDO $pdo)
    {
        try {
            $this->array = [];
            $sql = 'SELECT admins.id, admins.login, admins.password FROM admins ORDER BY admins.login;';
            $statement = $pdo->prepare($sql);
            $statement->execute();

            while ($row = $statement->fetch(\PDO::FETCH_OBJ)) {
                $this->array[] = new Admin($row->id, $row->login, $row->password);
            }

            return $this->array;
        } catch (\Exception $e) {
            throw new \RuntimeException('Ошибка получения списка администраторов', 0, $e);
        }
    }

}