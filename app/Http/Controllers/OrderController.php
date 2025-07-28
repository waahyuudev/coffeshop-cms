<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('itemsPerPage') ?? 10;
            $orders = Order::with('items', 'items.product')->filter($request->all())->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

            return ResponseHelper::success($orders->items(), 'Products retrieved successfully', total: $orders->total());
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to retrieve products', 500, ['exception' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {

            $validated = $request->validate([
                'table_number' => 'required|integer',
                'customer_name' => 'required|string',
                'order_items' => 'required|array|min:1',
                'channel_payment' => 'nullable|string',
                'status' => 'nullable|string',
            ]);

            // Generate order_id
            $orderId = strtoupper(Str::random(4));

            $order = Order::create([
                'order_id' => $orderId,
                'table_number' => $validated['table_number'],
                'customer_name' => $validated['customer_name'],
                'channel_payment' => $validated['channel_payment'] ?? 'cash',
                'status' => $validated['status'] ?? 'request',
                'payment_status' => 'unpaid',
                'total_price' => 0,
            ]);

            $totalPrice = 0;
            foreach ($validated['order_items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $price = $product->price;
                $quantity = $item['quantity'];
                $subtotal = $price * $quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'total_price' => $price,
                    'subtotal_price' => $subtotal,
                    'note' => $item['note'] ?? null,
                ]);

                $totalPrice += $subtotal;
            }

            $order->update(['total_price' => $totalPrice]);

            return ResponseHelper::success($orderId, 'Order created successfully');

        } catch (Exception $e) {
            return ResponseHelper::error('Failed to create order', 500, ['exception' => $e->getMessage()]);
        }
    }

    public function destroy(Request $request)
    {
        try {

            $order = Order::findOrFail($request->query('id'));

            $order->items()->delete();

            $order->delete();

            return ResponseHelper::success($order->id, 'Order deleted successfully');

        } catch (Exception $e) {
            return ResponseHelper::error('Failed to delete order', 500, ['exception' => $e->getMessage()]);
        }
    }

    public function markStatus(Request $request)
    {
        try {

            $order = Order::findOrFail($request->query('id'));

            $order->update(['status' => $request->status, 'payment_status' => 'paid']);

            return ResponseHelper::success($order->id, 'Status updated successfully');

        } catch (Exception $e) {
            return ResponseHelper::error('Failed to update status', 500, ['exception' => $e->getMessage()]);
        }
    }

    public function orderDetail(Request $request)
    {
        try {

            $order = Order::with('items')->findOrFail($request->query('id'));

            if (!$order) {
                return ResponseHelper::error('Order not found', 404);
            }

            return ResponseHelper::success($order, 'Order retrieved successfully');

        } catch (Exception $e) {
            return ResponseHelper::error('Failed to get order details', 500, ['exception' => $e->getMessage()]);
        }
    }
}
