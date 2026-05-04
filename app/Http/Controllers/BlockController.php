<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Block;

class BlockController extends Controller
{
    public function index($project_id)
    {
        return response()->json(Block::where('project_id', $project_id)->paginate(10));
    }

    public function store(Request $request, $project_id)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $block = Block::create([
            'project_id' => $project_id,
            'name'       => $request->name,
        ]);

        return response()->json($block, 201);
    }

    public function show($project_id, $id)
    {
        $block = Block::where('project_id', $project_id)->find($id);

        if (!$block) {
            return response()->json(['message' => 'Bloque no encontrado.'], 404);
        }

        return response()->json($block);
    }

    public function update(Request $request, $project_id, $id)
    {
        $block = Block::where('project_id', $project_id)->find($id);

        if (!$block) {
            return response()->json(['message' => 'Bloque no encontrado.'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string',
        ]);

        $block->update($request->all());

        return response()->json($block);
    }

    public function destroy($project_id, $id)
    {
        $block = Block::where('project_id', $project_id)->find($id);

        if (!$block) {
            return response()->json(['message' => 'Bloque no encontrado.'], 404);
        }

        $block->delete();

        return response()->json(['message' => 'Bloque eliminado.']);
    }
}