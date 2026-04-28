<?php
namespace App\Shared\Controllers;
use App\Features\Settings\Requests\StoreAppSettingRequest;
use App\Features\Settings\Requests\UpdateAppSettingRequest;
use App\Shared\Models\AppSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class AppSettingController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', AppSetting::class);
        return response()->json(AppSetting::all());
    }

    public function store(StoreAppSettingRequest $request): JsonResponse
    {
        $this->authorize('create', AppSetting::class);

        $setting = AppSetting::create([
            'id'    => Str::uuid(),
            'key'   => $request->key,
            'value' => $request->value,
            'section' => $request->section,
        ]);

        return response()->json($setting, 201);
    }

    public function show(AppSetting $appSetting): JsonResponse
    {
        $this->authorize('view', $appSetting);
        return response()->json($appSetting);
    }

    public function update(UpdateAppSettingRequest $request, AppSetting $appSetting): JsonResponse
    {
        $this->authorize('update', $appSetting);
        $appSetting->update($request->validated());
        return response()->json($appSetting);
    }

    public function destroy(AppSetting $appSetting): JsonResponse
    {
        $this->authorize('delete', $appSetting);
        $appSetting->delete();
        return response()->json(['message' => 'Setting deleted successfully.']);
    }
}