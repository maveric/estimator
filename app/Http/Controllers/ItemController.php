<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(): View
    {
        return view('items.index');
    }

    public function create(): View
    {
        return view('items.form', ['mode' => 'create']);
    }

    public function edit(Item $item): View
    {
        return view('items.form', [
            'mode' => 'edit',
            'item' => $item
        ]);
    }
}
