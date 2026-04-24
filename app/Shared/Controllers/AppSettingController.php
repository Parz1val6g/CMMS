<?php
namespace App\Shared\Controllers;
use App\Shared\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
class AppSettingController extends Controller
{
    public function index()
    {
        return response()->json(AppSetting::all());
    }
    public function update(Request $request, AppSetting $appSetting)
    {
        $data = $request->validate(['value' => 'required|array']); // 'value' is cast to an array in the Model
        $appSetting->update($data);
        return response()->json($appSetting);
    }
}