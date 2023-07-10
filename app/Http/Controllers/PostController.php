<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\PostDetailResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use response;


class PostController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->query('limit', 10); // Menentukan jumlah item per halaman, defaultnya 10
        
        try {
            $validator = Validator::make($request->all(), [
                'limit' => 'integer|min:1|max:100' // Validasi input limit
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Request',
                    'errors' => $validator->errors()
                ], 400);
            }
    
            $posts = Post::paginate($perPage);
            $posts->makeHidden(['updated_at', 'deleted_at']);
            return response()->json([
                'success' => true,
                'message' => 'List Semua Posts!',
                // 'current_page' => $posts->currentPage(),
                // 'per_page' => $posts->perPage(),
                // 'total_data' => $posts->total(),
                // 'last_page' => $posts->lastPage(),
                'data' => $posts->items(),
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error',
            ], 500);
        }
    }


    public function show ($id)
    {
        $post = Post::with('writer:id,username')->findOrFail($id);
        $post->makeHidden(['updated_at', 'deleted_at']);
             if ($post) {
            return response()->json([
                'success' => true,
                'message' => 'Detail Post!',
                'data'    => $post
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Post Tidak Ditemukan!',
                'data' => (object)[],
            ], 401);
        }
    }
    

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'news_content' => 'required',
            'file' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Penyesuaian validasi file gambar
        ]);
        
        $imageLink = '';
        if ($request->file) {
            // Simpan file gambar dan dapatkan pathnya
            $imagePath = $request->file('file')->store('public/images');
            
            // Dapatkan URL dari path gambar
            $imageLink = url(Storage::url($imagePath));
        }
    
        $request->merge([
            'image' => $imageLink,
            'author' => Auth::user()->id
        ]);
    
        $post = Post::create($request->all());
        if ($post) {
            return response()->json([
                'success' => true,
                'message' => 'Post Berhasil Disimpan!',
                'data' => $post->loadMissing('writer:id,username'),
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Post Gagal Disimpan!',
            ], 401);
        }
        return new PostDetailResource($post->loadMissing('writer:id,username'));
    }
    
    
    
    

    
   public function update(Request $request, $id)
{
    // Define validation rules
    $validator = Validator::make($request->all(), [
        'title' => 'sometimes|required',
        'news_content' => 'sometimes|required',
        'image' => 'sometimes|image',
    ]);

    // Check if validation fails
    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    // Find post by ID
    $post = Post::find($id);

    // Check if post exists
    if (!$post) {
        return response()->json([
            'success' => false,
            'message' => 'Post Tidak Ditemukan!',
            'data' => (object)[],
        ], 404);
    }

    // Update post
    if ($request->hasFile('image')) {
        // Upload new image
        $image = $request->file('image');
        $imagePath = $image->store('public/posts');

        // Delete old image
        Storage::delete($post->image);

        // Generate full image URL
        $imageUrl = url(Storage::url($imagePath));

        // Update post with image URL
        $post->image = $imageUrl;
    }

    if ($request->filled('title')) {
        $post->title = $request->title;
    }

    if ($request->filled('news_content')) {
        $post->news_content = $request->news_content;
    }

    $post->save();

    return response()->json([
        'success' => true,
        'message' => 'Post Berhasil Diupdate!',
        'data' => $post->loadMissing('writer:id,username'),
    ], 200);
}



    


    
public function destroy($id)
{
    $post = Post::withTrashed()->find($id);
    $deleted = $post->delete();

    if (!$post) {
        return response()->json([
            'message' => 'Post Not Found.',
        ], 404);
    }

    if ($post->trashed()) {
        $post->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Post Berhasil Dihapus!',
            'data' => (object)[],
        ], 200);
    } else {

        if ($deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Post Sudah Terhapus!',
            ], 204);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus Post!',
            ], 400);
        }
    }
}


    
    

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
