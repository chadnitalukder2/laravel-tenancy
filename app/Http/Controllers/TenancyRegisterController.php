<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTenancyRegisterRequest;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenancyRegisterController extends Controller
{
    public function create()
    {
        return view('tenancy-register');
    }

    public function store(StoreTenancyRegisterRequest $request)
    {
        $tenant = Tenant::create($request->validated());

        $tenant->createDomain(['domain' => $request->domain]);

        return 'done';
    }
}
