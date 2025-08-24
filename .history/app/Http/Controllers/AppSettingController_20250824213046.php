<?php

namespace App\Http\Controllers;

use App\Models\SoftImage;
use App\Models\SoftNews;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view("appSettings.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        // Validate file
        $request->validate([
            'name' => 'required',
            'image_file' => 'required|mimes:jpg,jpeg,png,gif|max:2048',
        ], [
            'image_file.required' => 'Please upload an image.',
            'image_file.mimes'    => 'Only JPG, JPEG, PNG, and GIF formats are allowed.',
            'image_file.max'      => 'Image size must not exceed 2MB.',
        ]);

        // Store the image in "storage/app/public/images"
        $path = $request->file('image_file')->store('images', 'public');

        SoftImage::create([
            'name' => $request->name,
            'image_path' => $path,
        ]);
        return back()->with('success', 'Image uploaded successfully!')->with('path', $path);
    }

    /**
     * Display the specified resource.
     */

    public function fetchApi()
    {

        return SoftNews::orderByDesc("id")->
    }
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
