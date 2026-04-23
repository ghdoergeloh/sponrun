<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Projects/Index', [
            'projects' => Project::orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Projects/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'id' => 'required|integer|unique:projects,id',
            'name' => 'required|string|max:255',
            'scope' => 'required|in:person,project',
        ]);

        Project::create($request->only('id', 'name', 'scope'));

        return redirect()->route('admin.project.index')->with('success', 'Projekt erstellt.');
    }

    public function edit(Project $project): Response
    {
        return Inertia::render('Admin/Projects/Edit', ['project' => $project]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'scope' => 'required|in:person,project',
        ]);

        $project->update($request->only('name', 'scope'));

        return redirect()->route('admin.project.index')->with('success', 'Projekt gespeichert.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        try {
            $project->delete();
        } catch (\Exception) {
            return redirect()->back()->with('error', 'Projekt kann nicht gelöscht werden (noch in Verwendung).');
        }

        return redirect()->route('admin.project.index')->with('success', 'Projekt gelöscht.');
    }
}
