<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function byType(Request $request)
    {
        $type = $request->query('type');
        if (!in_array($type, ['income', 'expense'], true)) {
            return response()->json(['message' => 'Invalid type'], 422);
        }
        $cats = Category::where('type', $type)->orderBy('name')->get(['id','name','type','icon']);
        return response()->json($cats);
    }

    public function quickStore(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'type' => ['required','in:income,expense'],
            'icon' => ['nullable','string','max:80'],
        ]);
        // TODO: attach user_id when auth is added; for now set null or 1 if needed
        // Attach to current user if available; otherwise attach to the first user
        $data['user_id'] = optional($request->user())->id;
        if (!$data['user_id']) {
            $data['user_id'] = \App\Models\User::query()->value('id') ?? 1;
        }
        $category = Category::create($data);
        return response()->json([
            'id' => $category->id,
            'name' => $category->name,
            'type' => $category->type,
            'icon' => $category->icon,
        ], 201);
    }
}
