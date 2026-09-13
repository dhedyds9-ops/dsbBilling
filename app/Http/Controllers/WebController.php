<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebController extends Controller
{
    public function home()
    {
        $baseQuery = \App\Models\ISP\ServiceProfile::where('status', 'active')
            ->where('visibility', 'public')
            ->orderBy('base_price', 'asc');

        $packagesPppoe = (clone $baseQuery)->where('service_type', 'pppoe')->get();
        $packagesHotspot = (clone $baseQuery)->where('service_type', 'hotspot')->get();
        $packagesVoucher = (clone $baseQuery)->where('service_type', 'voucher')->get();
            
        return view('landing', compact('packagesPppoe', 'packagesHotspot', 'packagesVoucher'));
    }

    public function comingSoon()
    {
        return view('coming-soon');
    }

    public function loginAsAdmin()
    {
        if (! app()->isLocal()) abort(404);
        
        $user = \App\Models\User::where('email', 'admin@example.com')->firstOrFail();
        \Illuminate\Support\Facades\Auth::login($user);
        return redirect()->route('dashboard');
    }

    public function gisCustomerMap()
    {
        return new \Illuminate\Http\RedirectResponse(route('gis.map'));
    }
}
