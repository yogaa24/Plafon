<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:active,inactive',
            'sales_id' => 'nullable|integer|exists:users,id',
            'search' => 'nullable|string|max:255',
            'updated_since' => 'nullable|date',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $customers = Customer::query()
            ->with(['sales:id,name,email'])
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($validated['sales_id'] ?? null, fn ($query, $salesId) => $query->where('sales_id', $salesId))
            ->when($validated['search'] ?? null, fn ($query, $search) => $query->search($search))
            ->when($validated['updated_since'] ?? null, fn ($query, $date) => $query->where('updated_at', '>=', $date))
            ->orderBy('updated_at')
            ->orderBy('id')
            ->paginate($validated['per_page'] ?? 50)
            ->withQueryString();

        return response()->json([
            'data' => $customers->getCollection()->map(fn (Customer $customer) => $this->formatCustomer($customer)),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
                'last_page' => $customers->lastPage(),
            ],
            'links' => [
                'first' => $customers->url(1),
                'last' => $customers->url($customers->lastPage()),
                'prev' => $customers->previousPageUrl(),
                'next' => $customers->nextPageUrl(),
            ],
        ]);
    }

    public function show(string $kodeCustomer)
    {
        $customer = Customer::with(['sales:id,name,email'])
            ->where('kode_customer', $kodeCustomer)
            ->firstOrFail();

        return response()->json([
            'data' => $this->formatCustomer($customer),
        ]);
    }

    private function formatCustomer(Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'sales_id' => $customer->sales_id,
            'nama_sales' => $customer->nama_sales,
            'kode_customer' => $customer->kode_customer,
            'nama' => $customer->nama,
            'nama_kios' => $customer->nama_kios,
            'alamat' => $customer->alamat,
            'plafon_aktif' => $customer->plafon_aktif,
            'piutang' => $customer->piutang,
            'status' => $customer->status,
            'sales' => $customer->sales ? [
                'id' => $customer->sales->id,
                'name' => $customer->sales->name,
                'email' => $customer->sales->email,
            ] : null,
            'created_at' => $customer->created_at?->toISOString(),
            'updated_at' => $customer->updated_at?->toISOString(),
        ];
    }
}
