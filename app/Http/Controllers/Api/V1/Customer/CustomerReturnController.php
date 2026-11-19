<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnItem;
use App\Models\ReturnRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerReturnController extends Controller
{
    /** Only delivered orders can be returned or exchanged. */
    private const RETURNABLE_STATUSES = ['delivered'];

    public function index(Request $request): JsonResponse
    {
        $returns = ReturnRequest::with(['order:id,order_number', 'items.orderItem:id,product_name,size,color,image'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $returns,
        ]);
    }

    public function store(Request $request, string $orderNumber): JsonResponse
    {
        $order = Order::with('items')
            ->where('user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if (! in_array($order->order_status, self::RETURNABLE_STATUSES, true)) {
            throw ValidationException::withMessages([
                'order' => ['Only delivered orders can be returned or exchanged.'],
            ]);
        }

        $validated = $request->validate([
            'type' => 'required|in:return,exchange',
            'reason' => 'required|string|max:255',
            'condition' => 'nullable|in:unopened,defective,wrong_item,size_issue',
            'customer_notes' => 'nullable|string|max:2000',
            'replacement_product' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Quantities already tied up in returns that are not rejected.
        $alreadyRequested = ReturnItem::query()
            ->whereHas('returnRequest', fn ($q) => $q->where('order_id', $order->id)->where('status', '!=', 'rejected'))
            ->selectRaw('order_item_id, SUM(quantity) as qty')
            ->groupBy('order_item_id')
            ->pluck('qty', 'order_item_id');

        $orderItems = $order->items->keyBy('id');
        $lines = [];

        foreach ($validated['items'] as $i => $line) {
            $orderItem = $orderItems->get($line['order_item_id']);

            if (! $orderItem) {
                throw ValidationException::withMessages([
                    "items.$i.order_item_id" => ['This item does not belong to the order.'],
                ]);
            }

            $available = $orderItem->quantity - (int) ($alreadyRequested[$orderItem->id] ?? 0);

            if ($line['quantity'] > $available) {
                throw ValidationException::withMessages([
                    "items.$i.quantity" => ["Only {$available} unit(s) of {$orderItem->product_name} can still be returned."],
                ]);
            }

            $lines[] = ['item' => $orderItem, 'quantity' => $line['quantity']];
        }

        $returnRequest = DB::transaction(function () use ($request, $order, $validated, $lines) {
            $refund = collect($lines)->sum(fn ($l) => (float) $l['item']->price * $l['quantity']);

            $returnRequest = ReturnRequest::create([
                'return_number' => 'RMA-'.now()->format('ymd').'-'.Str::upper(Str::random(5)),
                'order_id' => $order->id,
                'user_id' => $request->user()->id,
                'type' => $validated['type'],
                'reason' => $validated['reason'],
                'condition' => $validated['condition'] ?? null,
                'status' => 'pending',
                'refund_amount' => $validated['type'] === 'return' ? $refund : 0,
                'replacement_product' => $validated['replacement_product'] ?? null,
                'customer_notes' => $validated['customer_notes'] ?? null,
            ]);

            foreach ($lines as $line) {
                $returnRequest->items()->create([
                    'order_item_id' => $line['item']->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['item']->price,
                ]);
            }

            return $returnRequest;
        });

        return response()->json([
            'success' => true,
            'message' => 'Return request submitted successfully.',
            'data' => $returnRequest->load('items.orderItem'),
        ], 201);
    }
}
