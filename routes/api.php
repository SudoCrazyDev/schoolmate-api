<?php

use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\MetaController;
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
    Route::post('update', 'update_institution');
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
    Route::get('validate/{email}', 'validate_email');
    Route::post('add', 'create_user');
    Route::put('update/{user_id}', 'update_user');
    Route::put('role/{user_id}', 'update_user_role');
    Route::post('update-password', 'update_user_password');
});

Route::prefix('institution_sections')->controller(SectionController::class)->group(function(){
    Route::get('all_by_institutions/{institution_id}', 'get_all_sections');
    Route::get('get_by_user/{user_id}', 'get_by_user');
    Route::get('subjects', 'get_section_subjects');
    Route::get('{section_id}', 'get_section_details');
    Route::post('add', 'create_section');
    Route::post('add_with_subjects', 'creaet_section_with_subject');
    Route::post('update', 'update_section');
    Route::delete('delete/{section_id}', 'delete_section');
});

Route::group(['prefix' => 'subjects', 'controller' => SubjectController::class], function(){
    Route::get('by_section/{section_id}', 'get_subjects_by_section');
    Route::get('by_user/{user_id}', 'get_subjects_by_user');
    Route::get('{subject_id}', 'get_subject_details');
    Route::put('unlock_grades/{subject_id}', 'unlock_subject_grades');
    Route::put('{subject_id}', 'update_subject');
    Route::post('validate_conflict', 'check_for_teacher_conflict');
    Route::post('add', 'create_subject');
    Route::delete('{subject_id}', 'delete_subject');
});

Route::group(['prefix' => 'students', 'controller' => StudentController::class], function(){
    Route::post('add', 'create_student');
    Route::post('submit_grades', 'submit_grade');
    Route::post('submit_core_values', 'submit_observed_values');
    Route::put('update/{student_id}', 'update_student');
    Route::put('unlock_grade/{grade_id}', 'unlock_student_grade');
    Route::get('count/section/{section_id}', 'count_students_per_section');
    Route::get('count/institution/{institution_id}', 'count_students_per_institution');
    Route::get('info/{student_id}', 'get_student_info');
});

Route::group(['prefix' => 'meta', 'controller' => MetaController::class], function(){
    Route::get('grade_access/{institution_id}', 'get_grades_access');
    Route::put('update_grading_access/{institution_id}', 'update_grading_access');
});