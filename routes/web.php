<?php

use App\Http\Controllers\CourseCategoryController;
use App\Http\Controllers\AdminTeacherSalaryController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CurriculumController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PaidScheduleController;
use App\Http\Controllers\PendingReportController;
use App\Http\Controllers\ParentReportController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\StudentCourseController;
use App\Http\Controllers\StudentClassController;
use App\Http\Controllers\StudentRegistrationController;
use App\Http\Controllers\StudentReportController;
use App\Http\Controllers\TeacherPaidScheduleController;
use App\Http\Controllers\TeacherSalaryController;
use App\Http\Controllers\TeacherHistoryController;
use App\Http\Controllers\TeacherTrialScheduleController;
use App\Http\Controllers\TeacherCourseController;
use App\Http\Controllers\TrialScheduleController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::view('/login', 'pages.auth.login')->name('login');
    Route::view('/register', 'pages.auth.register')->name('register');
});

Route::get('/home', [DashboardController::class, 'index'])
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
    Route::get('/report-study', [StudentReportController::class, 'index'])->name('student-reports.index');
    Route::get('/report-study/{report}', [StudentReportController::class, 'show'])->name('student-reports.show');
    Route::get('/report-study/{report}/certificate', [StudentReportController::class, 'certificate'])->name('student-reports.certificate');
    Route::get('/my-classes', [StudentClassController::class, 'index'])->name('student-classes.index');
    Route::get('/my-classes/schedules/{schedule}/join', [StudentClassController::class, 'join'])
        ->name('student-classes.join');
    Route::get('/all-courses', [StudentCourseController::class, 'index'])->name('student-courses.index');
    Route::get('/all-courses/{course}/payment', [StudentCourseController::class, 'payment'])->name('student-courses.payment');
    Route::post('/all-courses/{course}/enroll', [StudentCourseController::class, 'enroll'])->name('student-courses.enroll');
    Route::get('/my-transactions', [StudentCourseController::class, 'transactions'])->name('student-transactions.index');
});

Route::middleware(['auth', 'active', 'role:guru'])->group(function () {
    Route::get('/teacher/histories', [TeacherHistoryController::class, 'index'])
        ->name('teacher-histories.index');
    Route::get('/teacher/parent-reports', [ParentReportController::class, 'index'])->name('parent-reports.index');
    Route::get('/teacher/parent-reports/{transaction}', [ParentReportController::class, 'create'])->name('parent-reports.create');
    Route::post('/teacher/parent-reports/{transaction}', [ParentReportController::class, 'store'])->name('parent-reports.store');
    Route::get('/teacher/my-salary', [TeacherSalaryController::class, 'index'])
        ->name('teacher-salaries.index');
    Route::get('/teacher/my-salary/payouts/{payout}/proof', [TeacherSalaryController::class, 'proof'])
        ->name('teacher-salaries.proof');
    Route::get('/teacher/courses', [TeacherCourseController::class, 'index'])
        ->name('teacher-courses.index');
    Route::get('/teacher/paid-schedules', [TeacherPaidScheduleController::class, 'index'])
        ->name('teacher-paid-schedules.index');
    Route::get('/teacher/paid-schedules/{schedule}/join', [TeacherPaidScheduleController::class, 'join'])
        ->name('teacher-paid-schedules.join');
    Route::get('/teacher/trial-schedules', [TeacherTrialScheduleController::class, 'index'])
        ->name('teacher-trial-schedules.index');
    Route::get('/teacher/trial-schedules/{schedule}/join', [TeacherTrialScheduleController::class, 'join'])
        ->name('teacher-trial-schedules.join');
    Route::get('/teacher/pending-reports', [PendingReportController::class, 'index'])->name('pending-reports.index');
    Route::get('/teacher/pending-reports/create', [PendingReportController::class, 'select'])->name('pending-reports.select');
    Route::get('/teacher/pending-reports/{schedule}', [PendingReportController::class, 'create'])->name('pending-reports.create');
    Route::post('/teacher/pending-reports/{schedule}', [PendingReportController::class, 'store'])->name('pending-reports.store');
});

Route::middleware(['auth', 'active', 'role:admin'])->group(function () {
    Route::get('/teacher-salaries', [AdminTeacherSalaryController::class, 'index'])->name('admin-teacher-salaries.index');
    Route::get('/teacher-salaries/payouts/{payout}/proof', [AdminTeacherSalaryController::class, 'proof'])->name('admin-teacher-salaries.proof');
    Route::get('/teacher-salaries/{teacher}', [AdminTeacherSalaryController::class, 'create'])->name('admin-teacher-salaries.create');
    Route::post('/teacher-salaries/{teacher}', [AdminTeacherSalaryController::class, 'store'])->name('admin-teacher-salaries.store');
    Route::get('/paid-schedules', [PaidScheduleController::class, 'index'])->name('paid-schedules.index');
    Route::post('/paid-schedules', [PaidScheduleController::class, 'store'])->name('paid-schedules.store');
    Route::get('/trial-schedules', [TrialScheduleController::class, 'index'])->name('trial-schedules.index');
    Route::post('/trial-schedules', [TrialScheduleController::class, 'store'])->name('trial-schedules.store');

    Route::get('/student-registers', [StudentRegistrationController::class, 'index'])
        ->name('student-registrations.index');
    Route::get('/student-registers/{transaction}/proof', [StudentRegistrationController::class, 'proof'])
        ->name('student-registrations.proof');
    Route::patch('/student-registers/{transaction}/verify', [StudentRegistrationController::class, 'verify'])
        ->name('student-registrations.verify');

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
