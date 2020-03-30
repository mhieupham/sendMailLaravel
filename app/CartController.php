<?php

namespace App\Http\Controllers;

use App\Coupon;
use App\Customer;
use App\Mail\ShoppingMail;
use App\ProductModel;
use Illuminate\Http\Request;
use App\OrderCustomer;
use Illuminate\Support\Facades\Mail;

class CartController extends Controller
{
    //
    public function index(){
        return view('pages.all-cart');
    }
    public function addToCart(Request $request){
        $product_id = $request->input('product_id');
        $count_product = $request->input('count_product');

        $product = ProductModel::findOrFail($product_id);
        if(!$product){
            return redirect()->back();
        }
        $cartData = $request->session()->get('cart-data', []);
            if($cartData[$product_id] ?? false){
                $cartData[$product_id]['count']+=1;
            }else{
                $cartData[$product_id] = [
                    'id'=>$product->product_id,
                    'name'=>$product->product_name,
                    'count'=>$count_product,
                    'price'=>$product->product_price,
                    'image'=>$product->product_image,
                ];
            }
        $request->session()->put('cart-data',$cartData);
            return redirect()->back();
    }
    public function destroyItem(Request $request){
        $product_id = $request->input('product_id');
        $cartData = $request->session()->get('cart-data');
        if($cartData[$product_id] ?? false){
            unset($cartData[$product_id]);
        }
        $request->session()->put('cart-data',$cartData);
        return redirect()->back();
    }
    public function plusItem(Request $request){
        $product_id = $request->input('product_id');
        $cartData = $request->session()->get('cart-data');
        if($cartData[$product_id] ?? false){
            $cartData[$product_id]['count']+=1;
        }
        $request->session()->put('cart-data',$cartData);
        return redirect()->back();
    }
    public function minusItem(Request $request){
        $product_id = $request->input('product_id');
        $cartData = $request->session()->get('cart-data');
        if($cartData[$product_id] ?? false){
            $cartData[$product_id]['count']-=1;
            if($cartData[$product_id]['count'] == 0){
                unset($cartData[$product_id]);
            }
        }
        $request->session()->put('cart-data',$cartData);
        return redirect()->back();
    }
    public function countQuantity(Request $request,$product_id){
        $cartData = $request->session()->get('cart-data');
        $countItem = $request->input('quantity');
        if($cartData[$product_id] ?? false){
            $cartData[$product_id]['count']=$countItem;
        }
        $request->session()->put('cart-data',$cartData);
        return redirect()->back();
    }

    public function store(Request $request){
        if($request->session()->has('customer_email')){
            $user = Customer::where('customer_email','=',$request->session()->get('customer_email'))->get()->first();
            if($request->input('action') == 'Check Out') {
                $this->validate($request,[
                    'product_id'=>'required',
                    'product_count'=>'required',
                    'total_cart'=>'required'
                ]);
                $order = new OrderCustomer([
                    'customer_name' => $user->customer_name,
                    'customer_address' => $user->customer_address,
                    'customer_number' => $user->customer_numberphone,
                    'product_id' => json_encode($request->input('product_id')),
                    'product_count' => json_encode($request->input('product_count')),
                    'order_total' => $request->input('total_cart')
                ]);
                $order->save();

                $total_cart = $request->input('total_cart');
                $order_mail = $request->session()->get('cart-data');
                Mail::to($request->session()->get('customer_email'))->send(new ShoppingMail($order_mail,$total_cart));
                $request->session()->forget('cart-data');
                $request->session()->forget('discount');
                return view('pages.ad_to_cart');
            }elseif ($request->input('action') == 'Get Code'){
                if($request->session()->has('discount')){
                    return redirect()->back()->with('success','Invalid Code');
                }else{
                    $coupon = Coupon::where('coupon_code','=',$request->input('coupon_code'))
                        ->where('publication_status','=',1)
                        ->first();
                    if($coupon){
                        $coupon_arr = [
                            'code'=>$coupon['coupon_code'],
                            'number_percent'=>$coupon['number_percent']/100,
                            'number'=>$coupon['number_percent']
                        ];
                        $request->session()->put('discount',$coupon_arr);
                        return redirect()->back();
                    }else{
                        return redirect()->back()->with('success','Wrong Code');
                    }
                }
            }
        }else{
            if($request->input('action') == 'Check Out'){
                $this->validate($request,[
                    'customer_name'=>'required',
                    'customer_number'=>'required',
                    'customer_address'=>'required',
                    'product_id'=>'required',
                    'product_count'=>'required',
                    'total_cart'=>'required'
                ]);
                $order = new OrderCustomer([
                    'customer_name'=>$request->input('customer_name'),
                    'customer_address'=>$request->input('customer_address'),
                    'customer_number'=>$request->input('customer_number'),
                    'product_id'=>json_encode($request->input('product_id')),
                    'product_count'=>json_encode($request->input('product_count')),
                    'order_total'=>$request->input('total_cart')
                ]);
                $order->save();
                $request->session()->forget('cart-data');
                $request->session()->forget('discount');
                return view('pages.ad_to_cart');
            }elseif ($request->input('action') == 'Get Code'){
                if($request->session()->has('discount')){
                    return redirect()->back()->with('success','Invalid Code');
                }else{
                    $coupon = Coupon::where('coupon_code','=',$request->input('coupon_code'))
                        ->where('publication_status','=',1)
                        ->first();
                    if($coupon){
                        $coupon_arr = [
                            'code'=>$coupon['coupon_code'],
                            'number_percent'=>$coupon['number_percent']/100,
                            'number'=>$coupon['number_percent']
                        ];
                        $request->session()->put('discount',$coupon_arr);
                        return redirect()->back();
                    }else{
                        return redirect()->back()->with('success','Wrong Code');
                    }
                }
            }
        }

    }
    public function unCoupon(Request $request){
        $request->session()->forget('discount');
        return redirect()->back();
    }
}
