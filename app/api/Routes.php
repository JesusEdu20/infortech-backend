<?php

require_once APP_ROOT . '/app/Core/Router.php';

/* Router::get('usuarios', 'UserController@index');
Router::post('usuarios', 'UserController@store');
Router::get('usuarios/show', function () {
    echo json_encode(['mensaje' => 'Usuario único']);
});

Router::get('test', function () {
    echo json_encode(['mensaje' => 'Ruta de prueba']);
});


Router::get('usuarios/{id}/posts/{postId}', 'UserController@detail');
Router::get('more', 'UserController@more'); */


/* Router::post('solicitudes', 'CotizacionController@solicitar'); */

/* Cotizaciones */
Router::post('cotizaciones', 'QuotationsController@createQuotation');
Router::get('cotizaciones', 'QuotationsController@getQuotations');
Router::get('cotizaciones/{id}', 'QuotationsController@getQuotationById');
Router::delete('cotizaciones/{id}', 'QuotationsController@deleteQuotation');

Router::put('cotizaciones/{id}', 'QuotationsController@updateQuotation');

/* items */
Router::put('cotizaciones/approve/{id}/items', 'QuotationsController@approveQuotation');
Router::post('cotizaciones/{id}/items', 'QuotationsController@addItemsToQuotation');
