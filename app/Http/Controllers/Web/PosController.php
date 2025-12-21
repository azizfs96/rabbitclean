<?php

namespace App\Http\Controllers\Web;

use App\Enum\OrderStatus;
use App\Enum\Roles;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\VariantResource;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Repositories\CustomerRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Repositories\VariantRepository;
use App\Repositories\TransationRepository;
use Illuminate\Http\Request;
use App\Repositories\PaymentGatewayRepository;
use App\Models\PaymentGateway;
use App\Repositories\AddressRepository;

class PosController extends Controller
{
    public function index()
    {
        $repository = new PaymentGatewayRepository();
        $gateways = $repository->query()
            ->where('is_active', 1)
            ->get();

        $data = [
            'paymentGateways' => $gateways,
            'customers' => Customer::all(),
            'services' => Service::all(),
        ];
        return view('pos.index', $data);
    }

    public function sales()
    {
        $orderStatus = OrderStatus::cases();

        $orders = (new OrderRepository())->query()->withoutGlobalScope('pos')->get();

        return view('pos.sales', compact('orders', 'orderStatus'));
    }

    public function store(Request $request)
    {
        $order = (new OrderRepository())->PosStoreByRequest($request);
        $transaction = (new TransationRepository())->storeForOrder($order);

        if($order->payment_type != 'cash'){
            $paymentUrl = route('pos.payment', ['order' => $order->id, 'gateway' => $order->payment_type]);

            return $this->json('Order Successful',[
                'message' => 'Order is added successfully',
                'payment_url' => $paymentUrl,
                'payment_type' => $order->payment_type,
                'orders' =>$order,
            ]);


        }else{
            return $this->json('Order Successful',[
                'message' => 'Order is added successfully',
                'payment_type' => $order->payment_type,

            ]);
        }



    }
    public function payment(){
        // Get active payment gateways
        $paymentGateways = PaymentGateway::where('is_active', true)->get();
        
        $data = [
            'payment_gateways' => $paymentGateways,
        ];
        return view('pos.payment', compact('data'));
    }


    public function storeCustomer(Request $request)
    {
        $request['is_active'] = 1;

        $user = (new UserRepository())->registerUser($request);

        $user->assignRole(Roles::CUSTOMER->value);

        (new CustomerRepository())->storeByUser($user);

        return $this->json(__('Created Successfully'), [
            'user' => (object)[
                'id' => $user->customer->id,
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->mobile,
            ],
        ], 200);
    }

    public function storeAddress(AddressRequest $request)
    {

        $address = (new AddressRepository())->storeByPos($request);

        return $this->json(__('Created Successfully'), [
            'address' => (object)[
                'id' => $address->id,
                'address_name' => $address->address_name,
                'road_no' => $address->road_no,
            ],
        ], 200);
    }

    public function fetchVariants()
    {

        $variants = (new VariantRepository())->query()->orderBy('position', 'asc')->get();

        return $this->json('variant list', [
            'variants' => VariantResource::collection($variants)
        ]);
    }

    public function fetchProducts(Request $request)
    {

        $products = (new ProductRepository())->getByRequest($request);

        return $this->json('product list', [
            'products' => ProductResource::collection($products)
        ]);
    }
}
