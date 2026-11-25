<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/** GET /api/v1/recommendations — maks. 10 produk (KF-13, KNF-08). */
class RecommendationController extends Controller
{
    public function __invoke(Request $request, RecommendationService $recommendations): AnonymousResourceCollection
    {
        return ProductResource::collection($recommendations->forUser($request->user()));
    }
}
