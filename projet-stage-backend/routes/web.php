<?php

use App\Http\Controllers\LinkedInController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LinkedInMyPageController;
use App\Http\Controllers\linkedInOtherPageController;
use App\Http\Controllers\LinkedInMyPostsController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\deconnexionController;
use App\Http\Controllers\FacebookPageDetailsController;
use App\Http\Controllers\FacebookPagePostController;
use App\Http\Controllers\FacebookPageReactionController;
use App\Http\Controllers\FacebookPageStatisticsController;
use App\Http\Controllers\FacebookOtherPagesDetails;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\FacebookAuthController;
use App\Mail\TestEmail; 

use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;

use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\LikendinFrontOrganizationController;
use App\Http\Controllers\UserManagementController;



Route::get('/ipinfo', function () {
    try {
        $response = Http::get('https://ipapi.co/json/');
        return $response->json();
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Impossible de récupérer les infos IP',
            'message' => $e->getMessage()
        ], 500);
    }
});

// Route CSRF
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});

// Routes des organisations
Route::get('/api/organizations', [LinkedInOtherPageController::class, 'listOrganizations']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/api/organizations', [LinkedInOtherPageController::class, 'addOrganization']);
    Route::delete('/api/organizations/{id}', [LinkedInOtherPageController::class, 'deleteOrganization']);
});


Route::get('/send-test-email', function () {
    Mail::to('safaabdi930@gmail.com')->send(new TestEmail());
    return 'Email de test envoyé !';
});
//---------------------------------Facebook routes save to database-------------------------------------------------------------------
Route::get('/facebook/redirect', [FacebookAuthController::class, 'redirectToFacebook']);
Route::get('/fb/callback', [FacebookAuthController::class, 'handleFacebookCallback'])->name('facebook.callback');
Route::get('/facebook-page/{pageId}/{facebookId}', [FacebookPageDetailsController::class, 'fetchAndStorePageData']);
//Route::get('/pages_posts/{pageId}/{facebookId}', [FacebookPagePostController::class, 'fetchAllPosts']);
Route::get('/pages_posts/{facebookId}/{pageId}', [FacebookPagePostController::class, 'fetchAllPosts']);
Route::get('/Allpages_posts/{facebookId}/{pageId}', [FacebookPagePostController::class, 'fetchPagesPosts']);

Route::get('/get-user-id-by-email/{email}', [FacebookPagePostController::class, 'getUserIdByEmail']);

Route::get('/reactionspages_posts/{pageId}/{facebookId}', [FacebookPageReactionController::class, 'fetchAllReactions']);
Route::get('/update_reactions_posts/{pageId}/{facebookId}', [FacebookPageReactionController::class, 'fetchAllPagesReactions']);
Route::get('/Fetch_AllReactions', [FacebookPageReactionController::class, 'fetchAllPageREACT']);

Route::get('/api/users', [UserManagementController::class, 'index']);
Route::post('/api/users/{id}/toggle-block', [UserManagementController::class, 'toggleBlock']);
Route::post('/api/users/{id}/toggle-role', [UserManagementController::class, 'toggleRole']);
Route::get('/api/user-role', [UserManagementController::class, 'getRoleByEmail']);


Route::get('/facebookOtherPagesDetails/{facebookId}/{pageName}/{pageId}', [FacebookOtherPagesDetails::class, 'getStoreDetails']);
Route::get('/otherpages', [FacebookOtherPagesDetails::class, 'getpagesFromDatabase']);


Route::get('/statsPage/{pageId}/{facebookId}', [FacebookPageStatisticsController::class, 'fetchAllStats']);


//---------------------------------Facebook get from database-------------------------------------------------------------------
Route::get('/facebook-pageDetails', [FacebookPageDetailsController::class, 'getpageDetailFromDatabase']);
Route::get('/pageName/{idPage}', [FacebookPageDetailsController::class, 'getPageNameById']);
Route::get('/getOtherPageName/{id}', [FacebookPageDetailsController::class, 'getOtherPageName']);


//Route::get('/pages_postsDatabase/{facebookId}', [FacebookPagePostController::class, 'getpageDetailFromDatabase']);
Route::get('/pages_postsDatabase', [FacebookPagePostController::class, 'getPageDetailFromDatabase'])->middleware('auth:facebook');
Route::get('/ALLpages_postsDatabase', [FacebookPagePostController::class, 'getPageDetailFromDatabase']);


Route::get('/reactionspages_postsDatabase', [FacebookPageReactionController::class, 'getpageDetailFromDatabase'])->middleware('auth:facebook');
Route::get('/statsPage_database', [FacebookPageStatisticsController::class, 'getstatsDetailFromDatabase']);
Route::get('/pages/{facebookId}', [FacebookPageDetailsController::class, 'index']);


