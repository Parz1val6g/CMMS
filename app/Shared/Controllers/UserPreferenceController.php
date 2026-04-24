<?php
namespace App\Shared\Controllers;
use App\Shared\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
class UserPreferenceController extends Controller
{
    public function index(Request $request)
    {
        // Only return preferences for the logged-in user
        return response()->json($request->user()->preferences);
    }
    public function update(Request $request)
    {
        $data = $request->validate([
            'key' => 'required|string|max:50',
            'value' => 'required|array'
        ]);
        $preference = UserPreference::updateOrCreate(
            ['user_id' => $request->user()->id, 'key' => $data['key']],
            ['value' => $data['value']]
        );
        return response()->json($preference);
    }
}