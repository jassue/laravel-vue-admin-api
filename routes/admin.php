<?php

use App\Domain\Admin\Config\PermissionEnum;

Route::group(['namespace' => 'Api\Admin', 'prefix' => 'admin'], function () {
    Route::post('/login', 'AuthController@login');

    Route::group(['middleware' => 'auth:admin'], function () {
        Route::post('/logout', 'AuthController@logout');
        Route::get('/admin', 'AdminController@info');
        Route::put('/update-info', 'AdminController@updateBySelf');
        // Route::get('/admin', 'IndexController@index')->middleware('can:'. PermissionEnum::DASHBOARD);

        //管理员
        Route::group([
            'middleware' => 'can:' . PermissionEnum::ADMIN_VIEW,
            'prefix'     => 'admins'
        ], function () {
            Route::get('/', 'AdminController@getList')->middleware('can:' . PermissionEnum::ADMIN_VIEW);
            Route::post('/', 'AdminController@store')->middleware('can:' . PermissionEnum::ADMIN_CREATE);
            Route::get('/{id}', 'AdminController@detail')->middleware('can:' . PermissionEnum::ADMIN_UPDATE);
            Route::put('/{id}', 'AdminController@update')->middleware('can:' . PermissionEnum::ADMIN_UPDATE);
            Route::delete('/{id}', 'AdminController@destroy')->middleware('can:' . PermissionEnum::ADMIN_DELETE);

            Route::get('/roles', 'AdminController@getRoleListForCreateOrUpdate')->middleware('can-any:' . PermissionEnum::ADMIN_CREATE . '|' . PermissionEnum::ADMIN_UPDATE);
            Route::patch('/toggle-status', 'AdminController@toggleStatus')->middleware('can:' . PermissionEnum::ADMIN_VIEW);
            Route::delete('/batch', 'AdminController@batchDestroy')->middleware('can:' . PermissionEnum::ADMIN_DELETE);
        });

        //管理员角色
        Route::group([
            'middleware' => 'can:' . PermissionEnum::ROLE_VIEW,
            'prefix'     => 'roles'
        ], function () {
            Route::get('/', 'AdminController@getRoleList')->middleware('can:' . PermissionEnum::ROLE_VIEW);
            Route::post('/', 'AdminController@roleStore')->middleware('can:' . PermissionEnum::ROLE_CREATE);
            Route::get('/{id}', 'AdminController@roleDetail')->middleware('can:' . PermissionEnum::ROLE_UPDATE);
            Route::put('/{id}', 'AdminController@roleUpdate')->middleware('can:' . PermissionEnum::ROLE_UPDATE);
            Route::delete('/{id}', 'AdminController@roleDestroy')->middleware('can:' . PermissionEnum::ROLE_DELETE);

            Route::get('/permissions', 'AdminController@getPermissionListForCreateOrUpdate')->middleware('can-any:' . PermissionEnum::ROLE_CREATE . '|' . PermissionEnum::ROLE_UPDATE);
            Route::delete('/batch', 'AdminController@roleBatchDestroy')->middleware('can:' . PermissionEnum::ADMIN_DELETE);
        });
    });
});
