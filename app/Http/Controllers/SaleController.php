<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerCredit;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf; // If using barryvdh/laravel-dompdf

class SaleController extends Controller
{


    public function all_sales()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $usertype = Auth()->user()->usertype;

            // Retrieve all Sales with their related Purchase data (including invoice_no)
            $Sales = Sale::where('userid', $userId)->where('user_type', $usertype)->get();
            // dd($Sales);
            return view('admin_panel.sale.sales', [
                'Sales' => $Sales,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function add_Sale()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            // dd($userId);
            $Customers = Customer::get();
            $Warehouses = Warehouse::get();
            $Category = Category::get();

            // dd($Customers);
            return view('admin_panel.sale.add_sale', [
                'Customers' => $Customers,
                'Warehouses' => $Warehouses,
                'Category' => $Category,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function getItemsByCategory($categoryId)
    {
        $items = Product::where('category', $categoryId)->get(); // Adjust according to your database structure
        return response()->json($items);
    }


    public function view($id)
    {
        // Fetch the purchase details
        $purchase = Purchase::findOrFail($id);

        // Decode the JSON fields if necessary
        $purchase->item_category = json_decode($purchase->item_category);
        $purchase->item_name = json_decode($purchase->item_name);
        $purchase->quantity = json_decode($purchase->quantity);
        $purchase->price = json_decode($purchase->price);
        $purchase->total = json_decode($purchase->total);

        return view('admin_panel.purchase.view', [
            'purchase' => $purchase,
        ]);
    }

    public function store_Sale(Request $request)
    {
        // Sale data prepare karo
        $usertype = Auth()->user()->usertype;
        $userId = Auth::id();
        $invoiceNo = Sale::generateInvoiceNo();
        // \Log::info('Request Data:', $request->all());

        $customerInfo = explode('|', $request->input('customer_info'));
        // dd($customerInfo);
        if (count($customerInfo) < 2) {
            return redirect()->back()->with('error', 'Invalid customer information format.');
        }


        $customerId = $customerInfo[0];
        $customerName = $customerInfo[1];


        $itemNames = $request->input('item_name', []);
        $itemCategories = $request->input('item_category', []);
        $quantities = $request->input('quantity', []);

        // Step 1: Validate stock for all products
        foreach ($itemNames as $key => $item_name) {
            $item_category = $itemCategories[$key] ?? '';
            $quantity = $quantities[$key] ?? 0;

            $product = Product::where('product_name', $item_name)
                ->where('category', $item_category)
                ->first();

            if (!$product) {
                return redirect()->back()->with('error', "Product $item_name in category $item_category not found.");
            }

            if ($product->stock < $quantity) {
                return redirect()->back()->with('error', "Insufficient stock for product $item_name. Available: {$product->stock}, Required: $quantity.");
            }
        }

        // Get existing customer credit
        $customerCredit = CustomerCredit::where('customerId', $customerId)->first();
        $previousBalance = 0;
        $closingBalance = 0;
        $previous_balance = $request->input('previous_balance');
        $net_total = $request->input('total_amount');
        $closing_balance = $request->input('closing_balance');
        // dd($net_total);

        // Initialize variables to hold balance details
        $previousBalance = 0;
        $closingBalance = 0;

        if ($customerCredit) {
            // Get last closing balance
            $previousBalance = $customerCredit->closing_balance;

            // Calculate new closing balance (same as previous_balance)
            $closingBalance = $previousBalance + $net_total;

            // Update existing customer credit (both values same)
            $customerCredit->update([
                'net_total' => $net_total,
                'previous_balance' => $closingBalance, // Maintain same as closing balance
                'closing_balance' => $closingBalance,  // Maintain same as previous balance
            ]);
        } else {
            // Create new customer credit entry
            CustomerCredit::create([
                'customerId' => $customerId,
                'customer_name' => $customerName,
                'previous_balance' => $net_total, // First entry, so no previous balance
                'net_total' => $net_total,
                'closing_balance' => $net_total,
            ]);

            $previousBalance = 0;
            $closingBalance = $net_total;
        }


        $saleData = [
            'userid' => $userId,
            'user_type' => $usertype,
            'invoice_no' => $invoiceNo,
            'customerId' => $customerId,
            'customer' => $customerName,
            'sale_date' => $request->input('sale_date'),
            'warehouse_id' => $request->input('warehouse_id'),
            'item_category' => json_encode($request->input('item_category', [])),
            'item_name' => json_encode($request->input('item_name', [])),
            'quantity' => json_encode($request->input('quantity', [])),
            'price' => json_encode($request->input('price', [])),
            'gross_amount' => json_encode($request->input('gross_amnt', [])),
            'discount_14' => json_encode($request->input('discount_14', [])),
            'after_14' => json_encode($request->input('after_14', [])),
            'discount_7' => json_encode($request->input('discount_7', [])),
            'after_7' => json_encode($request->input('after_7', [])),
            'final_total' => json_encode($request->input('final_total', [])),
            'net_amount' => $request->input('net_amount'),
            'discount_13' => $request->input('discount_13'),
            'after_discount_13' => $request->input('after_discount_13'),
            'discount_2' => $request->input('discount_2'),
            'after_discount_2' => $request->input('after_discount_2'),
            'scheme_minus' => $request->input('scheme_minus'),
            'total_amount' => $request->input('total_amount'),
            'previous_balance' => $request->input('previous_balance'),
            'closing_balance' => $request->input('closing_balance'),
        ];

        // Sale store karna
        $sale = Sale::create($saleData);

        //  Step 3: Deduct stock after successfully saving the sale
        foreach ($itemNames as $key => $item_name) {
            $item_category = $itemCategories[$key] ?? '';
            $quantity = $quantities[$key] ?? 0;

            $product = Product::where('product_name', $item_name)
                ->where('category', $item_category)
                ->first();

            if ($product) {
                $product->stock -= $quantity;
                $product->save();
            }
        }
        // Success response
        return redirect()->route('sale-receipt', [
            'id' => $sale->id,
            'previous_balance' => $previousBalance, // Ensure this is the correct variable name
            'closing_balance' => $closingBalance, // Ensure this is the correct variable name
            'net_total' => $net_total // Include this if needed
        ])->with('success', 'Sale recorded successfully. Redirecting to receipt...');
    }

    // public function store_Sale(Request $request)
    // {
    //     dd($request);
    //     $invoiceNo = Sale::generateInvoiceNo();
    //     \Log::info('Request Data:', $request->all());

    //     $totalPrice = (float) $request->input('total_price', 0);
    //     $cashReceived = (float) $request->input('cash_received', 0);
    //     $changeToReturn = (float) $request->input('change_to_return', 0);

    //     // New Discounts
    //     $discount1 = (int) $request->input('discount_1', 0); // Percentage
    //     $discount2 = (int) $request->input('discount_2', 0); // Percentage
    //     $discount3 = (int) $request->input('discount_3', 0); // Percentage

    //     // TO Calculation
    //     $toType = $request->input('to_type');  // 'percentage' or 'rupees'
    //     $toValue = (int) $request->input('to_value', 0); // Ensure integer

    //     $totalPrice = 1600; // Assuming input
    //     $cashReceived = 0; // Dummy value
    //     $changeToReturn = 0; // Dummy value

    //     \Log::info('Processed Values:', [
    //         'discount_1' => $discount1,
    //         'discount_2' => $discount2,
    //         'discount_3' => $discount3,
    //         'to_type' => $toType,
    //         'to_value' => $toValue,
    //         'total_price' => $totalPrice,
    //         'cash_received' => $cashReceived,
    //         'change_to_return' => $changeToReturn,
    //     ]);

    //     // Step 1: Apply percentage-based discounts (rounded down)
    //     $discountedTotal = floor($totalPrice - ($totalPrice * ($discount1 / 100))); // First Discount
    //     $discountedTotal = floor($discountedTotal - ($discountedTotal * ($discount2 / 100))); // Second Discount
    //     $discountedTotal = floor($discountedTotal - ($discountedTotal * ($discount3 / 100))); // Third Discount

    //     // Step 2: Apply TO value
    //     if ($toType == 'percentage') {
    //         $finalAmount = floor($discountedTotal - ($discountedTotal * ($toValue / 100)));
    //     } else {
    //         $finalAmount = $discountedTotal - $toValue;
    //     }


    //     \Log::info('Final Amount after Discounts and TO:', ['final_amount' => $finalAmount]);

    //     $usertype = Auth()->user()->usertype;
    //     $userId = Auth::id();

    //     $itemNames = $request->input('item_name', []);
    //     $itemCategories = $request->input('item_category', []);
    //     $quantities = $request->input('quantity', []);

    //     // Step 1: Validate stock for all products
    //     foreach ($itemNames as $key => $item_name) {
    //         $item_category = $itemCategories[$key] ?? '';
    //         $quantity = $quantities[$key] ?? 0;

    //         $product = Product::where('product_name', $item_name)
    //             ->where('category', $item_category)
    //             ->first();

    //         if (!$product) {
    //             return redirect()->back()->with('error', "Product $item_name in category $item_category not found.");
    //         }

    //         if ($product->stock < $quantity) {
    //             return redirect()->back()->with('error', "Insufficient stock for product $item_name. Available: {$product->stock}, Required: $quantity.");
    //         }
    //     }

    //     // Get customer info from the concatenated string
    //     $customerInfo = explode('|', $request->input('customer_info'));
    //     if (count($customerInfo) < 2) {
    //         return redirect()->back()->with('error', 'Invalid customer information format.');
    //     }


    //     $customerId = $customerInfo[0];
    //     $customerName = $customerInfo[1];

    //     // Get total price
    //     $totalPrice = (float) $request->input('total_price', 0);
    //     $discount1 = (float) $request->input('discount_1', 0);
    //     $discount2 = (float) $request->input('discount_2', 0);
    //     $discount3 = (float) $request->input('discount_3', 0);

    //     // TO Calculation
    //     $toType = $request->input('to_type');  // 'percentage' or 'rupees'
    //     $toValue = (float) $request->input('to_value', 0);
    //     // Step 1: Calculate total discount sequentially
    //     $discountedTotal = $totalPrice;
    //     $discountedTotal -= ($discountedTotal * $discount1) / 100;
    //     $discountedTotal -= ($discountedTotal * $discount2) / 100;
    //     $discountedTotal -= ($discountedTotal * $discount3) / 100;

    //     // Step 2: Apply TO value
    //     if ($toType == 'percentage') {
    //         $finalAmount = $discountedTotal - ($discountedTotal * $toValue / 100);
    //     } else {
    //         $finalAmount = $discountedTotal - $toValue;
    //     }

    //     // Round to 2 decimal places
    //     $netTotal = round($finalAmount, 2);

    //     // Get existing customer credit
    //     $customerCredit = CustomerCredit::where('customerId', $customerId)->first();

    //     $previousBalance = 0;
    //     $closingBalance = 0;
    //     $previous_balance = $request->input('previous_balance');
    //     $net_total = $request->input('net_total');
    //     $closing_balance = $request->input('closing_balance');

    //     // Initialize variables to hold balance details
    //     $previousBalance = 0;
    //     $closingBalance = 0;

    //     if ($customerCredit) {
    //         // Get last closing balance
    //         $previousBalance = $customerCredit->closing_balance;

    //         // Calculate new closing balance (same as previous_balance)
    //         $closingBalance = $previousBalance + $netTotal;

    //         // Update existing customer credit (both values same)
    //         $customerCredit->update([
    //             'net_total' => $netTotal,
    //             'previous_balance' => $closingBalance, // Maintain same as closing balance
    //             'closing_balance' => $closingBalance,  // Maintain same as previous balance
    //         ]);
    //     } else {
    //         // Create new customer credit entry
    //         CustomerCredit::create([
    //             'customerId' => $customerId,
    //             'customer_name' => $customerName,
    //             'previous_balance' => $netTotal, // First entry, so no previous balance
    //             'net_total' => $netTotal,
    //             'closing_balance' => $netTotal,
    //         ]);

    //         $previousBalance = 0;
    //         $closingBalance = $netTotal;
    //     }


    //     // Step 2: Proceed to save the sale
    //     $saleData = [
    //         'userid' => $userId,
    //         'user_type' => $usertype,
    //         'invoice_no' => $invoiceNo,
    //         'customerId' => $customerId,
    //         'customer' => $customerName,
    //         'sale_date' => $request->input('sale_date', ''),
    //         'warehouse_id' => $request->input('warehouse_id', ''),
    //         'item_category' => json_encode($request->input('item_category', [])),
    //         'item_name' => json_encode($request->input('item_name', [])),
    //         'quantity' => json_encode($request->input('quantity', [])),
    //         'price' => json_encode($request->input('price', [])),
    //         'total' => json_encode($request->input('total', [])),
    //         'note' => $request->input('note', ''),
    //         'total_price' => $totalPrice,
    //         'discount_1' => $discount1,
    //         'discount_2' => $discount2,
    //         'discount_3' => $discount3,
    //         'to_type' => $toType,
    //         'to_value' => $toValue,
    //         'Payable_amount' => $finalAmount, // Correct payable amount
    //     ];

    //     $sale = Sale::create($saleData);

    //     // Step 3: Deduct stock after successfully saving the sale
    //     foreach ($itemNames as $key => $item_name) {
    //         $item_category = $itemCategories[$key] ?? '';
    //         $quantity = $quantities[$key] ?? 0;

    //         $product = Product::where('product_name', $item_name)
    //             ->where('category', $item_category)
    //             ->first();

    //         if ($product) {
    //             $product->stock -= $quantity;
    //             $product->save();
    //         }
    //     }

    //     return redirect()->route('sale-receipt', [
    //         'id' => $sale->id,
    //         'previous_balance' => $previousBalance, // Ensure this is the correct variable name
    //         'closing_balance' => $closingBalance, // Ensure this is the correct variable name
    //         'net_total' => $netTotal // Include this if needed
    //     ])
    //         ->with('success', 'Sale recorded successfully. Redirecting to receipt...');
    // }


    // public function store_Sale(Request $request)
    // {
    //     // dd($request);
    //     $invoiceNo = Sale::generateInvoiceNo();
    //     \Log::info('Request Data:', $request->all());

    //     $totalPrice = (float) $request->input('total_price', 0);
    //     $cashReceived = (float) $request->input('cash_received', 0);
    //     $changeToReturn = (float) $request->input('change_to_return', 0);

    //     // New Discounts
    //     $discount1 = (float) $request->input('discount_1', 0);
    //     $discount2 = (float) $request->input('discount_2', 0);
    //     $discount3 = (float) $request->input('discount_3', 0);

    //     // TO Calculation
    //     $toType = $request->input('to_type');  // 'percentage' or 'rupees'
    //     $toValue = (float) $request->input('to_value', 0);

    //     \Log::info('Processed Values:', [
    //         'discount_1' => $discount1,
    //         'discount_2' => $discount2,
    //         'discount_3' => $discount3,
    //         'to_type' => $toType,
    //         'to_value' => $toValue,
    //         'total_price' => $totalPrice,
    //         'cash_received' => $cashReceived,
    //         'change_to_return' => $changeToReturn,
    //     ]);

    //     $usertype = Auth()->user()->usertype;
    //     $userId = Auth::id();
    //     // Step 1: Calculate total discount after applying each discount
    //     $discountedTotal = $totalPrice - $discount1;
    //     $discountedTotal = $discountedTotal - ($discountedTotal * ($discount2 / 100));
    //     $discountedTotal = $discountedTotal - ($discountedTotal * ($discount3 / 100));

    //     // Step 2: Apply TO value
    //     if ($toType == 'percentage') {
    //         $finalAmount = $discountedTotal - ($discountedTotal * ($toValue / 100));
    //     } else {
    //         $finalAmount = $discountedTotal - $toValue;
    //     }

    //     \Log::info('Final Amount after Discounts and TO:', ['final_amount' => $finalAmount]);


    //     $itemNames = $request->input('item_name', []);
    //     $itemCategories = $request->input('item_category', []);
    //     $quantities = $request->input('quantity', []);

    //     // Step 1: Validate stock for all products
    //     foreach ($itemNames as $key => $item_name) {
    //         $item_category = $itemCategories[$key] ?? '';
    //         $quantity = $quantities[$key] ?? 0;

    //         $product = Product::where('product_name', $item_name)
    //             ->where('category', $item_category)
    //             ->first();

    //         if (!$product) {
    //             return redirect()->back()->with('error', "Product $item_name in category $item_category not found.");
    //         }

    //         if ($product->stock < $quantity) {
    //             return redirect()->back()->with('error', "Insufficient stock for product $item_name. Available: {$product->stock}, Required: $quantity.");
    //         }
    //     }

    //     // Get customer info from the concatenated string
    //     $customerInfo = explode('|', $request->input('customer_info'));
    //     if (count($customerInfo) < 2) {
    //         return redirect()->back()->with('error', 'Invalid customer information format.');
    //     }

    //     $customerId = $customerInfo[0];
    //     $customerName = $customerInfo[1];

    //     // Calculate Net Total with Discounts
    //     $netTotal = $totalPrice - ($discount1 + $discount2 + $discount3);

    //     // Get the existing customer credit to retrieve previous balance
    //     $customerCredit = CustomerCredit::where('customerId', $customerId)->first();

    //     $previous_balance = $request->input('previous_balance');
    //     $net_total = $request->input('net_total');
    //     $closing_balance = $request->input('closing_balance');

    //     // Initialize variables to hold balance details
    //     $previousBalance = 0;
    //     $closingBalance = 0;

    //     if ($customerCredit) {
    //         // If customer credit exists, get the previous balance
    //         $previousBalance = $customerCredit->previous_balance;

    //         // Update previous balance to include the new sale's payable amount
    //         $closingBalance = $previousBalance + $netTotal;

    //         // Update existing credit
    //         $customerCredit->net_total = $netTotal; // Store the net total from the current sale
    //         $customerCredit->closing_balance = $closingBalance; // Closing balance is now the updated previous balance
    //         $customerCredit->previous_balance = $closingBalance; // Update to the new previous balance
    //         $customerCredit->save();
    //     } else {
    //         // Create new credit entry for the customer
    //         CustomerCredit::create([
    //             'customerId' => $customerId,
    //             'customer_name' => $customerName,
    //             'previous_balance' => $netTotal, // Set previous balance to the net total for the first sale
    //             'net_total' => $netTotal, // This is the first sale's amount
    //             'closing_balance' => $netTotal, // Closing balance for first entry
    //         ]);

    //         // Set the balances for the first entry
    //         $previousBalance = 0; // No previous balance exists for new customers
    //         $closingBalance = $netTotal; // This will be the closing balance
    //     }

    //     // Step 2: Proceed to save the sale
    //     $saleData = [
    //         'userid' => $userId,
    //         'user_type' => $usertype,
    //         'invoice_no' => $invoiceNo,
    //         'customerId' => $customerId,
    //         'customer' => $customerName,
    //         'sale_date' => $request->input('sale_date', ''),
    //         'warehouse_id' => $request->input('warehouse_id', ''),
    //         'item_category' => json_encode($request->input('item_category', [])),
    //         'item_name' => json_encode($request->input('item_name', [])),
    //         'quantity' => json_encode($request->input('quantity', [])),
    //         'price' => json_encode($request->input('price', [])),
    //         'total' => json_encode($request->input('total', [])),
    //         'note' => $request->input('note', ''),
    //         'total_price' => $totalPrice,
    //         'discount_1' => $discount1,
    //         'discount_2' => $discount2,
    //         'discount_3' => $discount3,
    //         'to_type' => $toType,
    //         'to_value' => $toValue,
    //         'Payable_amount' => $finalAmount, // Correct payable amount
    //         'cash_received' => $cashReceived,
    //         'change_return' => $changeToReturn,
    //     ];

    //     $sale = Sale::create($saleData);

    //     // Step 3: Deduct stock after successfully saving the sale
    //     foreach ($itemNames as $key => $item_name) {
    //         $item_category = $itemCategories[$key] ?? '';
    //         $quantity = $quantities[$key] ?? 0;

    //         $product = Product::where('product_name', $item_name)
    //             ->where('category', $item_category)
    //             ->first();

    //         if ($product) {
    //             $product->stock -= $quantity;
    //             $product->save();
    //         }
    //     }

    //     return redirect()->route('sale-receipt', [
    //         'id' => $sale->id,
    //         'previous_balance' => $previousBalance, // Ensure this is the correct variable name
    //         'closing_balance' => $closingBalance, // Ensure this is the correct variable name
    //         'net_total' => $netTotal // Include this if needed
    //     ])
    //         ->with('success', 'Sale recorded successfully. Redirecting to receipt...');
    // }



    // public function store_Sale(Request $request)
    // {   
    //     $invoiceNo = Sale::generateInvoiceNo();
    //     \Log::info('Request Data:', $request->all());

    //     $totalPrice = (float) $request->input('total_price', 0);
    //     $cashReceived = (float) $request->input('cash_received', 0);
    //     $changeToReturn = (float) $request->input('change_to_return', 0);

    //     // New Discounts
    //     $discount1 = (float) $request->input('discount_1', 0);
    //     $discount2 = (float) $request->input('discount_2', 0);
    //     $discount3 = (float) $request->input('discount_3', 0);

    //     // TO Calculation
    //     $toType = $request->input('to_type');  // 'percentage' or 'rupees'
    //     $toValue = (float) $request->input('to_value', 0);

    //     \Log::info('Processed Values:', [
    //         'discount_1' => $discount1,
    //         'discount_2' => $discount2,
    //         'discount_3' => $discount3,
    //         'to_type' => $toType,
    //         'to_value' => $toValue,
    //         'total_price' => $totalPrice,
    //         'cash_received' => $cashReceived,
    //         'change_to_return' => $changeToReturn,
    //     ]);

    //     $usertype = Auth()->user()->usertype;
    //     $userId = Auth::id();

    //     $itemNames = $request->input('item_name', []);
    //     $itemCategories = $request->input('item_category', []);
    //     $quantities = $request->input('quantity', []);

    //     // Step 1: Validate stock for all products
    //     foreach ($itemNames as $key => $item_name) {
    //         $item_category = $itemCategories[$key] ?? '';
    //         $quantity = $quantities[$key] ?? 0;

    //         $product = Product::where('product_name', $item_name)
    //             ->where('category', $item_category)
    //             ->first();

    //         if (!$product) {
    //             return redirect()->back()->with('error', "Product $item_name in category $item_category not found.");
    //         }

    //         if ($product->stock < $quantity) {
    //             return redirect()->back()->with('error', "Insufficient stock for product $item_name. Available: {$product->stock}, Required: $quantity.");
    //         }
    //     }

    //     // Get customer info from the concatenated string
    //     $customerInfo = explode('|', $request->input('customer_info'));
    //     if (count($customerInfo) < 2) {
    //         return redirect()->back()->with('error', 'Invalid customer information format.');
    //     }

    //     $customerId = $customerInfo[0];
    //     $customerName = $customerInfo[1];
    //     // Prepare data for storage
    //     // Calculate Net Total with Discounts
    //     $netTotal = $totalPrice - ($discount1 + $discount2 + $discount3);

    //     // Get the existing customer credit to retrieve previous balance
    //     $customerCredit = CustomerCredit::where('customerId', $customerId)->first();

    //     $previous_balance = $request->input('previous_balance');
    //     $net_total = $request->input('net_total');
    //     $closing_balance = $request->input('closing_balance');
    //     // Initialize variables to hold balance details
    //     $previousBalance = 0;
    //     $closingBalance = 0;

    //     if ($customerCredit) {
    //         // If customer credit exists, get the previous balance
    //         $previousBalance = $customerCredit->previous_balance;

    //         // Update previous balance to include the new sale's payable amount
    //         $closingBalance = $previousBalance + $netTotal;

    //         // Update existing credit
    //         $customerCredit->net_total = $netTotal; // Store the net total from the current sale
    //         $customerCredit->closing_balance = $closingBalance; // Closing balance is now the updated previous balance
    //         $customerCredit->previous_balance = $closingBalance; // Update to the new previous balance
    //         $customerCredit->save();
    //     } else {
    //         // Create new credit entry for the customer
    //         CustomerCredit::create([
    //             'customerId' => $customerId,
    //             'customer_name' => $customerName,
    //             'previous_balance' => $netTotal, // Set previous balance to the net total for the first sale
    //             'net_total' => $netTotal, // This is the first sale's amount
    //             'closing_balance' => $netTotal, // Closing balance for first entry
    //         ]);

    //         // Set the balances for the first entry
    //         $previousBalance = 0; // No previous balance exists for new customers
    //         $closingBalance = $netTotal; // This will be the closing balance
    //     }

    //     // Step 2: Proceed to save the sale
    //     $saleData = [
    //         'userid' => $userId,
    //         'user_type' => $usertype,
    //         'invoice_no' => $invoiceNo,
    //         'customerId' => $customerId,
    //         'customer' => $customerName,
    //         'sale_date' => $request->input('sale_date', ''),
    //         'warehouse_id' => $request->input('warehouse_id', ''),
    //         'item_category' => json_encode($request->input('item_category', [])),
    //         'item_name' => json_encode($request->input('item_name', [])),
    //         'quantity' => json_encode($request->input('quantity', [])),
    //         'price' => json_encode($request->input('price', [])),
    //         'total' => json_encode($request->input('total', [])),
    //         'note' => $request->input('note', ''),
    //         'total_price' => $totalPrice,
    //         'discount_1' => $discount1,
    //         'discount_2' => $discount2,
    //         'discount_3' => $discount3,
    //         'to_type' => $toType,
    //         'to_value' => $toValue,
    //         'Payable_amount' => $netTotal,
    //         'cash_received' => $cashReceived,
    //         'change_return' => $changeToReturn,
    //     ];

    //     $sale = Sale::create($saleData);

    //     // Step 3: Deduct stock after successfully saving the sale
    //     foreach ($itemNames as $key => $item_name) {
    //         $item_category = $itemCategories[$key] ?? '';
    //         $quantity = $quantities[$key] ?? 0;

    //         $product = Product::where('product_name', $item_name)
    //             ->where('category', $item_category)
    //             ->first();

    //         if ($product) {
    //             $product->stock -= $quantity;
    //             $product->save();
    //         }
    //     }

    //     return redirect()->route('sale-receipt', [
    //         'id' => $sale->id,
    //         'previous_balance' => $previousBalance, // Ensure this is the correct variable name
    //         'closing_balance' => $closingBalance, // Ensure this is the correct variable name
    //         'net_total' => $netTotal // Include this if needed
    //     ])
    //         ->with('success', 'Sale recorded successfully. Redirecting to receipt...');
    // }




    // public function store_Sale(Request $request)
    // {
    //     $invoiceNo = Sale::generateInvoiceNo();
    //     \Log::info('Request Data:', $request->all());

    //     $discount = (float)($request->input('discount', 0));
    //     $totalPrice = (float)$request->input('total_price', 0);
    //     $cashReceived = (float)$request->input('cash_received', 0);
    //     $changeToReturn = (float)$request->input('change_to_return', 0);

    //     \Log::info('Processed Values:', [
    //         'discount' => $discount,
    //         'total_price' => $totalPrice,
    //         'cash_received' => $cashReceived,
    //         'change_to_return' => $changeToReturn,
    //     ]);

    //     $usertype = Auth()->user()->usertype;
    //     $userId = Auth::id();

    //     $itemNames = $request->input('item_name', []);
    //     $itemCategories = $request->input('item_category', []);
    //     $quantities = $request->input('quantity', []);

    //     // Step 1: Validate stock for all products
    //     foreach ($itemNames as $key => $item_name) {
    //         $item_category = $itemCategories[$key] ?? '';
    //         $quantity = $quantities[$key] ?? 0;

    //         $product = Product::where('product_name', $item_name)
    //             ->where('category', $item_category)
    //             ->first();

    //         if (!$product) {
    //             return redirect()->back()->with('error', "Product $item_name in category $item_category not found.");
    //         }

    //         if ($product->stock < $quantity) {
    //             return redirect()->back()->with('error', "Insufficient stock for product $item_name. Available: {$product->stock}, Required: $quantity.");
    //         }
    //     }

    //     $customerInfo = explode('|', $request->input('customer_info'));
    //     if (count($customerInfo) < 2) {
    //         return redirect()->back()->with('error', 'Invalid customer information format.');
    //     }

    //     $customerId = $customerInfo[0];
    //     $customerName = $customerInfo[1];

    //     $netTotal = $totalPrice - $discount;

    //     $customerCredit = CustomerCredit::where('customerId', $customerId)->first();

    //     $previousBalance = $customerCredit ? $customerCredit->closing_balance : 0;
    //     $closingBalance = $previousBalance + $netTotal;

    //     if ($cashReceived > 0) {
    //         $closingBalance -= $cashReceived; // Deduct cash received from the closing balance
    //     }

    //     if ($customerCredit) {
    //         $customerCredit->net_total = $netTotal;
    //         $customerCredit->closing_balance = $closingBalance;
    //         $customerCredit->previous_balance = $previousBalance;
    //         $customerCredit->save();
    //     } else {
    //         CustomerCredit::create([
    //             'customerId' => $customerId,
    //             'customer_name' => $customerName,
    //             'previous_balance' => $previousBalance,
    //             'net_total' => $netTotal,
    //             'closing_balance' => $closingBalance,
    //         ]);
    //     }

    //     $saleData = [
    //         'userid' => $userId,
    //         'user_type' => $usertype,
    //         'invoice_no' => $invoiceNo,
    //         'customerId' => $customerId,
    //         'customer' => $customerName,
    //         'sale_date' => $request->input('sale_date', ''),
    //         'warehouse_id' => $request->input('warehouse_id', ''),
    //         'item_category' => json_encode($request->input('item_category', [])),
    //         'item_name' => json_encode($request->input('item_name', [])),
    //         'quantity' => json_encode($request->input('quantity', [])),
    //         'price' => json_encode($request->input('price', [])),
    //         'total' => json_encode($request->input('total', [])),
    //         'note' => $request->input('note', ''),
    //         'total_price' => $totalPrice,
    //         'discount' => $discount,
    //         'Payable_amount' => $netTotal,
    //         'cash_received' => $cashReceived,
    //         'change_return' => $changeToReturn,
    //     ];

    //     $sale = Sale::create($saleData);

    //     foreach ($itemNames as $key => $item_name) {
    //         $item_category = $itemCategories[$key] ?? '';
    //         $quantity = $quantities[$key] ?? 0;

    //         $product = Product::where('product_name', $item_name)
    //             ->where('category', $item_category)
    //             ->first();

    //         if ($product) {
    //             $product->stock -= $quantity;
    //             $product->save();
    //         }
    //     }

    //     return redirect()->route('sale-receipt', [
    //         'id' => $sale->id,
    //         'previous_balance' => $previousBalance,
    //         'closing_balance' => $closingBalance,
    //         'net_total' => $netTotal,
    //     ])->with('success', 'Sale recorded successfully. Redirecting to receipt...');
    // }




    public function get_customer_amount($id)
    {
        // Fetch the customer by customer_id (not id)
        $customer = CustomerCredit::where('customerId', $id)->first();

        // Check if the customer record is found
        if (!$customer) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        // Return the previous amount as JSON
        return response()->json([
            'previous_balance' => $customer->previous_balance // Ensure this field exists in your model
        ]);
    }


    public function downloadInvoice($id)
    {
        // Fetch the sale data
        $sale = Sale::findOrFail($id);

        // Fetch the customer information based on the customer name in the sale
        $customer = Customer::where('customer_name', $sale->customer)->first();

        // If customer is not found, handle the case (optional)
        if (!$customer) {
            abort(404, 'Customer not found for this sale.');
        }

        // Load the view and pass both sale and customer data
        $pdf = Pdf::loadView('admin_panel.invoices.invoice', ['sale' => $sale, 'customer' => $customer]);

        // Download the PDF file
        return $pdf->download('invoice-' . $sale->invoice_no . '.pdf');
    }

    public function showReceipt($id, Request $request)
    {
        // Fetch Sale record
        $sale = Sale::findOrFail($id);

        // Retrieve data passed from the store method
        $previousBalance = $request->input('previous_balance', 0);
        $closingBalance = $request->input('closing_balance', 0);
        $netTotal = $request->input('net_total', 0);

        return view('admin_panel.sale.receipt', [
            'sale' => $sale,
            'previousBalance' => $previousBalance,
            'closingBalance' => $closingBalance,
            'netTotal' => $netTotal,
        ]);
    }
}
