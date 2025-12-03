<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Priest;
use Illuminate\Http\Request;

class PriestController extends Controller
{
    public function search(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if ($q === '' || strlen($q) < 2) return response()->json([]);
        $items = Priest::query()
            ->where('name', 'like', '%'.$q.'%')
            ->orderBy('name')
            ->limit(10)
            ->get(['id','name','phone','email']);
        return response()->json($items);
    }
}
