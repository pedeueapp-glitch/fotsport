<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class HeroController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Hero/Index', [
            'items' => HeroItem::orderBy('order')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120', // 5MB
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
        ]);

        $path = $request->file('image')->store('hero', 'public');

        HeroItem::create([
            'image_path' => '/storage/' . $path,
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'order' => HeroItem::count()
        ]);

        return redirect()->back();
    }

    public function update(Request $request, HeroItem $heroItem)
    {
        $request->validate([
            'image' => 'nullable|image|max:5120',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        $data = $request->only(['title', 'subtitle', 'is_active']);

        if ($request->hasFile('image')) {
            $oldPath = str_replace('/storage/', '', $heroItem->image_path);
            Storage::disk('public')->delete($oldPath);

            $path = $request->file('image')->store('hero', 'public');
            $data['image_path'] = '/storage/' . $path;
        }

        $heroItem->update($data);

        return redirect()->back();
    }

    public function destroy(HeroItem $heroItem)
    {
        $oldPath = str_replace('/storage/', '', $heroItem->image_path);
        Storage::disk('public')->delete($oldPath);
        
        $heroItem->delete();
        return redirect()->back();
    }

    public function reorder(Request $request)
    {
        foreach ($request->items as $index => $id) {
            HeroItem::where('id', $id)->update(['order' => $index]);
        }
        return redirect()->back();
    }
}
