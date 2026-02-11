<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesExecutiveController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'sales_executive') {
            abort(403, 'Unauthorized access');
        }

        // Query semua customer
        $query = Customer::with('sales');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nama_kios', 'like', "%{$search}%")
                  ->orWhere('kode_customer', 'like', "%{$search}%");
            });
        }

        // Filter by sales
        if ($request->filled('sales_id')) {
            $query->where('sales_id', $request->sales_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'nama');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $customers = $query->paginate(20)->appends($request->query());

        // Get all sales for filter
        $salesList = \App\Models\User::where('role', 'sales')->orderBy('name')->get();

        return view('sales-executive.index', compact('customers', 'salesList'));
    }

    public function show(Customer $customer)
    {
        $user = Auth::user();
        
        if ($user->role !== 'sales_executive') {
            abort(403, 'Unauthorized access');
        }

        $customer->load('sales', 'submissions.approvals');

        return view('sales-executive.show', compact('customer'));
    }
}