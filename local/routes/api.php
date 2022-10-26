<?php
use Bitrix\Main\Routing\RoutingConfigurator;
use Vendor\Project\Basic\Controller\IBlockElementController;

return function (RoutingConfigurator $routes) {
    $routes
        ->prefix('api/iblock/element')
        ->name('api_iblock_element_')
        ->group(function (RoutingConfigurator $routes) {
                $routes->name('get')    ->get( 'get/{id}', [IBlockElementController::class,'getById']);
                $routes->name('add')    ->post('add/', [IBlockElementController::class,'add']);
                $routes->name('update') ->post('update/', [IBlockElementController::class,'update']);
                $routes->name('delete') ->post('delete/{id}', [IBlockElementController::class,'delete']);
                $routes->name('test') ->get('catalog/{sid}/{id}/recommended', [IBlockElementController::class,'test']);
            }
        );
};