<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use TCPDF;

class ReportController extends Controller
{
    /**
     * Show reports dashboard
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Sales Report
     */
    public function salesReport(Request $request)
    {

        if ($request->filled('start_date')) {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        }
        if ($request->filled('end_date')) {
            $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        }
        $userId = $request->input('user_id');
        


        return $request;
        $query = Sale::with(['product', 'user'])
            ->whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate);

        return $query;
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $sales = $query->orderBy('sale_date', 'desc')->get();
        
        // Calculate totals
        $totalSales = $sales->sum('total_amount');
        $totalProfit = $sales->sum('profit');
        $totalQuantity = $sales->sum('quantity');

        // Group by product
        $productSales = $sales->groupBy('product_id')->map(function ($group) {
            return [
                'product' => $group->first()->product,
                'quantity' => $group->sum('quantity'),
                'total_amount' => $group->sum('total_amount'),
                'profit' => $group->sum('profit'),
            ];
        });

        // Group by salesperson
        $salespersonSales = $sales->groupBy('user_id')->map(function ($group) {
            return [
                'user' => $group->first()->user,
                'quantity' => $group->sum('quantity'),
                'total_amount' => $group->sum('total_amount'),
                'profit' => $group->sum('profit'),
                'sales_count' => $group->count(),
            ];
        });

        $salespeople = User::where('role', 'salesperson')->get();

        return view('reports.sales', compact(
            'sales', 'startDate', 'endDate', 'totalSales', 'totalProfit', 
            'totalQuantity', 'productSales', 'salespersonSales', 'salespeople'
        ));
    }

    /**
     * Profit Report
     */
    public function profitReport(Request $request)
    {
        $period = $request->input('period', 'monthly');
        $year = $request->input('year', Carbon::now()->year);

        $profits = collect();
        
        if ($period === 'monthly') {
            for ($month = 1; $month <= 12; $month++) {
                $startDate = Carbon::create($year, $month, 1)->startOfMonth();
                $endDate = Carbon::create($year, $month, 1)->endOfMonth();

                $monthlyProfit = Sale::whereDate('sale_date', '>=', $startDate)
                    ->whereDate('sale_date', '<=', $endDate)
                    ->sum('profit');

                $monthlySales = Sale::whereDate('sale_date', '>=', $startDate)
                    ->whereDate('sale_date', '<=', $endDate)
                    ->sum('total_amount');

                $profits->push([
                    'period' => $startDate->format('F Y'),
                    'sales' => $monthlySales,
                    'profit' => $monthlyProfit,
                    'margin' => $monthlySales > 0 ? ($monthlyProfit / $monthlySales) * 100 : 0,
                ]);
            }
        } else {
            // Daily for current month
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();

            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                $dailyProfit = Sale::whereDate('sale_date', $date)
                    ->sum('profit');

                $dailySales = Sale::whereDate('sale_date', $date)
                    ->sum('total_amount');

                $profits->push([
                    'period' => $date->format('M d, Y'),
                    'sales' => $dailySales,
                    'profit' => $dailyProfit,
                    'margin' => $dailySales > 0 ? ($dailyProfit / $dailySales) * 100 : 0,
                ]);
            }
        }

        return view('reports.profit', compact('profits', 'period', 'year'));
    }

    /**
     * Inventory Report
     */
    public function inventoryReport()
    {
        $products = Purchase::with(['product', 'sales'])->get();

        $inventoryData = $products->map(function ($product) {
            $totalPurchased = $product->sum('purchase_price');
            $totalSold = $product->sales->sum('quantity');
            $totalPurchaseCost = $product->sum('purchase_price');
            $averageCostPrice = $product->getAverageCostPrice();

            return [
                'product' => $product,
                'current_stock' => $product->quantity,
                'total_purchased' => $totalPurchased,
                'total_sold' => $totalSold,
                'stock_value' => $product->quantity * $averageCostPrice,
                'average_cost_price' => $averageCostPrice,
                'selling_price' => $product->selling_price,
                'potential_profit' => $product->current_stock * ($product->selling_price - $averageCostPrice),
            ];
        });

        $totalStockValue = $inventoryData->sum('stock_value');
        $totalPotentialProfit = $inventoryData->sum('potential_profit');

        return view('reports.inventory', compact('inventoryData', 'totalStockValue', 'totalPotentialProfit'));
    }

    /**
     * Export Sales Report to PDF
     */
    public function exportSalesPDF(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $sales = Sale::with(['purchase.product', 'user'])
            ->whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->orderBy('sale_date', 'desc')
            ->get();

        $totalSales = $sales->sum('total_amount');
        $totalProfit = $sales->sum('profit');

        // Create PDF
        $pdf = new TCPDF();
        $pdf->SetCreator('Palm Oil Shop');
        $pdf->SetAuthor('Palm Oil Shop');
        $pdf->SetTitle('Sales Report');
        $pdf->SetSubject('Sales Report');

        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Palm Oil Shop - Sales Report', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, "Period: {$startDate} to {$endDate}", 0, 1, 'C');
        $pdf->Ln(5);

        // Summary
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Summary', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 8, "Total Sales: ₦" . number_format($totalSales, 2), 0, 1, 'L');
        $pdf->Cell(0, 8, "Total Profit: ₦" . number_format($totalProfit, 2), 0, 1, 'L');
        $pdf->Cell(0, 8, "Number of Transactions: " . $sales->count(), 0, 1, 'L');
        $pdf->Ln(10);

        // Table header
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(20, 8, 'Date', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Product', 1, 0, 'C');
        $pdf->Cell(20, 8, 'Quantity', 1, 0, 'C');
        $pdf->Cell(25, 8, 'Unit Price', 1, 0, 'C');
        $pdf->Cell(25, 8, 'Total', 1, 0, 'C');
        $pdf->Cell(25, 8, 'Profit', 1, 0, 'C');
        $pdf->Cell(35, 8, 'Salesperson', 1, 1, 'C');

        // Table data
        $pdf->SetFont('helvetica', '', 8);
        foreach ($sales as $sale) {
            $pdf->Cell(20, 8, $sale->sale_date->format('M d'), 1, 0, 'C');
            $pdf->Cell(40, 8, substr($sale->purchase->product->name, 0, 25), 1, 0, 'L');
            $pdf->Cell(20, 8, number_format($sale->quantity, 1), 1, 0, 'C');
            $pdf->Cell(25, 8, '₦' . number_format($sale->selling_price_per_unit), 1, 0, 'R');
            $pdf->Cell(25, 8, '₦' . number_format($sale->total_amount), 1, 0, 'R');
            $pdf->Cell(25, 8, '₦' . number_format($sale->net_profit_per_unit), 1, 0, 'R');
            $pdf->Cell(35, 8, substr($sale->user->name, 0, 20), 1, 1, 'L');
        }

        $filename = "sales_report_{$startDate}_to_{$endDate}.pdf";
        $pdf->Output($filename, 'D');
    }
}
