<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Blog;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Blog",
 *     description="API Endpoints of Blog"
 * )
 */

class BlogController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/blogs",
     *     summary="Get all blog posts",
     *     @OA\Response(response=200, description="List of blogs")
     * )
     */
    public function index()
    {
        return response()->json(Blog::with('user')->latest()->get());
    }

    /**
     * @OA\Post(
     *     path="/api/blogs",
     *     summary="Create a new blog post",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","content"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="content", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Blog created")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $blog = Blog::create([
            'title' => $request->title,
            'content' => $request->content,
            'user_id' => Auth::id(),
        ]);

        return response()->json($blog, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/blogs/{id}",
     *     summary="Get a single blog post",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Blog found"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show($id)
    {
        $blog = Blog::find($id);
        if (!$blog) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json($blog);
    }

    /**
     * @OA\Put(
     *     path="/api/blogs/{id}",
     *     summary="Update a blog post",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="content", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Blog updated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function update(Request $request, $id)
    {
        $blog = Blog::find($id);
        if (!$blog || $blog->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $blog->update($request->only('title', 'content'));

        return response()->json($blog);
    }

    /**
     * @OA\Delete(
     *     path="/api/blogs/{id}",
     *     summary="Delete a blog post",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Deleted"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy($id)
    {
        $blog = Blog::find($id);
        if (!$blog || $blog->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $blog->delete();
        return response()->json(null, 204);
    }
}
