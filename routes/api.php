<?php

use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [UserController::class, 'login']);

/*START ADMIN ROUTES */
Route::prefix('institution')->controller(InstitutionController::class)->group(function(){
    Route::get('all', 'get_all_institution');
    Route::post('add', 'create_institution');
    Route::patch('update', 'update_institution');
});

Route::prefix('roles')->controller(RoleController::class)->group(function(){
    Route::get('all', 'get_all_roles');
    Route::post('add', 'create_role');
});

Route::prefix('permissions')->controller(PermissionController::class)->group(function(){
    Route::get('all', 'get_all_permissions');
    Route::post('add', 'create_permission');
});
/*END ADMIN ROUTES */

Route::prefix('users')->controller(UserController::class)->group(function(){
    Route::get('all_by_institutions/{institution_id}', 'get_users_by_institutions');
    Route::get('all_users/{institution_id}', 'get_all_users');
    Route::get('{slug}', 'get_users_by_role');
    Route::post('add', 'create_user');
});

Route::prefix('institution_sections')->controller(SectionController::class)->group(function(){
    Route::get('all_by_institutions/{institution_id}', 'get_all_sections');
    Route::get('get_by_user/{user_id}', 'get_by_user');
    Route::get('subjects', 'get_section_subjects');
    Route::get('{section_id}', 'get_section_details');
    Route::post('add', 'create_section');
    Route::post('add_with_subjects', 'creaet_section_with_subject');
    Route::post('update', 'update_section');
});

Route::group(['prefix' => 'subjects', 'controller' => SubjectController::class], function(){
    Route::get('by_section/{section_id}', 'get_subjects_by_section');
    Route::get('by_user/{user_id}', 'get_subjects_by_user');
    Route::get('{subject_id}', 'get_subject_details');
    Route::put('{subject_id}', 'update_subject');
    Route::post('validate_conflict', 'check_for_teacher_conflict');
    Route::post('add', 'create_subject');
    Route::delete('{subject_id}', 'delete_subject');
});

Route::group(['prefix' => 'students', 'controller' => StudentController::class], function(){
    Route::post('add', 'create_student');
    Route::post('submit_grades', 'submit_grade');
    Route::put('update', 'update_student');
});