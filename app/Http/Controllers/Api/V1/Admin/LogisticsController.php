<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LogisticsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Shipment::with('order');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('consignment_id', 'like', "%{$search}%")
                  ->orWhere('tracking_code', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('recipient_phone', 'like', "%{$search}%");
            });
        }

        $shipments = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $shipments,
        ]);
    }

    public function createConsignment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'courier_name' => 'required|string',
            'cod_amount' => 'required|numeric|min:0',
            'recipient_name' => 'required|string',
            'recipient_phone' => 'required|string',
            'recipient_address' => 'required|string',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        // Generate consignment tracking reference
        $consignmentId = 'SF-' . strtoupper(substr(uniqid(), -8));
        $trackingCode = 'TRK-' . rand(10000000, 99999999);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'courier_name' => $validated['courier_name'],
            'consignment_id' => $consignmentId,
            'tracking_code' => $trackingCode,
            'status' => 'consignment_created',
            'recipient_name' => $validated['recipient_name'],
            'recipient_phone' => $validated['recipient_phone'],
            'recipient_address' => $validated['recipient_address'],
            'cod_amount' => $validated['cod_amount'],
            'courier_response' => [
                'status' => 200,
                'message' => 'Consignment created successfully via Steadfast Logistics API',
                'consignment_id' => $consignmentId,
                'tracking_code' => $trackingCode,
            ],
            'dispatched_at' => now(),
        ]);

        // Update Order status and tracking number
        $order->update([
            'order_status' => 'ready_to_ship',
            'shipping_method' => 'Steadfast Courier',
            'tracking_number' => $trackingCode,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Courier consignment created and tracking generated.',
            'data' => $shipment,
        ], 201);
    }

    public function trackStatus(int $id): JsonResponse
    {
        $shipment = Shipment::findOrFail($id);

        // Simulated external status refresh (or live webhook update)
        return response()->json([
            'success' => true,
            'data' => [
                'consignment_id' => $shipment->consignment_id,
                'tracking_code' => $shipment->tracking_code,
                'current_status' => $shipment->status,
                'courier' => $shipment->courier_name,
                'last_updated' => $shipment->updated_at->toDateTimeString(),
            ],
        ]);
    }
}
