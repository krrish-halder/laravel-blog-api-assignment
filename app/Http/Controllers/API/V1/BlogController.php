<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlogRequest;
use App\Models\Blog;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    //? List all blogs
    public function index(Request $request)
    {

        // !! Used 'withExists' to check if the blog is liked by the authenticated user
        $query = Blog::with('user', 'likes')->withCount('likes')
            ->withExists([
                'likes as is_liked' => function ($q) {
                    $q->where('user_id', Auth::id());
                }
            ]);

        //* Search by title or content
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%$search%")
                    ->orWhere('content', 'LIKE', "%$search%");
            });
            $query->orWhereHas('user', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                    ->orWhere('email', 'LIKE', "%$search%");
            });
        }

        //* Sort by likes or latest
        $sort = $request->query('sort', Blog::SORT_LATEST);

        if ($sort === Blog::SORT_MOST_LIKED) {
            $query->orderBy('likes_count', 'desc');
        } else { // Default to latest
            $query->latest();
        }

        $blogs = $query->paginate(10);

        // !! This is an alternative option which I implemented

        // $user = Auth::user();
        // $likedBlogIds = [];

        // if ($user) {
        //     $blogIds = $blogs->pluck('id');

        //     $likedBlogIds = Like::where('user_id', $user->id)
        //         ->where('likeable_type', Blog::class)
        //         ->whereIn('likeable_id', $blogIds)
        //         ->pluck('likeable_id')
        //         ->toArray();
        // }
        //
        // $blogs->getCollection()->transform(function ($blog) use ($likedBlogIds) {
        //     $blog->is_liked = in_array($blog->id, $likedBlogIds);
        //     return $blog;
        // });

        return response()->json($blogs);
    }

    // ? Store a Blog
    public function store(StoreBlogRequest $request)
    {
        $data = $request->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('blog_images', 'public');
        }

        $blog = Blog::create([
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'content' => $data['content'],
            'image_path' => $imagePath,
        ]);

        return response()->json([
            'message' => __('messages.blog_created'),
            'blog' => $blog
        ], 201);
    }

    //? Show Blog
    public function show($id)
    {
        $blog = Blog::with('user', 'likes')->withCount('likes')->findOrFail($id);
        $user = Auth::user();
        $blog->is_liked = false;
        if ($user) {
            $blog->is_liked = Like::where('user_id', $user->id)
                ->where('likeable_type', Blog::class)
                ->where('likeable_id', $blog->id)
                ->exists();
        }

        return response()->json($blog);
    }

    // ? Update Blog
    public function update(UpdateBlogRequest  $request, $id)
    {
        $data = $request->validated();
        $blog = Blog::findOrFail($id);

        if ($blog->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }


        if ($request->hasFile('image')) {
            if ($blog->image_path) {
                Storage::disk('public')->delete($blog->image_path);
            }

            $data['image_path'] = $request->file('image')->store('blog_images', 'public');
        }


        $blog->update($data);

        return response()->json([
            'message' => __('Blog updated successfully'),
            'blog' => $blog
        ], 200);
    }

    //? Delete Blog
    public function destroy($id)
    {
        $blog = Blog::findOrFail($id);

        if ($blog->user_id !== request()->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($blog->image_path) {
            Storage::disk('public')->delete($blog->image_path);
        }

        $blog->delete();

        return response()->json(['message' => 'Blog deleted successfully'], 204);
    }
}
