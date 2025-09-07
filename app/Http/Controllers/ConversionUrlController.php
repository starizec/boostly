<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ConversionUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ConversionUrlController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $conversionUrls = ConversionUrl::latest()->paginate(15);

        return view('conversion-urls.index', compact('conversionUrls'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('conversion-urls.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => 'required|string|max:255|url',
        ]);

        $conversionUrl = ConversionUrl::create($validated);

        return response()->json([
            'message' => 'Conversion URL created successfully.',
            'data' => $conversionUrl,
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(ConversionUrl $conversionUrl): View
    {
        return view('conversion-urls.show', compact('conversionUrl'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ConversionUrl $conversionUrl): View
    {
        return view('conversion-urls.edit', compact('conversionUrl'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ConversionUrl $conversionUrl): JsonResponse
    {
        $validated = $request->validate([
            'url' => 'required|string|max:255|url',
        ]);

        $conversionUrl->update($validated);

        return response()->json([
            'message' => 'Conversion URL updated successfully.',
            'data' => $conversionUrl,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ConversionUrl $conversionUrl): JsonResponse
    {
        $conversionUrl->delete();

        return response()->json([
            'message' => 'Conversion URL deleted successfully.',
        ]);
    }
}
