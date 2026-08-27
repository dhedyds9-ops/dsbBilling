<?php

namespace App\Http\Controllers\Api\ISP;

use App\Http\Controllers\Controller;
use App\Models\ISP\Odp;
use App\Services\ISP\OdpOccupancyService;
use App\Services\ISP\FiberLinkStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OdpApiController extends Controller
{
    public function __construct(
        protected OdpOccupancyService $occupancy,
        protected FiberLinkStatusService $linkStatus,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int)$request->input('per_page', 50);
        $q = Odp::with(['olt:id,name', 'odc:id,name']);
        if ($request->filled('olt_id')) {
            $q->where('olt_id', (int)$request->olt_id);
        }
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        if ($request->filled('occupancy_min')) {
            $minOcc = (int)$request->occupancy_min;
            $q->whereRaw('COALESCE(used_port_count,0) * 100.0 / NULLIF(COALESCE(port_count,16),0) >= ?', [$minOcc]);
        }
        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $q->where(fn ($qq) => $qq->where('name', 'like', $s)->orWhere('code', 'like', $s)->orWhere('address', 'like', $s));
        }
        return response()->json($q->latest('id')->paginate($perPage));
    }

    public function show(int $id): JsonResponse
    {
        $odp = Odp::with(['olt', 'odc', 'splitters', 'onus.olt:id,name', 'onus.vendor:id,name', 'parent:id,name'])->findOrFail($id);
        return response()->json(['data' => $odp]);
    }

    public function geojson(Request $request): JsonResponse
    {
        $filters = $request->only(['occupancy_min', 'status']);
        return response()->json(
            $this->occupancy->listForMap(
                (int)$request->input('radius_km', 0),
                $request->filled('lat') ? (float)$request->lat : null,
                $request->filled('lng') ? (float)$request->lng : null,
                $filters
            )
        );
    }

    public function recalculate(int $id): JsonResponse
    {
        $odp = Odp::findOrFail($id);
        return response()->json(['success' => true, 'data' => $this->occupancy->recalculateOne($odp)]);
    }

    public function recalculateAll(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->occupancy->recalculateAll()]);
    }

    public function geocode(int $id): JsonResponse
    {
        $odp = Odp::findOrFail($id);
        $coord = $this->occupancy->geocode($odp);
        return response()->json([
            'success' => $coord !== null,
            'data' => $coord ? ['lat' => $coord->latitude, 'lng' => $coord->longitude] : null,
        ]);
    }

    public function problematic(Request $request): JsonResponse
    {
        $limit = (int)($request->input('limit', 50));
        return response()->json([
            'success' => true,
            'data' => $this->linkStatus->getProblematicOdps($limit),
        ]);
    }
}
