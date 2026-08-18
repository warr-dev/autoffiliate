<?php

namespace App\Http\Controllers;

use App\Models\WorkflowRule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class WorkflowController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Automated/Index', [
            'workflows' => WorkflowRule::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'frequency' => 'required|string',
            'target_page' => 'required|string',
            'times' => 'nullable|array',
            'days' => 'nullable|array',
            'general_context' => 'nullable|string',
        ]);

        WorkflowRule::create([
            'id' => 'wf_' . Str::random(10),
            'name' => $validated['name'],
            'category' => $validated['category'],
            'frequency' => $validated['frequency'],
            'target_page' => $validated['target_page'],
            'times' => $validated['times'] ?? [],
            'days' => $validated['days'] ?? [],
            'general_context' => $validated['general_context'] ?? '',
            'status' => 'active',
        ]);

        return back()->with('success', 'Workflow rule created.');
    }

    public function destroy(string $id)
    {
        $rule = WorkflowRule::findOrFail($id);
        $rule->delete();

        return back()->with('success', 'Workflow rule deleted.');
    }
}
