<?php

use App\Http\Controllers\Api\HealthController;

// Auth & account
use App\Http\Controllers\Api\V1\Admin\FacultyAdminController;
use App\Http\Controllers\Api\V1\Admin\GradeScaleAdminController;
use App\Http\Controllers\Api\V1\Admin\InstitutionAdminController;
use App\Http\Controllers\Api\V1\Admin\InterestAreaAdminController;

// Public catalogue
use App\Http\Controllers\Api\V1\Admin\InterestQuestionAdminController;
use App\Http\Controllers\Api\V1\Admin\ProgrammeAdminController;
use App\Http\Controllers\Api\V1\Admin\ProgrammeRequirementAdminController;
use App\Http\Controllers\Api\V1\Admin\RequirementFlagAdminController;
use App\Http\Controllers\Api\V1\Admin\SubjectAdminController;
use App\Http\Controllers\Api\V1\AuthController;

// Student domain (protected)
use App\Http\Controllers\Api\V1\Catalog\FacultyController;
use App\Http\Controllers\Api\V1\Catalog\InstitutionController;
use App\Http\Controllers\Api\V1\Catalog\InterestAreaController;

// Recs & saves (protected)
use App\Http\Controllers\Api\V1\Catalog\ProgrammeController;
use App\Http\Controllers\Api\V1\Catalog\RequirementController;
use App\Http\Controllers\Api\V1\Catalog\SubjectController;

