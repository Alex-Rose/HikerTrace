<?php

Route::set('auth', '<action>', ['action' => '(login|logout|register|forgot)'])
    ->defaults(array(
        'controller' => 'auth',
        'action'     => 'login',
    ));
Route::set('default', '(<controller>(/<action>(/<id>(/<option>(/<option1>)))))')
        ->defaults(array(
            'controller' => 'home',
            'action'     => 'index',
        ));
