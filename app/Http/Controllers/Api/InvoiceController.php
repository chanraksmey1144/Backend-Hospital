<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::with(['patient', 'appointment', 'items', 'payments']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhereHas('patient', fn($q2) => $q2->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $perPage = $request->get('per_page', 15);
        $invoices = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Invoices retrieved.',
            'data'    => $invoices,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $invoice = Invoice::with(['patient', 'appointment', 'items', 'payments'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Invoice retrieved.',
            'data'    => $invoice,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id'     => 'required|string|exists:patients,id',
            'appointment_id' => 'nullable|string|exists:appointments,id',
            'discount'       => 'nullable|numeric|min:0',
            'tax'            => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:20',
            'issue_date'     => 'nullable|date',
            'due_date'       => 'nullable|date',
            'items'          => 'required|array|min:1',
            'items.*.name'        => 'required|string|max:150',
            'items.*.description' => 'nullable|string',
            'items.*.type'        => 'required|in:consultation,medication,laboratory,procedure,other',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        $invoice = DB::transaction(function () use ($validated) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $amount = $item['quantity'] * $item['unit_price'];
                $subtotal += $amount;
                $itemsData[] = array_merge($item, ['amount' => $amount]);
            }

            $discount = $validated['discount'] ?? 0;
            $tax = $validated['tax'] ?? 0;
            $total = $subtotal - $discount + $tax;

            $inv = Invoice::create([
                'patient_id'     => $validated['patient_id'],
                'appointment_id' => $validated['appointment_id'] ?? null,
                'code'           => 'INV-' . strtoupper(uniqid()),
                'discount'       => $discount,
                'tax'            => $tax,
                'total'          => $total,
                'paid_amount'    => 0,
                'status'         => 'pending',
                'payment_method' => $validated['payment_method'] ?? null,
                'issue_date'     => $validated['issue_date'] ?? now()->toDateString(),
                'due_date'       => $validated['due_date'] ?? null,
            ]);

            foreach ($itemsData as $item) {
                $inv->items()->create($item);
            }

            return $inv;
        });

        $invoice->load(['patient', 'items']);

        return response()->json([
            'success' => true,
            'message' => 'Invoice created.',
            'data'    => $invoice,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $invoice = Invoice::findOrFail($id);

        $validated = $request->validate([
            'patient_id'     => 'sometimes|required|string|exists:patients,id',
            'appointment_id' => 'nullable|string|exists:appointments,id',
            'discount'       => 'nullable|numeric|min:0',
            'tax'            => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:20',
            'issue_date'     => 'nullable|date',
            'due_date'       => 'nullable|date',
            'status'         => 'nullable|in:pending,partial,paid,cancelled_inv,refunded',
        ]);

        $invoice->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Invoice updated.',
            'data'    => $invoice,
        ]);
    }

    public function recordPayment(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'amount'    => 'required|numeric|min:0.01',
            'method'    => 'required|in:cash,card,bank_transfer',
            'date'      => 'nullable|date',
            'reference' => 'nullable|string|max:50',
        ]);

        $invoice = Invoice::findOrFail($id);

        $payment = DB::transaction(function () use ($validated, $invoice) {
            $payment = $invoice->payments()->create([
                'amount'    => $validated['amount'],
                'method'    => $validated['method'],
                'date'      => $validated['date'] ?? now()->toDateString(),
                'reference' => $validated['reference'] ?? 'PAY-' . strtoupper(uniqid()),
            ]);

            $newPaid = $invoice->paid_amount + $validated['amount'];

            $status = 'partial';
            if ($newPaid >= $invoice->total) {
                $status = 'paid';
            }

            $invoice->update([
                'paid_amount'    => $newPaid,
                'status'         => $status,
                'payment_method' => $validated['method'],
            ]);

            return $payment;
        });

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded.',
            'data'    => $payment,
        ], 201);
    }

    public function destroy(string $id): JsonResponse
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Invoice deleted.',
        ]);
    }
}
