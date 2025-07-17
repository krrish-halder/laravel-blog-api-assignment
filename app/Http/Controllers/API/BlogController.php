<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    //? List all blogs
    public function index(Request $request)
    {
        $query = Blog::with('user', 'likes')->withCount('likes');

        //* Search by title or content
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%$search%")
                    ->orWhere('content', 'LIKE', "%$search%");
            });
        }

        //* Sort by likes or latest
        $sort = $request->query('sort', 'latest');

        if ($sort === 'most_liked') {
            $query->orderBy('likes_count', 'desc');
        } else { // Default to latest
            $query->latest();
        }

        $blogs = $query->paginate(10);

        return response()->json($blogs);
    }

    // ? Store a Blog
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:10240', // 10MB max
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('blog_images', 'public');
        }

        $blog = Blog::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image_path' => $imagePath,
        ]);

        return response()->json([
            'message' => 'Blog created successfully',
            'blog' => $blog
        ], 201);
    }

    //? Show Blog
    public function show($id)
    {
        $blog = Blog::with('user', 'likes')->withCount('likes')->findOrFail($id);

        return response()->json($blog);
    }

    // ? Update Blog
    public function update(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        if ($blog->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:10240', // 10MB max
        ]);

        if ($request->hasFile('image')) {
            if ($blog->image_path) {
                Storage::disk('public')->delete($blog->image_path);
            }

            $blog->image_path = $request->file('image')->store('blog_images', 'public');
        }

        $blog->update($validated);

        return response()->json([
            'message' => 'Blog updated successfully',
            'blog' => $blog
        ]);
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
