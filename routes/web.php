<?php

use App\Events\chatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FollowController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/admins-only', function() {
  return 'Only admins should be able to see this page.';
})->middleware('can:visitAdminPages');

// User related routes
Route::get('/', [UserController::class, "showCorrectHomepage"])->name('login');
Route::post('/register', [UserController::class, 'register'])->middleware('guest');
Route::post('/login', [UserController::class, 'login'])->middleware('guest');
Route::post('/logout', [UserController::class, 'logout'])->middleware('mustBeLoggedIn');
Route::get('/manage-avatar', [UserController::class, 'showAvatarForm'])->middleware('mustBeLoggedIn');
Route::post('/manage-avatar', [UserController::class, 'storeAvatar'])->middleware('mustBeLoggedIn');

// Follow related routes
Route::post('/create-follow/{user:username}', [FollowController::class, 'createFollow'])->middleware('mustBeLoggedIn');
Route::post('/remove-follow/{user:username}', [FollowController::class, 'removeFollow'])->middleware('mustBeLoggedIn');

// Blog post related routes
Route::get('/create-post', [PostController::class, 'showCreateForm'])->middleware('mustBeLoggedIn');
Route::post('/create-post', [PostController::class, 'storeNewPost'])->middleware('mustBeLoggedIn');
Route::get('/post/{post}', [PostController::class, 'viewSinglePost']);
Route::delete('/post/{post}', [PostController::class, 'delete'])->middleware('can:delete,post');
Route::get('/post/{post}/edit', [PostController::class, 'showEditForm'])->middleware('can:update,post');
Route::put('/post/{post}', [PostController::class, 'actuallyUpdate'])->middleware('can:update,post');
Route::get('/search/{term}',[PostController::class,'search']);
// Profile related routes
Route::get('/profile/{user:username}', [UserController::class, 'profile']);
Route::get('/profile/{user:username}/followers', [UserController::class, 'profileFollowers']);
Route::get('/profile/{user:username}/following', [UserController::class, 'profileFollowing']);
//for chatching profile
Route::middleware('cache.headers:public;max_age=20;etag')->group(function(){

Route::get('/profile/{user:username}/raw', [UserController::class, 'profileRaw']);
Route::get('/profile/{user:username}/followers/raw', [UserController::class, 'profileFollowersRaw']);
Route::get('/profile/{user:username}/following/raw', [UserController::class, 'profileFollowingRaw']);

});


//chat route
Route::post('/send-chat-message',function(Request $request){
 $formFields = $request->validate([
  'textvalue' => 'required'
 ]);
 if(!trim(strip_tags($formFields['textvalue']))){
    return response()->noContent();
 }
 //for something vald to broadcast
 broadcast(new chatMessage(['username' =>auth()->user()->username,'textvalue'=>strip_tags($request->textvalue),'avatar'=> auth()->user()->avatar]))->toOthers();
  return response()->noContent();

})->middleware('mustBeLoggedIn');