// Admin (protected + role:admin)
use App\Http\Controllers\Api\V1\ConsentController;
use App\Http\Controllers\Api\V1\FeedbackController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\RecommendationController;
use App\Http\Controllers\Api\V1\SavedProgrammeController;
use App\Http\Controllers\Api\V1\Student\ExamController;
use App\Http\Controllers\Api\V1\Student\InterestController;
use App\Http\Controllers\Api\V1\Student\ProfileController;
use App\Http\Controllers\Api\V1\UserRoleController;
use App\Http\Controllers\Api\VersionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // -------------------------
    // Public: Auth
    // -------------------------
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // -------------------------
    // Public: Catalogue
    // -------------------------
    Route::get('/catalog/institutions', [InstitutionController::class, 'index']);
    Route::get('/catalog/faculties', [FacultyController::class, 'index']); // ?institution_id=
    Route::get('/catalog/subjects', [SubjectController::class, 'index']);
    Route::get('/catalog/interest-areas', [InterestAreaController::class, 'index']);

    Route::get('/catalog/programmes', [ProgrammeController::class, 'index']); // filters: q, institution_id, faculty_id, interest_area_id, course_type, per_page
    Route::get('/catalog/programmes/{programme}', [ProgrammeController::class, 'show']);
    Route::get('/catalog/programmes/{programme}/requirements', [RequirementController::class, 'show']);

    // -------------------------
    // Protected (auth:sanctum)
    // -------------------------
    Route::middleware('auth:sanctum')->group(function () {
        // Account
        Route::get('/me', [MeController::class, 'show']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Consents
        Route::post('/consents', [ConsentController::class, 'store']);

        // Student profile
        Route::get('/student/profile', [ProfileController::class, 'show']);
        Route::put('/student/profile', [ProfileController::class, 'upsert']);

        // Exam results
        Route::get('/student/exam-results', [ExamController::class, 'show']);
        Route::put('/student/exam-results', [ExamController::class, 'upsert']);

        // Interests
        Route::get('/student/interest-questions', [InterestController::class, 'questions']);
        Route::post('/student/interest-assessments', [InterestController::class, 'submit']);
        Route::get('/student/interest-assessments/latest', [InterestController::class, 'latest']);

                                                                                                 // Recommendations
        Route::post('/recommendations/generate', [RecommendationController::class, 'generate']); // body: {top_n?}
        Route::get('/recommendations/runs', [RecommendationController::class, 'index']);
        Route::get('/recommendations/runs/{run}', [RecommendationController::class, 'show']);

        // after other recommendation routes, still inside auth:sanctum group
        Route::get('/recommendations/items/{item}', [RecommendationController::class, 'showItem']);

        // Saved programmes
        Route::get('/student/saved-programmes', [SavedProgrammeController::class, 'index']);
        Route::post('/student/saved-programmes', [SavedProgrammeController::class, 'store']); // {programme_id, note?}
        Route::delete('/student/saved-programmes/{programme}', [SavedProgrammeController::class, 'destroy']);
        Route::patch('/student/saved-programmes/{programme}/note', [SavedProgrammeController::class, 'updateNote']);

                                                                                                     // Feedback on recommendation items
        Route::post('/recommendations/items/{item}/feedback', [FeedbackController::class, 'store']); // {rating_1_5, comment?}
        Route::get('/recommendations/feedback', [FeedbackController::class, 'myFeedback']);

        // -------------------------
        // Admin (role:admin)
        // -------------------------
        Route::middleware(['role:admin', 'throttle:admin-api'])->group(function () {
            // user-role management
            Route::get('/admin/roles', [UserRoleController::class, 'listRoles']);
            Route::get('/admin/users/{user}/roles', [UserRoleController::class, 'getUserRoles']);
            Route::post('/admin/users/{user}/roles/sync', [UserRoleController::class, 'sync']);
            Route::get('/admin/users', [UserRoleController::class, 'index']);

            // catalogue admin CRUD
            Route::get('/admin/institutions', [InstitutionAdminController::class, 'index']);
            Route::post('/admin/institutions', [InstitutionAdminController::class, 'store']);
            Route::patch('/admin/institutions/{institution}', [InstitutionAdminController::class, 'update']);
            Route::delete('/admin/institutions/{institution}', [InstitutionAdminController::class, 'destroy']);

            Route::get('/admin/faculties', [FacultyAdminController::class, 'index']);
            Route::post('/admin/faculties', [FacultyAdminController::class, 'store']);
            Route::patch('/admin/faculties/{faculty}', [FacultyAdminController::class, 'update']);
            Route::delete('/admin/faculties/{faculty}', [FacultyAdminController::class, 'destroy']);

            Route::get('/admin/programmes', [ProgrammeAdminController::class, 'index']);
            Route::post('/admin/programmes', [ProgrammeAdminController::class, 'store']);
            Route::patch('/admin/programmes/{programme}', [ProgrammeAdminController::class, 'update']);
            Route::delete('/admin/programmes/{programme}', [ProgrammeAdminController::class, 'destroy']);

            // Subjects admin
            Route::get('/admin/subjects', [SubjectAdminController::class, 'index']);
            Route::post('/admin/subjects', [SubjectAdminController::class, 'store']);
            Route::patch('/admin/subjects/{subject}', [SubjectAdminController::class, 'update']);
            Route::delete('/admin/subjects/{subject}', [SubjectAdminController::class, 'destroy']);

            // Aliases
            Route::get('/admin/subjects/{subject}/aliases', [SubjectAdminController::class, 'aliases']);
            Route::post('/admin/subjects/{subject}/aliases', [SubjectAdminController::class, 'addAlias']);
            Route::delete('/admin/aliases/{alias}', [SubjectAdminController::class, 'deleteAlias']);

            // Interest Areas
            Route::get('/admin/interest-areas', [InterestAreaAdminController::class, 'index']);
            Route::post('/admin/interest-areas', [InterestAreaAdminController::class, 'store']);
            Route::patch('/admin/interest-areas/{area}', [InterestAreaAdminController::class, 'update']);
            Route::delete('/admin/interest-areas/{area}', [InterestAreaAdminController::class, 'destroy']);

            // Interest Questions
            Route::get('/admin/interest-questions', [InterestQuestionAdminController::class, 'index']);
            Route::post('/admin/interest-questions', [InterestQuestionAdminController::class, 'store']);
            Route::patch('/admin/interest-questions/{question}', [InterestQuestionAdminController::class, 'update']);
            Route::delete('/admin/interest-questions/{question}', [InterestQuestionAdminController::class, 'destroy']);

            // Requirement Flags
            Route::get('/admin/requirement-flags', [RequirementFlagAdminController::class, 'index']);
            Route::post('/admin/requirement-flags', [RequirementFlagAdminController::class, 'store']);
            Route::patch('/admin/requirement-flags/{flag}', [RequirementFlagAdminController::class, 'update']);
            Route::delete('/admin/requirement-flags/{flag}', [RequirementFlagAdminController::class, 'destroy']);

            // Grade Scales
            Route::get('/admin/grade-scales', [GradeScaleAdminController::class, 'index']);
            Route::post('/admin/grade-scales', [GradeScaleAdminController::class, 'store']);
            Route::patch('/admin/grade-scales/{scale}', [GradeScaleAdminController::class, 'update']);
            Route::delete('/admin/grade-scales/{scale}', [GradeScaleAdminController::class, 'destroy']);

            // Grade Mappings
            Route::get('/admin/grade-scales/{scale}/mappings', [GradeScaleAdminController::class, 'mappings']);
            Route::post('/admin/grade-scales/{scale}/mappings', [GradeScaleAdminController::class, 'addMapping']);
            Route::patch('/admin/grade-mappings/{mapping}', [GradeScaleAdminController::class, 'updateMapping']);
            Route::delete('/admin/grade-mappings/{mapping}', [GradeScaleAdminController::class, 'deleteMapping']);

            // Programme Requirement Builder
            Route::get('/admin/programmes/{programme}/requirements', [ProgrammeRequirementAdminController::class, 'show']);

            // Items
            Route::post('/admin/programmes/{programme}/requirements/items', [ProgrammeRequirementAdminController::class, 'storeItem']);
            Route::patch('/admin/programmes/{programme}/requirements/items/{item}', [ProgrammeRequirementAdminController::class, 'updateItem']);
            Route::delete('/admin/programmes/{programme}/requirements/items/{item}', [ProgrammeRequirementAdminController::class, 'destroyItem']);

            // Constraints
            Route::post('/admin/programmes/{programme}/requirements/items/{item}/constraints', [ProgrammeRequirementAdminController::class, 'storeConstraint']);
            Route::patch('/admin/programmes/{programme}/requirements/constraints/{constraint}', [ProgrammeRequirementAdminController::class, 'updateConstraint']);
            Route::delete('/admin/programmes/{programme}/requirements/constraints/{constraint}', [ProgrammeRequirementAdminController::class, 'destroyConstraint']);

            // Choice Groups
            Route::patch('/admin/programmes/{programme}/requirements/choice-groups/{group}', [ProgrammeRequirementAdminController::class, 'updateChoiceGroup']);
            Route::post('/admin/programmes/{programme}/requirements/choice-groups/{group}/subjects', [ProgrammeRequirementAdminController::class, 'addChoiceGroupSubject']);
            Route::delete('/admin/programmes/{programme}/requirements/choice-groups/{group}/subjects/{subject}', [ProgrammeRequirementAdminController::class, 'removeChoiceGroupSubject']);
        });
    });
});

Route::get('/health', HealthController::class); // 200 ok | 503 degraded
Route::get('/v1/version', [VersionController::class, 'show']);
