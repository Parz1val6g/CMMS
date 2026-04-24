<?php
namespace App\Features\Sectors\Controllers;
use App\Features\Sectors\Models\Sector;
use App\Features\Sectors\Resources\SectorResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
class SectorController extends Controller
{
    /**
     * Get a list of Sectors (used for dropdowns and tables)
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Sector::with(['head']);
        // Simple optimized search for the frontend dropdown
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        // Return paginated results (50 per page for dropdowns is a good balance)
        $sectors = $query->latest()->paginate(50);
        return SectorResource::collection($sectors);
    }
}