<?php

use App\Http\Controllers\ReviewController;
use App\Models\Review;
use App\Services\ReviewAnalyzer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

//  AUTH PUBLIC
Route::post('/register', function (Request $request) {
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|string|min:6',
    ]);

    $user = User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
    ]);

    return response()->json([
        'user' => $user,
        'token' => $user->createToken('api-token')->plainTextToken
    ], 201);
});

Route::post('/login', function (Request $request) {
    $data = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $data['email'])->first();

    if (!$user || !Hash::check($data['password'], $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'user' => $user,
        'token' => $token
    ]);
});

//  TOUTES ROUTES PROTÉGÉES (1 SEUL GROUPE)
Route::middleware('auth:sanctum')->group(function () {
    // PROFIL
    Route::get('/user', function (Request $request) {
    $user = $request->user();
    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role,           
        'phone' => $user->phone,
        'job_title' => $user->job_title,
        'department' => $user->department,
        'bio' => $user->bio,
        'created_at' => $user->created_at
    ]);


    });

    Route::patch('/user', function (Request $request) {
        $user = $request->user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'job_title' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000'
        ]);
        $user->update($validated);
        return response()->json($user, 200);
    });

    //  MOT DE PASSE  DANS LE GROUPE auth:sanctum
    Route::patch('/user/password', function (Request $request) {
        $user = $request->user();
        
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);
        
        $user->update(['password' => Hash::make($data['password'])]);
        
        return response()->json(['message' => 'Mot de passe mis à jour']);
    });

    // REVIEWS
    Route::post('/reviews', function(Request $request, ReviewAnalyzer $analyzer) {
        $content = $request->validate(['content' => 'required|string|max:1000']);
        $analysis = $analyzer->analyze($content['content']);
        $review = Review::create(array_merge($analysis, [
            'user_id' => $request->user()->id,
            'content' => $content['content']
        ]));
        return response()->json($review->load('user'), 201);
    });

    Route::get('/reviews', function() {
        return response()->json(Review::with('user')->orderBy('id', 'desc')->get());
    });

    // DASHBOARD
    Route::get('/dashboard/stats', function() {
        $reviews = Review::with('user')->get();
        return response()->json([
            'total_reviews' => $reviews->count(),
            'avg_score' => $reviews->avg('score'),
            'positive_percent' => $reviews->where('sentiment', 'positive')->count() / max(1, $reviews->count()) * 100,
            'negative_percent' => $reviews->where('sentiment', 'negative')->count() / max(1, $reviews->count()) * 100
        ]);
    });
});


//  SUPPRESSION ADMIN SEULE
Route::middleware('auth:sanctum')->group(function () {
    // ... tes routes existantes
    
    //  AJOUTE ÇA - DELETE ADMIN ONLY
    Route::delete('/reviews/{id}', function (Request $request, $id) {
        $user = $request->user();
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Admin requis'], 403);
        }
        
        $review = Review::findOrFail($id);
        $review->delete();
        
        return response()->json(['message' => 'Avis supprimé'], 200);
    });
});

Route::post('/analyze', function (Request $request, ReviewAnalyzer $analyzer) {
    $data = $request->validate(['text' => 'required|string']);
    return response()->json($analyzer->analyze($data['text']));
});

Route::middleware('auth:sanctum')->group(function () {
    // ... tes autres routes
    Route::get('/reviews/visual-report', [ReviewController::class, 'generateVisualReport']);
});