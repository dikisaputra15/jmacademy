<?php

use App\Http\Controllers\CourseCategoryController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CurriculumController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\StudentCourseController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::view('/login', 'pages.auth.login')->name('login');
    Route::view('/register', 'pages.auth.register')->name('register');
});

Route::view('/home', 'pages.dashboard')
    ->middleware(['auth', 'active'])
    ->name('home');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/regions/{level}/{parent?}', [RegionController::class, 'index'])
        ->whereIn('level', ['provinces', 'regencies'])
        ->whereNumber('parent')
        ->name('regions.index');
});

Route::middleware(['auth', 'active', 'role:student'])->group(function () {
    Route::get('/all-courses', [StudentCourseController::class, 'index'])->name('student-courses.index');
});

Route::middleware(['auth', 'active', 'role:admin'])->group(function () {
    Route::patch('/management-users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])
        ->name('users.toggle-status');
    Route::resource('/management-users', UserManagementController::class)
        ->except('show')
        ->parameters(['management-users' => 'user'])
        ->names([
            'index' => 'users.index',
            'create' => 'users.create',
            'store' => 'users.store',
            'edit' => 'users.edit',
            'update' => 'users.update',
            'destroy' => 'users.destroy',
        ]);

    Route::patch('/course-categories/{courseCategory}/toggle-status', [CourseCategoryController::class, 'toggleStatus'])
        ->name('course-categories.toggle-status');
    Route::resource('/course-categories', CourseCategoryController::class)
        ->except('show')
        ->parameters(['course-categories' => 'courseCategory']);

    Route::patch('/courses/{course}/toggle-status', [CourseController::class, 'toggleStatus'])->name('courses.toggle-status');
    Route::get('/courses/{course}/curriculum', [CurriculumController::class, 'edit'])->name('courses.curriculum');
    Route::post('/courses/{course}/curriculum/sections', [CurriculumController::class, 'storeSection'])->name('curriculum.sections.store');
    Route::put('/curriculum/sections/{section}', [CurriculumController::class, 'updateSection'])->name('curriculum.sections.update');
    Route::delete('/curriculum/sections/{section}', [CurriculumController::class, 'destroySection'])->name('curriculum.sections.destroy');
    Route::post('/curriculum/sections/{section}/lessons', [CurriculumController::class, 'storeLesson'])->name('curriculum.lessons.store');
    Route::delete('/curriculum/lessons/{lesson}', [CurriculumController::class, 'destroyLesson'])->name('curriculum.lessons.destroy');
    Route::resource('/courses', CourseController::class)->except('show');
});
