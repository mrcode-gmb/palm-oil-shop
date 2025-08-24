<?php

namespace App\Http\Controllers;

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
        ]);

        // Store the image in "storage/app/public/images"
        $path = $request->file('image_file')->store('images', 'public');

        $
        return back()->with('success', 'Image uploaded successfully!')->with('path', $path);
    }

    /**
     * Display the specified resource.
     */
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
