Система разрабатывалась и тестировалась под Windows
Apache-2.4-x64+Nginx-1.10
PHP-5.6-x64
MySQL-5.6-x64

Установка:
1.Выполнить git clone https://github.com/eastukalov/php-diplom.git в консоли в целевой папке проекта
2. там же выполнить composer install
3. Создать базу данных на MySQL и запустить там скрипт faq.sql
4. В model/db/connection.php прописать константы подключения к базе данных по п.3