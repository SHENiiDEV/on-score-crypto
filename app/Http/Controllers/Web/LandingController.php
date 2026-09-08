<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Analysis;
use App\Models\Entity;

class LandingController extends Controller
{
    public function index()
    {
        $entitiesCount = Entity::count();
        $analysesCount = Analysis::count();

        return view('landing', [
            'entitiesCount' => max(500, $entitiesCount),
            'analysesCount' => max(1240, $analysesCount),
        ]);
    }
}
