<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataLineage;
use Illuminate\Support\Facades\Validator;
use phpseclib3\Crypt\RSA;


class DataLineageController extends Controller
{
    public function index()
    {
        return view('lineage');
    }

    public function lookup(Request $request)
    {
        $v = Validator::make($request->all(), [
            'element' => 'required|string',
            'encrypted' => 'sometimes|boolean',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $element = $request->input('element');

        if ($request->boolean('encrypted')) {
            try {
                $privateKeyPath = storage_path('keys/private.pem');
                if (!file_exists($privateKeyPath)) {
                    return response()->json(['message' => 'Server key not found'], 500);
                }

                $privateKey = RSA::loadPrivateKey(file_get_contents($privateKeyPath));
                $cipher = base64_decode($element);
                $decrypted = $privateKey->decrypt($cipher);

                if ($decrypted === false) {
                    return response()->json(['message' => 'Decryption failed'], 400);
                }

                $element = $decrypted;
            } catch (\Throwable $e) {
                return response()->json(['message' => 'Decryption error: '.$e->getMessage()], 500);
            }
        }

        $rows = DataLineage::where('data_element', $element)
                    ->orderBy('occurred_at', 'asc')
                    ->get();

        if ($rows->isEmpty()) {
            return response()->json(['message' => 'No lineage found for '.$element], 404);
        }

        return response()->json([
            'data_element' => $element,
            'lineage' => $rows,
        ], 200);
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'data_element' => 'required|string',
            'action' => 'nullable|string',
            'source' => 'nullable|string',
            'transformation' => 'nullable|string',
            'destination' => 'nullable|string',
            'metadata' => 'nullable|array',
            'occurred_at' => 'nullable|date',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $entry = DataLineage::create($v->validated());

        return response()->json($entry, 201);
    }
}
