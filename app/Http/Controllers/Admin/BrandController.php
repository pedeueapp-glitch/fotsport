<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class BrandController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Brands/Index', [
            'brands' => Brand::orderBy('order')->orderBy('name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'required|image|max:2048',
            'order' => 'nullable|integer',
            'instagram_url' => 'nullable|url|max:255',
        ]);

        $logoPath = $request->file('logo')->store('brands', 'public');

        Brand::create([
            'name' => $request->name,
            'logo_path' => '/storage/' . $logoPath,
            'instagram_url' => $request->instagram_url,
            'order' => $request->order ?? 0,
        ]);

        return redirect()->back();
    }

    public function toggle(Brand $brand)
    {
        $brand->update(['is_active' => !$brand->is_active]);
        return redirect()->back();
    }

    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'order' => 'nullable|integer',
            'instagram_url' => 'nullable|url|max:255',
        ]);

        $data = [
            'name' => $request->name,
            'order' => $request->order ?? 0,
            'instagram_url' => $request->instagram_url,
        ];

        if ($request->hasFile('logo')) {
            // Remove old file
            $oldPath = str_replace('/storage/', '', $brand->logo_path);
            Storage::disk('public')->delete($oldPath);

            $logoPath = $request->file('logo')->store('brands', 'public');
            $data['logo_path'] = '/storage/' . $logoPath;
        }

        $brand->update($data);

        return redirect()->back();
    }

    public function destroy(Brand $brand)
    {
        // Remove file
        $path = str_replace('/storage/', '', $brand->logo_path);
        Storage::disk('public')->delete($path);
        
        $brand->delete();
        return redirect()->back();
    }
}
