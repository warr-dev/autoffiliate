<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Drafts/Index', [
            'posts' => Post::where('status', 'draft')->latest()->get(),
        ]);
    }

    public function history(): Response
    {
        return Inertia::render('History/Index', [
            'posts' => Post::whereIn('status', ['published', 'failed', 'approved'])->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_title' => 'required|string|max:255',
            'product_description' => 'nullable|string',
            'product_price' => 'nullable|string',
            'shop_name' => 'nullable|string',
            'affiliate_url' => 'required|url',
            'caption' => 'nullable|string',
            'tags' => 'nullable|string',
        ]);

        $post = Post::create([
            'id' => 'post_' . Str::random(12),
            'product_title' => $validated['product_title'],
            'product_description' => $validated['product_description'] ?? null,
            'product_price' => $validated['product_price'] ?? null,
            'shop_name' => $validated['shop_name'] ?? null,
            'affiliate_url' => $validated['affiliate_url'],
            'caption' => $validated['caption'] ?? '',
            'tags' => $validated['tags'] ?? '',
            'status' => 'draft',
            'media_files' => [],
        ]);

        return redirect()->route('drafts.index')->with('success', 'Post draft created successfully.');
    }

    public function update(Request $request, string $id)
    {
        $post = Post::findOrFail($id);

        $validated = $request->validate([
            'caption' => 'nullable|string',
            'tags' => 'nullable|string',
            'status' => 'nullable|string|in:draft,approved,published,failed',
        ]);

        $post->update($validated);

        return back()->with('success', 'Post updated.');
    }

    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return back()->with('success', 'Post deleted.');
    }
}
