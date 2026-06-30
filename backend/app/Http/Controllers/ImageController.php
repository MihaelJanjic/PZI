<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    /**
     * Display a listing of files (images and videos).
     */
    public function index()
    {
        return Image::latest()->paginate(20);
    }

    /**
     * Store a newly uploaded file (image or video).
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200|mimes:jpeg,jpg,png,gif,mp4,mov,avi,webm', // 50MB
            'imageable_id' => 'required|integer',
            'imageable_type' => 'required|string|in:App\Models\Bug,App\Models\Comment',
        ]);

        $file = $request->file('file');
        $path = $file->store('uploads', 'public');

        // Determine type: image or video
        $type = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';

        $image = Image::create([
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'type' => $type,
            'imageable_id' => $request->imageable_id,
            'imageable_type' => $request->imageable_type,
        ]);

        return response()->json($image, 201);
    }

    /**
     * Display the specified file.
     */
    public function show(Image $image)
    {
        return response()->json($image);
    }

    /**
     * Update the specified file.
     */
    public function update(Request $request, Image $image)
    {
        $request->validate([
            'file' => 'required|file|max:51200|mimes:jpeg,jpg,png,gif,mp4,mov,avi,webm',
        ]);

        // Delete old file
        Storage::disk('public')->delete($image->path);

        $file = $request->file('file');
        $path = $file->store('uploads', 'public');

        $type = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';

        $image->update([
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'type' => $type,
        ]);

        return response()->json($image);
    }

    /**
     * Remove the specified file.
     */
    public function destroy(Image $image)
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();

        return response()->json([
            'message' => 'File deleted successfully'
        ]);
    }

    public function serve(int $id)
    {
        $image = Image::findOrFail($id);

        $path = Storage::disk('public')->path($image->path);

        if (!file_exists($path)) {
            abort(404, 'File not found');
        }

        return response()->file($path);
    }
}