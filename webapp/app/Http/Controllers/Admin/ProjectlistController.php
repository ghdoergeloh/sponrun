<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Projectlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectlistController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Projectlists/Index', [
            'projectlists' => Projectlist::withCount('projects')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Projectlists/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['name' => 'required|string|max:255']);
        Projectlist::create($request->only('name'));

        return redirect()->route('admin.projectlist.index')->with('success', 'Projektliste erstellt.');
    }

    public function edit(Projectlist $projectlist): Response
    {
        $projectlist->load('projects');
        $assignedIds = $projectlist->projects->pluck('id');

        return Inertia::render('Admin/Projectlists/Edit', [
            'projectlist' => $projectlist,
            'assignedProjects' => $projectlist->projects,
            'availableProjects' => Project::whereNotIn('id', $assignedIds)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Projectlist $projectlist): RedirectResponse
    {
        $request->validate(['name' => 'required|string|max:255']);
        $projectlist->update($request->only('name'));

        return redirect()->route('admin.projectlist.edit', $projectlist)->with('success', 'Gespeichert.');
    }

    public function destroy(Projectlist $projectlist): RedirectResponse
    {
        try {
            $projectlist->delete();
        } catch (\Exception) {
            return redirect()->back()->with('error', 'Projektliste kann nicht gelöscht werden.');
        }

        return redirect()->route('admin.projectlist.index')->with('success', 'Projektliste gelöscht.');
    }

    public function addProjects(Request $request, Projectlist $projectlist): RedirectResponse
    {
        $request->validate(['project_ids' => 'required|array', 'project_ids.*' => 'exists:projects,id']);
        $projectlist->projects()->syncWithoutDetaching($request->project_ids);

        return redirect()->route('admin.projectlist.edit', $projectlist)->with('success', 'Projekte hinzugefügt.');
    }

    public function removeProjects(Request $request, Projectlist $projectlist): RedirectResponse
    {
        $request->validate(['project_ids' => 'required|array', 'project_ids.*' => 'exists:projects,id']);
        $projectlist->projects()->detach($request->project_ids);

        return redirect()->route('admin.projectlist.edit', $projectlist)->with('success', 'Projekte entfernt.');
    }
}
