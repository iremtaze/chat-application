<?php

use App\Controllers\UserController;
use App\Controllers\GroupController;
use App\Controllers\MessageController;
use App\Services\UserService;
use App\Services\GroupService;
use App\Services\MessageService;
use App\Models\UserModel;
use App\Models\GroupModel;
use App\Models\MessageModel;

//Dependencies configuration for PHP-DI
return [
    //Database connection
    PDO::class => function() {
        $dbPath = __DIR__ . '/../database/chat.sqlite';
        //Create database directory if it doesn't exist
        $dbDirectory = dirname($dbPath);
        if (!is_dir($dbDirectory)) {
            mkdir($dbDirectory, 0777, true);
        }
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    },

    //Models
    UserModel::class => function($container) {
        return new UserModel($container->get(PDO::class));
    },
    
    GroupModel::class => function($container) {
        return new GroupModel($container->get(PDO::class));
    },
    
    MessageModel::class => function($container) {
        return new MessageModel($container->get(PDO::class));
    },

    //Services
    UserService::class => function($container) {
        return new UserService($container->get(UserModel::class));
    },
    
    GroupService::class => function($container) {
        return new GroupService($container->get(GroupModel::class));
    },
    
    MessageService::class => function($container) {
        return new MessageService(
            $container->get(MessageModel::class),
            $container->get(GroupModel::class)
        );
    },

    //Controllers
    UserController::class => function($container) {
        return new UserController($container->get(UserService::class));
    },
    
    GroupController::class => function($container) {
        return new GroupController(
            $container->get(GroupService::class),
            $container->get(UserService::class)
        );
    },
    
    MessageController::class => function($container) {
        return new MessageController(
            $container->get(MessageService::class),
            $container->get(GroupService::class),
            $container->get(UserService::class)
        );
    },
];