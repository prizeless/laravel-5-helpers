Laravel Helpers
============================

composer require prizeless/laravel-5-helpers "dev-master"


This package contains base classes to enable easy database CRUD.
If you are going to extend the UuidModel you must add a uuid column to your database table.

All your repository classes must extend Laravel5Helpers\Repositories\Repository
All your definitions classes must extend Laravel5Helpers\Definitions\Definition

Checkout the examples in example folder