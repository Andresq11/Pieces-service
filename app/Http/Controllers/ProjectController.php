<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class ProjectController extends Controller
{
    public function index()
    {
        return response()->json(Project::paginate(10));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string',
            'description' => 'nullable|string',
        ]);

        $project = Project::create($request->all());

        return response()->json($project, 201);
    }

    public function show($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Proyecto no encontrado.'], 404);
        }

        return response()->json($project);
    }

    public function update(Request $request, $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Proyecto no encontrado.'], 404);
        }

        $request->validate([
            'name'        => 'sometimes|string',
            'description' => 'nullable|string',
        ]);

        $project->update($request->all());

        return response()->json($project);
    }

    public function destroy($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Proyecto no encontrado.'], 404);
        }

        $project->delete();

        return response()->json(['message' => 'Proyecto eliminado.']);
    }
}