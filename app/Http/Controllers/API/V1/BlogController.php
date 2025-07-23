<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlogListRequest;
use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use App\Models\Blog;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use App\Traits\ApiResponser;

class BlogController extends Controller
{
    use ApiResponser;
    //? List all blogs
    public function index(BlogListRequest $request)
    {
        // TODO: Refactor this method to use the ApiResponser trait for consistent API responses

        $query = Blog::with('user', 'likes')->withCount('likes')
            ->withExists([
                'likes as is_liked' => function ($q) {
                    $q->where('user_id', Auth::id());
                }
            ]);


        //* Search
        if (!empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%$search%")
                    ->orWhere('content', 'LIKE', "%$search%");
            });
            $query->orWhereHas('user', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                    ->orWhere('email', 'LIKE', "%$search%");
            });
        }

        //* Sort

        $sort = $request->sort ?? Blog::LATEST;

        if ($sort === Blog::MOST_LIKED) {
            $query->orderBy('likes_count', 'desc');
        } else {
            $query->latest();
        }

        $page = $request->page;
        $perPage = $request->per_page ?? 10;

        if ($page || $request->has('per_page')) {
            $blogs = $query->paginate($perPage);
        } else {
            $blogs = $query->get();
        }
        return $this->successResponse('Blogs Fetched', $blogs);
    }

    // ? Store a Blog
    public function store(StoreBlogRequest $request)
    {

        $user = Auth::user();
        $data = $request->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('blog_images', 'public');
        }

        $blog = Blog::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'content' => $data['content'],
            'image_path' => $imagePath,
        ]);
        $blog->save();

        return $this->successResponse('Blog created successfully', $blog, 201);
    }

    //? Show Blog
    public function show($id)
    {
        $blog = Blog::with('user', 'likes')->withCount('likes')->findOrFail($id);
        $user = Auth::user();
        $blog->is_liked = false;

        $blog->loadExists([
            'likes as is_liked' => function ($q) use ($user) {
                $q->where('user_id', $user ? $user->id : null);
            }
        ]);

        return $this->successResponse('Blog fetched successfully', $blog);
    }

    // ? Update Blog
    public function update(UpdateBlogRequest  $request, $id)
    {
        $data = $request->validated();
        $blog = Blog::findOrFail($id);

        if (!Gate::allows('update-blog', $blog)) {
            return response()->json(['message' => 'Unauthorized, You are not the author of this blog'], 403);
        }

        if ($request->hasFile('image')) {
            if ($blog->image_path) {
                Storage::disk('public')->delete($blog->image_path);
            }

            $data['image_path'] = $request->file('image')->store('blog_images', 'public');
        }


        $blog->update(
            [
                'title' => $request->title,
                'content' => $request->content,
            ]
        );

        return response()->json([
            'message' => __('Blog updated successfully'),
            'blog' => $blog
        ], 200);
    }

    //? Delete Blog
    public function destroy($id)
    {
        $blog = Blog::findOrFail($id);
        if (!Gate::allows('delete-blog', $blog)) {
            return response()->json(['message' => 'Unauthorized, You are not the author of this blog'], 403);
        }

        if ($blog->image_path) {
            Storage::disk('public')->delete($blog->image_path);
        }

        $blog->delete();

        return response()->json(['message' => 'Blog deleted successfully'], 204);
    }
}
