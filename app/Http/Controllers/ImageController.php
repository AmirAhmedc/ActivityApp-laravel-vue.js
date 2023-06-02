<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image as ImageModel;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{

    public function downloadImage()
    {
        // retrieve combined image from database
        $image = ImageModel::latest("filename")->first();
    
        // load image from disk using Storage::disk()
        $imageData = Storage::disk('public')->get($image->filename);
    
        // return image as file download with its original filename
        return response()->download(
            storage_path('app/public/' . $image->filename),
            $image->filename
        );
    }

    public function uploadImages(Request $request)
    {
        // retrieve combined image from request
        $image = $request->file('image');
    
        // validate that the uploaded file is an image
        $validated = $request->validate([
            'image' => 'required|image|max:2048'
        ]);
    
        // generate filename and save image to disk
        $filename = uniqid() . '.jpg';
        Storage::disk('public')->put($filename, file_get_contents($image));
    
        // save image to database
        $imageModel = new ImageModel;
        $imageModel->filename = $filename;
        $imageModel->width = $request->input('width');
        $imageModel->height = $request->input('height');
        $imageModel->save();
    
        return response()->json(['success' => true]);
    }
}