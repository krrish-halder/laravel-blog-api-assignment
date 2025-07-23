<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    //? Like or unlike a blog
    public function toggle(Blog $blog, Request $request)
    {
        $user = $request->user();

        $existingLike = $blog->likes()->where('user_id', $user->id)->first();

        if ($existingLike) {
            // If already liked, unlike it
            $existingLike->delete();

            return response()->json([
                'status' => true,
                'message' => 'Blog unliked successfully.'
            ]);
        }

        // If not liked yet, like it
        $blog->likes()->create([
            'user_id' => $user->id
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Blog liked successfully.'
        ]);
    }
}
