<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PiutangManagerController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'piutang_manager') {
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

        return view('piutang-manager.index', compact('customers', 'salesList'));
    }

    public function showImportPiutang()
    {
        $user = Auth::user();
        
        if ($user->role !== 'piutang_manager') {
            abort(403, 'Unauthorized access');
        }

        return view('piutang-manager.import-piutang');
    }

    public function processImportPiutang(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'piutang_manager') {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'piutang_data' => 'required|string|min:1'
        ], [
            'piutang_data.required' => 'Data piutang wajib diisi',
            'piutang_data.min' => 'Data piutang tidak boleh kosong'
        ]);

        DB::beginTransaction();
        try {
            $rawData = $request->input('piutang_data');
            $lines = explode("\n", $rawData);
            
            $successCount = 0;
            $errorCount = 0;
            $skippedCount = 0;
            $notFoundCustomers = [];

            foreach ($lines as $lineNumber => $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }

                $parts = preg_split('/[\s\t]+/', $line, 3);
                
                if (count($parts) < 2) {
                    $skippedCount++;
                    continue;
                }

                $kodeCustomer = trim($parts[0]);
                $piutangValue = trim($parts[1]);

                if (empty($kodeCustomer)) {
                    $skippedCount++;
                    continue;
                }

                $cleanPiutang = preg_replace('/[^0-9.]/', '', $piutangValue);
                
                if (empty($cleanPiutang) || !is_numeric($cleanPiutang)) {
                    $skippedCount++;
                    continue;
                }

                $piutangNumeric = floatval($cleanPiutang);

                if ($piutangNumeric < 0) {
                    $skippedCount++;
                    continue;
                }

                $customer = Customer::where('kode_customer', $kodeCustomer)->first();

                if (!$customer) {
                    $errorCount++;
                    if (!in_array($kodeCustomer, $notFoundCustomers)) {
                        $notFoundCustomers[] = $kodeCustomer;
                    }
                    continue;
                }

                $customer->piutang = $piutangNumeric;
                $customer->save();
                
                $successCount++;
            }

            DB::commit();

            $message = "Import selesai: {$successCount} berhasil";
            
            if ($skippedCount > 0) {
                $message .= ", {$skippedCount} baris dilewati (format tidak valid)";
            }
            
            if ($errorCount > 0) {
                $message .= ", {$errorCount} customer tidak ditemukan";
            }

            return redirect()->route('piutang-manager.import-piutang')
                ->with('success', $message)
                ->with('not_found', $notFoundCustomers)
                ->with('success_count', $successCount)
                ->with('error_count', $errorCount)
                ->with('skipped_count', $skippedCount);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('piutang-manager.import-piutang')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(Customer $customer)
    {
        $user = Auth::user();
        
        if ($user->role !== 'piutang_manager') {
            abort(403, 'Unauthorized access');
        }

        $customer->load('sales', 'submissions.approvals');

        return view('piutang-manager.show', compact('customer'));
    }
}