<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Order;
use App\Models\OrderItem;
use DateTime;
use Exception;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getStatistic() {
        try {

            // Total number of sales (orders)
            $salesCount = Order::count();

            // Total number of unique products sold (can use distinct if you prefer)
            $productsCount = OrderItem::sum('quantity');

            // Total revenue from all orders
            $totalRevenue = OrderItem::sum('total_price');

            $data = [
                [
                    'title' => 'Sales',
                    'stats' => number_format($salesCount) . 'k',
                    'icon' => 'tabler-chart-pie-2',
                    'color' => 'primary',
                ],
                [
                    'title' => 'Products',
                    'stats' => number_format($productsCount / 1000, 3) . 'k',
                    'icon' => 'tabler-shopping-cart',
                    'color' => 'error',
                ],
                [
                    'title' => 'Revenue',
                    'stats' => '$' . number_format($totalRevenue, 2),
                    'icon' => 'tabler-currency-dollar',
                    'color' => 'success',
                ],
            ];

            return ResponseHelper::success($data, 'Statistics retrieved successfully');

        }catch (Exception $e){
            return ResponseHelper::error('Failed get statistics', 500, ['exception' => $e->getMessage()]);
        }
    }

    public function getChartData()
    {
        // Earnings and Expenses by month
        $monthly = Order::selectRaw("
        MONTH(created_at) AS month,
        SUM(CASE WHEN total_price >= 0 THEN total_price ELSE 0 END) AS earning,
        SUM(CASE WHEN total_price < 0 THEN total_price ELSE 0 END) AS expense
    ")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Monthly line: last month vs this month daily
        $today = now();
        $startLast = $today->copy()->subMonth()->startOfMonth();
        $startThis = $today->copy()->startOfMonth();

        $last = Order::selectRaw("
        DAY(created_at) AS day,
        SUM(total_price) AS total
    ")
            ->whereBetween('created_at', [$startLast, $startLast->copy()->endOfMonth()])
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->pluck('total', 'day')->toArray();

        $thisM = Order::selectRaw("
        DAY(created_at) AS day,
        SUM(total_price) AS total
    ")
            ->whereBetween('created_at', [$startThis, $today])
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->pluck('total', 'day')->toArray();

        // Fill missing days with zeros
        $daysLast = range(1, $startLast->daysInMonth);
        $daysThis = range(1, $today->day);

        $lineLast = array_map(fn($d) => $last[$d] ?? 0, $daysLast);
        $lineThis = array_map(fn($d) => $thisM[$d] ?? 0, $daysThis);

        // Total revenue
        $totalRevenue = Order::where('total_price', '>=', 0)->sum('total_price');

        // Dynamic budget (last 3 months average earnings)
        $budget = Order::where('total_price', '>=', 0)
            ->whereBetween('created_at', [now()->subMonths(3)->startOfMonth(), now()])
            ->selectRaw('AVG(total_price) as avg_total')
            ->value('avg_total');

        return response()->json([
            'bar' => [
                'earning' => $monthly->pluck('earning')->map(fn($v) => floatval($v)),
                'expense' => $monthly->pluck('expense')->map(fn($v) => floatval($v)),
                'months' => $monthly->pluck('month')->map(fn($m) => DateTime::createFromFormat('!m', $m)->format('M')),
            ],
            'line' => [
                'last' => $lineLast,
                'this' => $lineThis,
                'days' => $daysLast,
            ],
            'summary' => [
                'revenue' => round($totalRevenue, 2),
                'budget' => round($budget, 2),
            ],
        ]);
    }
}
