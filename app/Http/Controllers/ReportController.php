<?php

namespace App\Http\Controllers;

use App\Models\Piece;
use App\Models\Project;

class ReportController extends Controller
{
    public function pendientesPorProyecto()
    {
        $projects = Project::with(['blocks.pieces' => function ($query) {
            $query->where('status', 'Pendiente');
        }])->get();

        $resultado = $projects->map(function ($project) {
            $total = $project->blocks->sum(function ($block) {
                return $block->pieces->count();
            });

            return [
                'proyecto' => $project->name,
                'pendientes' => $total,
            ];
        });

        return response()->json($resultado);
    }

    public function totalesPorEstado()
    {
        $pendientes = Piece::where('status', 'Pendiente')->count();
        $fabricadas = Piece::where('status', 'Fabricada')->count();

        return response()->json([
            'Pendiente' => $pendientes,
            'Fabricada' => $fabricadas,
        ]);
    }
}