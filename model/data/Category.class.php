<?php

namespace model\data;

class Category
{
    private $categoryId;
    private $categoryName;
    private $count = [];

    /**
     * Category constructor.
     * @param int $categoryId
     * @param string $categoryName
     */
    public function __construct($categoryId = 0, $categoryName = '')
    {
        $this->categoryId = $categoryId;
        $this->categoryName = $categoryName;
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param int $categoryId
     * @return $this
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategoryName()
    {
        return $this->categoryName;
    }

    /**
     * @param string $categoryName
     * @return $this
     */
    public function setCategoryName($categoryName)
    {
        $this->categoryName = $categoryName;
        return $this;
    }

    /**
     * @return array
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param array $count
     * @return $this
     */
    public function setCount(array $count)
    {
        $this->count = $count;
        return $this;
    }

    /**
     * @param \PDO $pdo
     * @return object
     */
    public function selectCategory(\PDO $pdo)
    {
        try {
            $sql = 'SELECT categories.name FROM categories WHERE categories.id = :id;';
            $statement = $pdo->prepare($sql);
            $statement->execute(['id'=>$this->categoryId]);
            return $statement->fetch(\PDO::FETCH_OBJ)->name;
        } catch (\Exception $e) {
            throw new \RuntimeException('Ошибка считывания темы', 0, $e);
        }
    }

    /**
     * @param \PDO $pdo
     */
    public function deleteCategory(\PDO $pdo)
    {
        try {
            $sql = 'DELETE FROM categories WHERE categories.id=:id;';
            $statement = $pdo->prepare($sql);
            $statement->execute(['id'=>$this->categoryId]);
        } catch (\Exception $e) {
            throw new \RuntimeException('Ошибка удаления темы', 0, $e);
        }
    }

    /**
     * @param \PDO $pdo
     */
    public function checkCategory(\PDO $pdo)
    {
        try {

            if (null === $this->categoryName || trim($this->categoryName) === '') {
                throw new \LogicException('При записи темы все поля должны быть заполнены');
            }

            $sql = 'SELECT categories.name FROM categories WHERE categories.name = :name;';
            $statement = $pdo->prepare($sql);
            $statement->execute(['name'=>$this->categoryName]);
            $row = $statement->fetch(\PDO::FETCH_OBJ);

            if ($row) {
                throw new \LogicException('Такая тема уже существует');
            }

        } catch (\RuntimeException $e) {
            throw new \RuntimeException('Ошибка при валидации записи темы', 0, $e);
        } catch (\LogicException $e) {
            throw $e;
        }
    }

    /**
     * @param \PDO $pdo
     */
    public function updateCategory(\PDO $pdo)
    {
        try {
            $sql = 'UPDATE categories SET categories.name = :name WHERE categories.id = :id;';
            $statement = $pdo->prepare($sql);
            $statement->execute(['id'=>$this->categoryId, 'name'=>$this->categoryName]);
        } catch (\Exception $e) {
            throw new \RuntimeException('Ошибка изменения темы', 0 , $e);
        }
    }

    /**
     * @param \PDO $pdo
     */
    public function insertCategory(\PDO $pdo)
    {
        try {
            $sql = 'INSERT INTO categories ( name ) VALUES (:name);';
            $statement = $pdo->prepare($sql);
            $statement->execute(['name'=>$this->categoryName]);
        } catch (\Exception $e) {
            throw new \RuntimeException('Ошибка добавления темы', 0, $e);
        }
    }

}