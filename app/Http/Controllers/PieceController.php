<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Piece; //

class PieceController extends Controller
{
    public function index($block_id)
    {
        return response()->json(Piece::where('block_id', $block_id)->paginate(10));
    }

    public function all(Request $request)
    {
        $query = Piece::with('block.project');

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('project_id') && $request->project_id !== '') {
            $query->whereHas('block', function ($q) use ($request) {
                $q->where('project_id', $request->project_id);
            });
        }

        return response()->json($query->paginate(100));
    }

    public function store(Request $request, $block_id)
    {
        $request->validate([
            'name'               => 'required|string',
            'theoretical_weight' => 'required|numeric',
            'real_weight'        => 'nullable|numeric',
        ]);

        $realWeight        = $request->real_weight;
        $theoreticalWeight = $request->theoretical_weight;
        $difference        = $realWeight ? $realWeight - $theoreticalWeight : null;

        $piece = Piece::create([
            'block_id'           => $block_id,
            'name'               => $request->name,
            'theoretical_weight' => $theoreticalWeight,
            'real_weight'        => $realWeight,
            'weight_difference'  => $difference,
            'status'             => $realWeight ? 'Fabricada' : 'Pendiente',
            'manufactured_at'    => $realWeight ? now() : null,
        ]);

        return response()->json($piece, 201);
    }

    public function show($block_id, $id)
    {
        $piece = Piece::where('block_id', $block_id)->find($id);

        if (!$piece) {
            return response()->json(['message' => 'Pieza no encontrada.'], 404);
        }

        return response()->json($piece);
    }

    public function update(Request $request, $block_id, $id)
    {
        $piece = Piece::where('block_id', $block_id)->find($id);

        if (!$piece) {
            return response()->json(['message' => 'Pieza no encontrada.'], 404);
        }

        $request->validate([
            'name'               => 'sometimes|string',
            'theoretical_weight' => 'sometimes|numeric',
            'real_weight'        => 'nullable|numeric',
        ]);

        $realWeight        = $request->real_weight ?? $piece->real_weight;
        $theoreticalWeight = $request->theoretical_weight ?? $piece->theoretical_weight;
        $difference        = $realWeight ? $realWeight - $theoreticalWeight : null;

        $piece->update([
            'name'               => $request->name ?? $piece->name,
            'theoretical_weight' => $theoreticalWeight,
            'real_weight'        => $realWeight,
            'weight_difference'  => $difference,
            'status'             => $realWeight ? 'Fabricada' : 'Pendiente',
            'manufactured_at'    => $realWeight ? now() : null,
        ]);

        return response()->json($piece);
    }

    public function destroy($block_id, $id)
    {
        $piece = Piece::where('block_id', $block_id)->find($id);

        if (!$piece) {
            return response()->json(['message' => 'Pieza no encontrada.'], 404);
        }

        $piece->delete();

        return response()->json(['message' => 'Pieza eliminada.']);
    }
}