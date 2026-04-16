<?php

namespace App\Http\Controllers\Affiliates;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MarketingMaterial;

class AffiliateMarketingMaterialController extends Controller
{
    /**
     * Display the materials library for affiliates.
     * Supports filtering by type and category via query params.
     */
    public function index(Request $request)
    {
        $query = MarketingMaterial::where('is_active', true)
            ->orderBy('priority', 'asc')
            ->orderBy('created_at', 'desc');

        // Filter by type
        if ($request->filled('type') && in_array($request->type, ['pdf', 'png', 'link', 'text'])) {
            $query->where('type', $request->type);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $materials = $query->paginate(18)->withQueryString();

        // Distinct active categories for the filter dropdown
        $categories = MarketingMaterial::where('is_active', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('affiliate.materials.index', compact('materials', 'categories'));
    }
}