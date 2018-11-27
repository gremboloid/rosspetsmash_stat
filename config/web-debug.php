<?php

$db = require (__DIR__.'/db-debug.php');
return [
    'id' => 'rosspetsmashstat',
    'basePath' => realpath(__DIR__ .'/../'),
    'bootstrap' => ['debug','log'],                  // модули, запускаемые с самого начала
    'language' => 'ru-Ru',
    'layout' => false,
    'modules' => [
        'debug' => [
            'class' => 'yii\debug\Module',
            // uncomment and adjust the following to add your IP if you are not connecting from localhost.
            //'allowedIPs' => ['127.0.0.1', '::1'],
        ],
    ],
    'defaultRoute' => 'index',
    'components' => [
        'db' => $db,
        'user' => [
            'identityClass' => 'app\models\user\UserIdentity',
            'enableAutoLogin' => false
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'pattern' => 'home',
                    'route' => 'index/index',
                    'suffix' => ''
                ],
                 'news/<id:\d+>' => 'news',
                '<_c:admin>/<_a:[\w-]+>' => '<_c>',
                '<_c:[\w\-]+>/<_a:[\w-]+>' => '<_c>/<_a>',
                
            ]
        ],
        'languageModule' => [
            'class' => 'app\stat\lang\Language',
            'isoCode' => 'ru' // язык по умолчанию          
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'ncQzFzwncrxWjVXysDsWX2XbITJNyfR5',
        ],
        'view' => [
            'class' => 'yii\web\View',             
            'renderers' => [
                'twig' => [                    
                    'class' => 'yii\twig\ViewRenderer',
             //      'cachePath' => '@runtime/Twig/cache',
                    'cachePath' => false,
                    // Array of twig options:
                    'options' => [
                        'auto_reload' => true,
                    ],
                    'globals' => [
                        'Url' => ['class' => '\yii\helpers\Url'],
                        'html' => ['class' => '\yii\helpers\Html'],
                        'active_form' => ['class' => '\yii\widgets\ActiveForm']
                    ],
                    'uses' => ['yii\bootstrap'],
                     'twigFallbackPaths' => [
                        'layouts' => '@app/views/layouts' //возможно использование yii2-алиасов
                    ],
                    'functions' => [
                        'print_r' => 'print_r'
                    ],
                ],
                // ...
            ],
        ],        
    ],
];