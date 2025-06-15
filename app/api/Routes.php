<?php

require_once APP_ROOT . '/app/Core/Router.php';

Router::get('usuarios', 'UserController@index');
Router::post('usuarios', 'UserController@store');
Router::get('usuarios/show', function () {
    echo json_encode(['mensaje' => 'Usuario único']);
});

Router::get('test', function () {
    echo json_encode(['mensaje' => 'Ruta de prueba']);
});


Router::get('usuarios/{id}/posts/{postId}', 'UserController@detail');
