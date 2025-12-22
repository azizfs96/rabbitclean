<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ServiceRepository;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $customers = Customer::all();
        $services = (new ServiceRepository())->getAll();
        $products = (new ProductRepository())->getAll();
        $income = (new OrderRepository())->getByStatus('complete')->sum('amount');

        $revenues = (new OrderRepository())->getRevenueReport();

        $confirmOrder = (new OrderRepository())->getByStatus('pickup')->count();
        $completeOrder = (new OrderRepository())->getByStatus('complete')->count();
        $pendingOrder = (new OrderRepository())->getByStatus('pickup')->count();
        $onPregressOrder = (new OrderRepository())->getByStatus('processing')->count();
        $cancelledOrder = (new OrderRepository())->getByStatus('cancelled')->count();


        return view('dashboard.index', compact(
            'customers', 'services', 'products', 'revenues', 'income', 'confirmOrder', 'completeOrder', 'pendingOrder', 'onPregressOrder', 'cancelledOrder'
        ));
    }
}
