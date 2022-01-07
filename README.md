# DCSLab

Doctor Computer SG Lab

This project is inspired by the desire to create an up to date web app boilerplate and keep evolving.

A web application focusing on the usage of the most popular framework, handpicked with consideration. To meet the everyday programming/coding obstacle and giving the best solution that we can find. Sometimes google/stackoverflow searching is enough to satisfied our curiosity 
but complex issue such as inter-related components, multi vendors applications problems, hardware
software compatibility, bad/slow performances of projects, is something that you can only face it if you do a real projects.

Interested? let discuss it together [here](https://github.com/GitzJoey/DCSLab/discussions)

## Features
* Role Based System
* Internal Messaging System
* Auditing Tools
* Multi language
* Single Page Application
* Secure Coding Paradigm
* and more...

## Minimal Requirement
* [PHP](https://www.php.net/downloads.php) (8.0.2)
* [Laravel](https://laravel.com/) (8.55.0)
* [MySQL](https://www.mysql.com/downloads/) (8.0.21)
* [Git](https://git-scm.com/downloads) (2.32.0)
* [NodeJS/NPM](https://nodejs.org/en/download/) (14.16.0/7.21.0)
* [Composer](https://getcomposer.org/download/) (2.0.11)

## Installation

Clone Repository

>`$ git clone https://github.com/GitzJoey/DCSLab.git DCSLab`

Create .env file

>`$ copy .env.example .env`

Fill the required config in .env file

eg database config:
> DB_CONNECTION=mysql  
> DB_HOST=127.0.0.1  
> DB_PORT=3306  
> DB_DATABASE=laravel  
> DB_USERNAME=root  
> DB_PASSWORD=

Run the installation scripts

>`$ php artisan app:install`

## Updates

Upon available updates, pull the project to the latest

>`$ git pull`

Recompile

>`$ php artisan app:helper`  
>`<choose option 3>`

## History
* **2014-11-04**
    * 1st concept of POS materialize in [GitHub](https://github.com/GitzJoey/TKBARUJAVA)
    * more clearer concept of 'always update always evolving' in [PHP](https://github.com/GitzJoey/TKBARUPHP)
    * update the [PHP project](https://github.com/GitzJoey/TKBARUPHP) to [SPA/PWA](https://github.com/GitzJoey/TKBARUSPA)

* **2021-04-05**
    * Reboot from [TKBARUSPA](https://www.github.com/gitzjoey/TKBARUSPA)
    * Officially using [CodeBase template](https://themeforest.net/item/codebase-bootstrap-4-admin-dashboard-template-ui-framework/20289243)

* **2021-04-15**
    * Using new laravel/fortify and laravel/sanctum
    * Utilize VueJS 3 as components

* **2021-08-21**
    * Split the code into master and boilerplate (for easier upgrade)
    * Messaging System

* **2021-10-07**
    * Embrace the Laravel + VueJS Style
    * Slimmer landing page and template dependent

* **2021-12-07**
    * Embrace PHP 8 (named arguments, arguments types, return types)
    * Adding test unit for service layers and api code