Route::get('/facebook/profile', [FacebookAuthController::class, 'getFacebookProfile']);
/*
Route::get('/facebook/profile', function () {
    $user = Auth::guard('facebook')->user(); 
    return response()->json([
        'user' => $user
    ]);
});*/

Route::get('/login', function () {
    return view('auth.login'); 
})->name('login');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard-facebook', [FacebookAuthController::class, 'showDashboard']);
});

Route::get('/success', function () {
    return view('success');
})->name('success.page');

Route::get('/deconnexion-facebook', [deconnexionController::class, 'deconnexionFacebook'])->name('deconnexion.facebook');


//---------------------------------LinkedIn routes -------------------------------------------------------------------

Route::get('/api/check-auth', [AuthController::class, 'checkAuth']);
Route::get('/api/deconnexion', [deconnexionController::class, 'deconnexion']);

Route::get('/check-post-thresholds', [NotificationController::class, 'checkPostThresholds']);

//Route::get('/page/{pageId}', [LinkedInMyPageController::class, 'getPageData'])->name('linkedin.page');
Route::middleware('auth')->group(function () {
    Route::get('/Myorganization/{pageId}', [LinkedInMyPageController::class, 'getDataFromDatabase']);
});

Route::get('/Get_MyorganizationData/{pageId}', [LinkedInMyPageController::class, 'getDataFromDatabase']);
Route::get('/UploadConnectedUser_PageData', [LinkedInMyPageController::class, 'getUserOrganizations']);
Route::get('/UpdateMyStats_Data', [LinkedInMyPageController::class, 'UpdateMyStatsOrganizations']);
Route::middleware('auth')->get('/api/profile', [LinkedInController::class, 'getProfileData']);
Route::middleware('auth')->get('/my_posts', [LinkedInMyPostsController::class, 'showDetails'])->name('linkedin.mypost');
Route::middleware('auth')->get('/Get_my_PagesPosts', [LinkedInMyPostsController::class, 'getPostsDataFromDatabase'])->name('linkedin.mypost');
Route::middleware('auth')->get('/api/posts', [LinkedInOtherPageController::class, 'getPostsDataFromDatabase']);
Route::get('/organizations/linkedin/{linkedin_id}', [LinkedInOtherPageController::class, 'getOrganizationNameByLinkedinId']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
//Route::middleware('auth')->get('/otherOrganization/{id}', [LinkedInOtherPageController::class, 'getOtherDataFromDatabase']);
// In routes/web.php or routes/api.php
//Route::middleware(['auth'])->get('/otherOrganization/{id}', [LinkedInOtherPageController::class, 'getOtherDataFromDatabase']);
//Route::get('/AutreOO', [LinkedInOtherPageController::class, 'getOtherDataFromDatabase1']);
//Route::middleware('auth')->get('/AutreOO', [LinkedInOtherPageController::class, 'getOtherDataFromDatabase1']);
//Route::get('/otherOrganization/{id}', [LinkedInOtherPageController::class, 'getOtherDataFromDatabase']);
//Route::get('/otherOrganization/{id}', [LinkedInOtherPageController::class, 'getOtherDataFromDatabase']);
Route::middleware('auth')->get('/otherOrganization/{id}', [LinkedInOtherPageController::class, 'getOtherDataFromDatabase']);


Route::get('auth/linkedin', [LinkedInController::class, 'redirectToLinkedIn']);
//Route::get('/api/profile', [LinkedInController::class, 'getProfileData']);

Route::get('callback', [LinkedInController::class, 'handleLinkedInCallback']);
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/organizationC', [LinkedInController::class, 'showPageOrganisations'])->name('linkedin.pageOrg');
Route::get('/AutreorganizationC', [LinkedInController::class, 'showPageAutreOrganisations'])->name('linkedin.pageAutreOrg');


Route::get('/other/{vanityName}/{id}', [LinkedInOtherPageController::class, 'showDetails'])->name('linkedin.other');

Route::get('/my_posts/{id}', [LinkedInMyPostsController::class, 'showDetails'])->name('linkedin.mypost');
//Route::get('/getMyPosts/{id}', [LinkedInMyPostsController::class, 'getPostsDataFromDatabase']);
// Dans routes/web.php ou routes/api.php
Route::get('/getMyPosts/{id}', [LinkedInMyPostsController::class, 'getPostsDataFromDatabase']);

Route::get('/stats/{idOrg}', [StatisticsController::class, 'showDetails'])->name('linkedin.stats');
Route::middleware('auth')->get('/api/Mystats', [StatisticsController::class, 'getMyStatsDataFromDatabase']);
Route::get('/api/MyStats', [StatisticsController::class, 'getMyStatsDataFromDatabase']);
Route::get('/predict-stats/{organizationId}', [PredictionController::class, 'predictStats']);
