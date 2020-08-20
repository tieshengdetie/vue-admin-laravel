<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

//系统接口
Route::group([
    'namespace' => 'Api',
    'prefix' => 'SystermApi',
    'middleware' => 'cors'
], function () {

    Route::group([
        'middleware' => 'checktoken'
    ], function () {

        Route::post('getUserInfo', 'UserController@getUserInfo');
        Route::post('getUserList', 'UserController@getUserList');
        Route::post('createUser', 'UserController@createUser');
        Route::post('setIsUse', 'UserController@setIsUse');
        Route::post('resetPwd', 'UserController@resetPwd');
        Route::post('getUserByName', 'UserController@getUserByName');

        Route::post('getRoleList', 'RoleController@getRoleList');
        Route::post('createRole', 'RoleController@createOrEditRole');
        Route::post('deleteRole', 'RoleController@deleteRole');
        Route::post('handlePower', 'RoleController@handlePower');


        Route::post('createOrEditMenu', 'MenuController@createOrEditMenu');
        Route::post('deleteMenu', 'MenuController@deleteMenu');
        Route::get('getFisrtMenu', 'MenuController@getFisrtMenu');
        Route::get('getMenuByRoleId', 'MenuController@getMenuByRoleId');


        Route::post('createOrEditDept', 'DeptController@createOrEditDept');
        Route::get('getDeptData', 'DeptController@getDeptData');
        Route::post('deleteDept', 'DeptController@deleteDept');

        Route::post('getTempData', 'GateController@getTempData');
        Route::post('getAllTempData', 'GateController@getAllTempData');
        Route::post('getGateList', 'GateController@getGateList');
        Route::post('getSensorList', 'GateController@getSensorList');
        Route::post('configGw', 'GateController@configGw');
        Route::post('sendLog', 'GateController@sendLog');
        Route::post('configSensor', 'GateController@configSensor');
        Route::post('getLogFile', 'GateController@getLogFile');
        Route::post('gwReboot', 'GateController@gwReboot');
        Route::post('backConnect', 'GateController@backConnect');
        Route::post('getDayData', 'GateController@getDayData');
        Route::post('getGwAndSn', 'GateController@getGwAndSn');

    });
    Route::get('downloadLog', 'GateController@downloadLog');
    Route::get('getCaptcha', 'LoginController@getCaptcha');
    Route::post('login', 'LoginController@login');

    Route::group([
        'middleware' => 'throttle:200,1'
    ], function () {

        Route::post('saveSensorsData', 'GateController@saveSensorsData');
        Route::post('saveHeartBeat', 'GateController@saveHeartBeat');
        Route::post('receiveLog', 'GateController@receiveLog');
    });


});

