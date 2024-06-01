<?php

namespace App\Http\Controllers;

use App\Account;
use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\CustomerGroup;
use App\InvoiceScheme;
use App\Media;
use App\Product;
use App\SellingPriceGroup;
use App\TaxRate;
use App\Transaction;
use App\TransactionSellLine;
use App\TypesOfService;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Warranty;
use Barryvdh\DomPDF\Facade\Pdf;
use Bl\FatooraZatca\Classes\InvoiceReportType;
use Bl\FatooraZatca\Classes\InvoiceType;
use Bl\FatooraZatca\Classes\PaymentType;
use Bl\FatooraZatca\Classes\TaxCategoryCode;
use Bl\FatooraZatca\Invoices\B2B;
use Bl\FatooraZatca\Objects\Client;
use Bl\FatooraZatca\Objects\Invoice;
use Bl\FatooraZatca\Objects\InvoiceItem;
use Bl\FatooraZatca\Objects\Seller;
use Bl\FatooraZatca\Objects\Setting;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Razorpay\Api\Item;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;



class ZatcaController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $contactUtil;

    protected $businessUtil;

    protected $transactionUtil;

    protected $productUtil;

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ContactUtil $contactUtil, BusinessUtil $businessUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil, ProductUtil $productUtil)
    {
        $this->contactUtil = $contactUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->productUtil = $productUtil;

        $this->dummyPaymentLine = [
            'method' => '', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
            'is_return' => 0, 'transaction_no' => '',
        ];

        $this->shipping_status_colors = [
            'ordered' => 'bg-yellow',
            'packed' => 'bg-info',
            'shipped' => 'bg-navy',
            'delivered' => 'bg-green',
            'cancelled' => 'bg-red',
        ];
    }



    public function creat_zatca()
    {
        $business_id = request()->session()->get('user.business_id');
        $business = Business::where('id', $business_id)->first();
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];
        $business_details = $this->businessUtil->getDetails($business_id);
        $buyers  = Contact::where('contacts.business_id', $business_id)
            ->leftjoin('customer_groups as cg', 'cg.id', '=', 'contacts.customer_group_id')
            ->active()->onlyCustomers()->select(
                'contacts.id as id',
                'contacts.tax_number as tax_number',

                'contacts.city_subdivision_name',
                'contacts.plot_identification',
                'contacts.building_number',
                'contacts.street_name',
                'contacts.registration_name',
                DB::raw("IF(contacts.contact_id IS NULL OR contacts.contact_id='', contacts.name, CONCAT(contacts.name, ' (', contacts.contact_id, ')')) AS name"),
                'mobile',
                'address_line_2',
                'city',
                'state',
                'country',
                'zip_code',

            )->get();

        $location_id   = request()->location_id ?? $business_locations->first();
        $items = Product::join('variations', 'products.id', '=', 'variations.product_id')
            ->active()
            ->whereNull('variations.deleted_at')
            ->leftjoin('tax_rates', 'products.tax', '=', 'tax_rates.id')
            ->leftjoin('units as U', 'products.unit_id', '=', 'U.id')
            ->leftjoin(
                'variation_location_details AS VLD',
                function ($join) use ($location_id) {
                    $join->on('variations.id', '=', 'VLD.variation_id');

                    //Include Location
                    if (!empty($location_id)) {
                        $join->where(function ($query) use ($location_id) {
                            $query->where('VLD.location_id', '=', $location_id);
                            //Check null to show products even if no quantity is available in a location.
                            //TODO: Maybe add a settings to show product not available at a location or not.
                            $query->orWhereNull('VLD.location_id');
                        });
                    }
                }
            )->where('products.business_id', $business_id)
            ->where('variations.default_sell_price', '>', 0)
            ->where('products.type', '!=', 'modifier')->select(
                'products.id as id',
                'products.name as name',
                'products.type',
                'products.enable_stock',
                'variations.id as variation_id',
                'variations.name as variation',
                'VLD.qty_available',
                'variations.default_sell_price as price',
                'variations.sell_price_inc_tax as total',
                'variations.sub_sku',
                'U.short_name as unit',
                'tax_rates.amount as tax'
            )->get();

        $seller = (object)[
            'registration_name' => $business->organization_name ?? '',
            'registration_number' => $business->registration_number ?? '',
            'street_name' => $business->street_name ?? '',
            'building_number' => $business->building_number ?? '',
            'plot_identification' => $business->plot_identification ?? '',
            'city_sub_division' => $business->city_sub_division ?? '',
            'city' => $business->city ?? '',
            'postal_number' => $business->postal_number ?? '',
            'tax_number' => $business->tax_number_1 ?? '',

            'country' => 'SA'
        ];
        $statuses = Transaction::sell_statuses();
        return view('sell.temp_zatca_create', compact('buyers', 'items', 'seller', 'business_locations', 'bl_attributes', 'statuses', 'business_details'));
        // return view('sell.zatca_create', compact('buyers', 'items', 'seller'));
    }


    // public function store_zatca(Request $request)
    // {
    //     if (!auth()->user()->can('sell.create') && !auth()->user()->can('direct_sell.access') && !auth()->user()->can('so.create')) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $business_id = request()->session()->get('user.business_id');
    //     $user_id = $request->session()->get('user.id');

    //     if (!$this->moduleUtil->isSubscribed($business_id)) {
    //         return $this->moduleUtil->expiredResponse();
    //     }

    //     $validatedData = $request->all();

    //     // Update buyer information if necessary
    //     $this->updateBuyerInformation($request);

    //     // Retrieve and update business ZATCA settings if necessary
    //     $business = $this->updateZatcaSettings($business_id);

    //     // Prepare seller information
    //     $seller = $this->prepareSeller($business);

    //     // Prepare invoice items
    //     $invoiceItems = [];
    //     $totalWithoutVAT = 0;
    //     $totalVAT = 0;
    //     $totalWithVAT = 0;

    //     foreach ($validatedData['selected_products'] as $index => $product) {
    //         // Calculate net amount per line
    //         $quantity = $product['quantity'];
    //         $price = $product['price'];
    //         $taxPercent = $product['tax_percent'];
    //         $netAmount = $quantity * $price;
    //         $taxAmount = ($taxPercent / 100) * $netAmount;

    //         $invoiceItems[] = new InvoiceItem(
    //             $index,
    //             $product['name'],
    //             $quantity,
    //             $price,
    //             0, // Assuming no discount
    //             $taxAmount,
    //             $taxPercent,
    //             $netAmount + $taxAmount
    //         );

    //         $totalWithoutVAT += $netAmount;
    //         $totalVAT += $taxAmount;
    //         $totalWithVAT += $netAmount + $taxAmount;
    //     }

    //     $invoiceTime = $validatedData['invoice_time'] . ':00';

    //     $input = [
    //         'location_id' => $request->select_location_id,
    //         'contact_id' => $request->buyer_id,
    //         'status' => $request->status,
    //         'transaction_date' => $request->invoice_date . ' ' . $invoiceTime,
    //         'sell_price_tax' => $request->sell_price_tax,
    //         'business_enable_inline_tax' => $request->business_enable_inline_tax,
    //         'products' => (object)$invoiceItems,
    //         'final_total' => $totalWithVAT,
    //         'discount_amount' => 0, // Assuming no discount
    //     ];

    //     $transaction = $this->transactionUtil->createSellTransaction($business_id, $input, [
    //         'total_before_tax' => $totalWithoutVAT,
    //         'tax' => $totalVAT,
    //         'final_total' => $totalWithVAT,
    //     ], $user_id);

    //     // $this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id']);
    //     // $this->moduleUtil->getModuleData('after_sale_saved', ['transaction' => $transaction, 'input' => $input]);
    //     // $this->transactionUtil->activityLog($transaction, 'added');

    //     if ($request->invoice_number) {
    //         $transaction->update([
    //             'invoice_no' => $request->invoice_number,
    //         ]);
    //     }

    //     $uuid = Uuid::uuid4()->toString();
    //     $invoice = new Invoice(
    //         $transaction->id,
    //         $transaction->invoice_no,
    //         $uuid,
    //         $validatedData['invoice_date'],
    //         $invoiceTime,
    //         $validatedData['invoice_type'],
    //         $validatedData['payment_type'],
    //         $totalWithoutVAT,
    //         0, // Assuming no discount
    //         $totalVAT,
    //         $totalWithVAT,
    //         $invoiceItems,
    //         null,
    //         1,
    //         null,
    //         $validatedData['payment_note'],
    //         $validatedData['invoice_currency'],
    //         15,
    //         $validatedData['invoice_date']
    //     );

    //     $client = new Client(
    //         $validatedData['buyer_registration_name'],
    //         $validatedData['buyer_tax_number'],
    //         $validatedData['buyer_postal_code'],
    //         $validatedData['buyer_street'],
    //         $validatedData['buyer_building_number'],
    //         $validatedData['buyer_plot_identification'],
    //         $validatedData['buyer_city_subdivision_name'],
    //         $validatedData['buyer_city'],
    //     );

    //     $b2b = B2B::make($seller, $invoice, $client)->report();

    //     $transaction->update([
    //         'uuid' => $uuid,
    //         'qr_code' => $b2b->getQr(),
    //         'invoice_type' => $validatedData['invoice_type'],
    //         'payment_type' => $validatedData['payment_type'],
    //         'delivery_date' => $validatedData['invoice_date'],
    //     ]);

    //     echo $b2b->getQrImage();
    //     dd(
    //         $b2b->getValidationResults(),
    //         $b2b->getInfoMessages(),
    //         $b2b->getWarningMessages(),
    //         $b2b->getErrorMessages(),
    //         $b2b->getValidationResultStatus(),
    //         $b2b->getResult(),
    //         $b2b->getClearedInvoice(),
    //         $b2b->getQr(),
    //         $b2b->getInvoiceHash()
    //     );

    //     return redirect()
    //         ->action([\App\Http\Controllers\SellController::class, 'index'])
    //         ->with('status', $output);
    // }

    private function updateBuyerInformation($request)
    {
        $existing_contact = Contact::where('id', $request->buyer_id)->first();
        $contact_updates = [];

        if ((!($existing_contact->registration_name) && $request->buyer_registration_name) ||
            (($existing_contact->registration_name) && ($existing_contact->registration_name != $request->buyer_registration_name))
        ) {
            $contact_updates['registration_name'] = $request->buyer_registration_name;
        }

        if ((!($existing_contact->tax_number) && $request->buyer_tax_number) ||
            (($existing_contact->tax_number) && ($existing_contact->tax_number != $request->buyer_tax_number))
        ) {
            $contact_updates['tax_number'] = $request->buyer_tax_number;
        }

        if ((!($existing_contact->zip_code) && $request->buyer_postal_code) ||
            (($existing_contact->zip_code) && ($existing_contact->zip_code != $request->buyer_postal_code))
        ) {
            $contact_updates['zip_code'] = $request->buyer_postal_code;
        }

        if ((!($existing_contact->street_name) && $request->buyer_street) ||
            (($existing_contact->street_name) && ($existing_contact->street_name != $request->buyer_street))
        ) {
            $contact_updates['street_name'] = $request->buyer_street;
        }

        if ((!($existing_contact->building_number) && $request->buyer_building_number) ||
            (($existing_contact->building_number) && ($existing_contact->building_number != $request->buyer_building_number))
        ) {
            $contact_updates['building_number'] = $request->buyer_building_number;
        }

        if ((!($existing_contact->plot_identification) && $request->buyer_plot_identification) ||
            (($existing_contact->plot_identification) && ($existing_contact->plot_identification != $request->buyer_plot_identification))
        ) {
            $contact_updates['plot_identification'] = $request->buyer_plot_identification;
        }

        if ((!($existing_contact->city_subdivision_name) && $request->buyer_city_subdivision_name) ||
            (($existing_contact->city_subdivision_name) && ($existing_contact->city_subdivision_name != $request->buyer_city_subdivision_name))
        ) {
            $contact_updates['city_subdivision_name'] = $request->buyer_city_subdivision_name;
        }

        if ((!($existing_contact->city) && $request->buyer_city) ||
            (($existing_contact->city) && ($existing_contact->city != $request->buyer_city))
        ) {
            $contact_updates['city'] = $request->buyer_city;
        }

        $contact_updates = array_filter($contact_updates, function ($value) {
            return !is_null($value);
        });

        Contact::where('id', $request->buyer_id)->update($contact_updates);
    }

    private function updateZatcaSettings($business_id)
    {
        $business = Business::where('id', $business_id)->first();

        if (!($business->zatca_secret) || !($business->zatca_certificate) || !($business->zatca_private_key)) {
            $settings = new Setting(
                $business->fatoora_otp,
                $business->email,
                $business->common_name,
                $business->organizational_unit_name,
                $business->organization_name,
                $business->tax_number_1,
                $business->registered_address,
                $business->business_category,
                $business->egs_serial_number,
                $business->registration_number,
                $business->invoice_type ?? InvoiceReportType::BOTH,
            );
            $result = \Bl\FatooraZatca\Zatca::generateZatcaSetting($settings);
            $privateKey = $result->private_key ?? null;
            $certificate = $result->cert_production ?? null;
            $secret = $result->secret_production ?? null;
            if ($privateKey && $certificate && $secret) {
                Business::where('id', $business_id)->update([
                    'zatca_secret' => $secret,
                    'zatca_certificate' => $certificate,
                    'zatca_private_key' => $privateKey,
                ]);
            }
        }

        return Business::where('id', $business_id)->first();
    }

    private function prepareSeller($business)
    {
        return new Seller(
            $business->registration_number,
            $business->street_name,
            $business->building_number,
            $business->plot_identification,
            $business->city_sub_division,
            $business->city,
            $business->postal_number,
            $business->tax_number_1,
            $business->organization_name,
            $business->zatca_private_key,
            $business->zatca_certificate,
            $business->zatca_secret,
        );
    }






    public function store_zatca(Request $request)
    {

        // return $request->all();
        if (!auth()->user()->can('sell.create') && !auth()->user()->can('direct_sell.access') && !auth()->user()->can('so.create')) {
            abort(403, 'Unauthorized action.');
        }
        // try {
        $business_id = request()->session()->get('user.business_id');
        $business = Business::where('id', $business_id)->first();

        $user_id = $request->session()->get('user.id');

        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }
        $validatedData = $request->all();

        // Update buyer information if necessary
        $this->updateBuyerInformation($request);

        // Retrieve and update business ZATCA settings if necessary
        $business = $this->updateZatcaSettings($business_id);

        // Prepare seller information
        $seller = $this->prepareSeller($business);

        // dd($validatedData['selected_products']);

        $invoiceItems = [];
        $totalWithoutVAT = 0;
        $totalVAT = 0;
        $totalWithVAT = 0;
        $totalDiscount = 0;

        foreach ($validatedData['selected_products'] as $index => $product) {
            // Calculate net amount per line
            $quantity = $product['quantity'];
            $price = $product['price'];
            $tax = $product['tax'];
            $taxPercent = $product['tax_percent'];
            // $discount = $product['discount'] ?? 0;
            $discount = 0;

            $invoiceItems[] = new InvoiceItem(
                $index,
                $product['name'],
                $quantity,
                $price,
                $discount, // Assuming no discount
                $tax,
                $taxPercent,
                $price + $tax
            );

            $totalWithoutVAT += $price;
            $totalVAT += $tax;
            $totalDiscount += $discount;
        }

        $totalWithVAT =  $totalWithoutVAT + $totalVAT;
        // dump(
        //     $totalWithoutVAT,
        //     $totalDiscount,
        //     $totalVAT,
        //     $totalWithVAT

        // );



        $invoiceTime = $validatedData['invoice_time'] . ':00';



        $location_id =  $request->select_location_id;
        $products = Product::whereIn('products.id', $request->item_ids)->leftjoin('variations', 'products.id', '=', 'variations.product_id')
            ->active()
            ->whereNull('variations.deleted_at')
            ->leftjoin('tax_rates', 'products.tax', '=', 'tax_rates.id')
            ->leftjoin('units as U', 'products.unit_id', '=', 'U.id')
            ->leftjoin(
                'variation_location_details AS VLD',
                function ($join) use ($location_id) {
                    $join->on('variations.id', '=', 'VLD.variation_id');

                    //Include Location
                    if (!empty($location_id)) {
                        $join->where(function ($query) use ($location_id) {
                            $query->where('VLD.location_id', '=', $location_id);
                            //Check null to show products even if no quantity is available in a location.
                            //TODO: Maybe add a settings to show product not available at a location or not.
                            $query->orWhereNull('VLD.location_id');
                        });
                    }
                }
            )->where('products.business_id', $business_id)
            ->where('variations.default_sell_price', '>', 0)
            ->where('products.type', '!=', 'modifier')
            ->select(
                'products.id as product_id',
                'products.*',
                'variations.*',
                'products.name as name',
                'products.type',
                'products.enable_stock',
                'variations.id as variation_id',
                'variations.name as variation',
                'VLD.qty_available',
                'variations.default_sell_price as price',
                'variations.sell_price_inc_tax as total',
                'variations.sub_sku',
                'U.short_name as unit',
                'tax_rates.amount as tax'
            )
            ->get();

        $products_arr = [];
        $discount = 0;
        foreach ($products as $key => $product) {
            $item_tax = 0;
            // $line_discount_amount = $request->selected_products[$product->product_id]['discount'] ?? 0;
            $line_discount_amount = 0;
            $discount += $line_discount_amount;

            $unit_price_inc_tax = $request->selected_products[$product->product_id]['price'] - ($request->selected_products[$product->product_id]['discount'] ?? 0) + $item_tax;
            $products_arr[$key + 1] = [
                'product_type' => $product->type,
                "sell_line_note" => null,
                "product_id" => $product->product_id,
                "variation_id" => $product->variation_id,
                "enable_stock" => $product->enable_stock,
                "quantity" => $request->selected_products[$product->product_id]['quantity'],
                "product_unit_id" => $product->unit_id,
                "sub_unit_id" => $product->unit_id,
                "base_unit_multiplier" => $product->base_unit_multiplier ?? 1,
                "unit_price" => $request->selected_products[$product->product_id]['price'],
                "line_discount_amount" => $line_discount_amount,
                "line_discount_type" => "fixed",
                "item_tax" => $item_tax,
                "tax_id" => $product->tax,
                "unit_price_inc_tax" => $unit_price_inc_tax,
                "warranty_id" => $product->warranty_id,
            ];
        }
        $invoice_total = [
            "total_before_tax" => $request->total_before_tax,
            "tax" => $request->total_tax,
            "final_total" => $request->final_total,
        ];

        $input = [
            'location_id' => $location_id,
            'contact_id' => $request->buyer_id,
            'status' => $request->status,
            'transaction_date' => $request->invoice_date . ' ' . $request->invoice_time . ':00',
            'sell_price_tax' => $request->sell_price_tax,
            'business_enable_inline_tax' => $request->business_enable_inline_tax,
            'products' => (object)$products_arr,
            'final_total' =>  $request->final_total,
            'discount_amount' => $discount,
        ];


        $transaction = $this->transactionUtil->createSellTransaction($business_id, $input, $invoice_total, $user_id);
        $this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id']);
        $this->moduleUtil->getModuleData('after_sale_saved', ['transaction' => $transaction, 'input' => $input]);
        $this->transactionUtil->activityLog($transaction, 'added');

        if ($request->invoice_number) {
            $transaction->update([
                'invoice_no' => $request->invoice_number,
            ]);
        }

        $uuid = Uuid::uuid4()->toString();
        $invoice = new Invoice(
            $transaction->id, // Replace with appropriate ID
            $transaction->invoice_no,
            $uuid, // Replace with actual UUID or generate dynamically
            $validatedData['invoice_date'],
            $invoiceTime,
            $validatedData['invoice_type'],
            $validatedData['payment_type'],
            $totalWithoutVAT, // Total before discount
            $totalDiscount, // Total discount if applicable
            $totalVAT, // Total tax
            $totalWithVAT, // Total after tax
            $invoiceItems,
            null, // Reference to previous invoice if applicable
            1, // Adjust as needed
            null, // Additional notes or details if any
            $validatedData['payment_note'], // Adjust payment note as needed
            $validatedData['invoice_currency'],
            15, // Average VAT percentage if needed
            $validatedData['invoice_date'] // Assuming due date is the same as invoice date
        );


        $client = new Client(
            $validatedData['buyer_registration_name'],
            $validatedData['buyer_tax_number'],
            $validatedData['buyer_postal_code'],
            $validatedData['buyer_street'],
            $validatedData['buyer_building_number'],
            $validatedData['buyer_plot_identification'],
            $validatedData['buyer_city_subdivision_name'],
            $validatedData['buyer_city'],
        );
        $b2b = B2B::make($seller, $invoice, $client)->report();

        $transaction->update([
            'uuid' =>    $uuid,
            'qr_code' => $b2b->getQr(),
            'invoice_type' => $validatedData['invoice_type'],
            'payment_type' =>   $validatedData['payment_type'],
            'delivery_date' =>  $validatedData['invoice_date'],
        ]);

        $output = ['success' => 1, 'msg' => __('lang_v1.converted_to_invoice_successfully', ['invoice_no' => $transaction->invoice_no])];

        // return \SimpleSoftwareIO\QrCode\Facades\QrCode::size(300)->generate($b2b->getQr());
        // echo $b2b->getQrImage();
        // dd(
        //     // $b2b->getReportingStatus(),
        //     $b2b->getValidationResults(),
        //     $b2b->getInfoMessages(),
        //     $b2b->getWarningMessages(),
        //     $b2b->getErrorMessages(),
        //     $b2b->getValidationResultStatus(),
        //     $b2b->getResult(),
        //     $b2b->getClearedInvoice(),
        //     $b2b->getQr(),
        //     $b2b->getInvoiceHash()
        // );

        // return redirect()
        //     ->action([\App\Http\Controllers\SellController::class, 'index'])
        //     ->with('status', $output);
        // } catch (\Exception $e) {
        //     \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        //     error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        //     $output = [
        //         'success' => 0,
        //         'msg' => trans('messages.something_went_wrong'),
        //     ];
        // }
        $Qr = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->generate($b2b->getQr());

        return view('sell.invoice', [
            'logo' => $business->logo,
            'Qr' => $Qr,
            'invoice' => $invoice,
            'seller' => $seller,
            'client' => $client,
            'invoiceTypeCode' =>  $business->invoice_type,
        ])->render();


        $output = [
            'is_enabled' => false,
            'print_type' => 'browser',
            'html_content' => null,
            'printer_config' => [],
            'data' => [],
        ];
        $output['html_content'] = view('sell.invoice', [
            'invoice' => $invoice,
            'seller' => $seller,
            'client' => $client,
            'invoiceTypeCode' =>  $business->invoice_type,
        ])->render();
        $output = ['success' => 1, 'receipt' =>  $output];
    }






    private function receiptContent(
        $business_id,
        $location_id,
        $transaction_id,
        $printer_type = null,
        $is_package_slip = false,
        $from_pos_screen = true,
        $invoice_layout_id = null,
        $is_delivery_note = false
    ) {
        try {
            $output = [
                'is_enabled' => false,
                'print_type' => 'browser',
                'html_content' => null,
                'printer_config' => [],
                'data' => [],
            ];


            $location_details = BusinessLocation::find($location_id);

            if ($from_pos_screen && $location_details->print_receipt_on_invoice != 1) {
                return $output;
            }

            $output['is_enabled'] = true;

            $invoice_layout_id = !empty($invoice_layout_id) ? $invoice_layout_id : $location_details->invoice_layout_id;

            $business = Business::where('id',   $business_id)->first();
            $transaction = Transaction::where('id', $transaction_id)->first();
            $output['print_title'] = $transaction->invoice_no;

            $transaction_sell_lines = TransactionSellLine::where('transaction_id', $transaction->id)
                ->leftjoin('products', 'products.id', '=', 'product_id')
                ->leftjoin('tax_rates', 'tax_rates.id', '=', 'tax_id')
                ->select(

                    'transaction_sell_lines.*',
                    'products.*',
                    'tax_rates.amount as tax_percent',
                    'tax_rates.*',
                )
                ->get();

            $invoiceItems = [];
            foreach ($transaction_sell_lines as $index => $transaction_sell_line) {

                $invoiceItems[] = new InvoiceItem(
                    $transaction_sell_line->product_id,
                    $transaction_sell_line->name,
                    $transaction_sell_line->quantity,
                    $transaction_sell_line->unit_price,
                    $transaction_sell_line->line_discount_amount ?? 0,
                    $transaction_sell_line->unit_price_inc_tax - $transaction_sell_line->unit_price - ($transaction_sell_line->line_discount_amount ?? 0),
                    $transaction_sell_line->tax_percent,
                    $transaction_sell_line->unit_price_inc_tax,
                );
            }


            $transaction_date = explode(' ', $transaction->transaction_date);

            $invoice = new Invoice(
                $transaction->id, // Replace with appropriate ID
                $transaction->invoice_no,
                $transaction->uuid, // Replace with actual UUID or generate dynamically
                $transaction_date[0],
                $transaction_date[1],
                $transaction->invoice_type,
                $transaction->payment_type,
                $transaction->total_before_tax, // Total before discount
                $transaction->discount_amount, // Total discount if applicable
                $transaction->tax_amount, // Total tax
                $transaction->final_total, // Total after tax
                $invoiceItems,
                null, // Reference to previous invoice if applicable
                1, // Adjust as needed
                null, // Additional notes or details if any
                $transaction->payment_note, // Adjust payment note as needed
                'SAR',
                15, // Average VAT percentage if needed
                $transaction->delivery_date, // Assuming due date is the same as invoice date
            );

            $contact = Contact::where('id', $transaction->contact_id)->first();
            $client = new Client(
                $contact->registration_name,
                $contact->tax_number,
                $contact->zip_code,
                $contact->street_name,
                $contact->building_number,
                $contact->plot_identification,
                $contact->city_subdivision_name,
                $contact->city,
            );


            $seller = new Seller(
                $business->registration_number,
                $business->street_name,
                $business->building_number,
                $business->plot_identification,
                $business->city_sub_division,
                $business->city,
                $business->postal_number,
                $business->tax_number_1,
                $business->organization_name,
                $business->zatca_private_key,
                $business->zatca_certificate,
                $business->zatca_secret,
            );
            $output['html_content'] = view('sell.invoice', [
                'logo' => $business->logo ?? '',
                'Qr' =>  \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->generate($transaction->qr_code) ?? '',
                'invoice' =>  $invoice,
                'seller' =>   $seller,
                'client' => $client,
                'invoiceTypeCode' =>  $business->invoice_type,
            ])->render();
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => trans('messages.something_went_wrong'),
            ];
        }

        return $output;
    }


    /**
     * Prints invoice for sell
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function printZatcaInvoice(Request $request, $transaction_id)
    {
        if (request()->ajax()) {
            try {
                $output = [
                    'success' => 0,
                    'msg' => trans('messages.something_went_wrong'),
                ];

                $business_id = $request->session()->get('user.business_id');

                $transaction = Transaction::where('business_id', $business_id)
                    ->where('id', $transaction_id)
                    ->with(['location'])
                    ->first();

                if (empty($transaction)) {
                    return $output;
                }

                $printer_type = 'browser';
                if (!empty(request()->input('check_location')) && request()->input('check_location') == true) {
                    $printer_type = $transaction->location->receipt_printer_type;
                }

                $is_package_slip = !empty($request->input('package_slip')) ? true : false;
                $is_delivery_note = !empty($request->input('delivery_note')) ? true : false;

                $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;
                $receipt = $this->receiptContent($business_id, $transaction->location_id, $transaction_id, $printer_type, $is_package_slip, false, $invoice_layout_id, $is_delivery_note);

                if (!empty($receipt)) {
                    $output = ['success' => 1, 'receipt' => $receipt];
                }
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
                error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
                $output = [
                    'success' => 0,
                    'msg' => trans('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }


    public function generateDummyInvoicePdf()
    {
        // Dummy seller data
        $seller = new Seller(
            '301111481900003',
            'King Abdulaziz Road',
            '7962',
            '7962',
            'حي البطحاء',
            'Riyadh',
            '13326',
            '301111481900003',
            'T2 - Business Research and Development',
            'private_key_dummy',
            'certificate_dummy',
            'secret_dummy'
        );

        // Dummy client data
        $client = new Client(
            'شركة بصل الجودة - شركة بصل الجودة',
            '310768785600003',
            '14722',
            'King Khalid Road',
            '1234',
            '1234',
            'حي الروضة',
            'Riyadh'
        );

        // Dummy invoice items
        $invoiceItems = [
            new InvoiceItem(1, 'Msgeat Invoice', 1, 200.00, 0, 30.00, 15.0, 230.00),
        ];

        // Dummy invoice data
        $invoice = new Invoice(
            1,
            'INV/2023/7084',
            '42156fac-991b-4a12-a6f0-54c024edd29e',
            '2023-11-06',
            '15:54:00',
            InvoiceType::TAX_INVOICE,
            PaymentType::MULTIPLE,
            200.00,
            0.00,
            30.00,
            230.00,
            $invoiceItems,
            null,
            1,
            'Reason for exemption',
            'cash,visa',
            'SAR',
            15.0,
            '2023-11-16'
        );

        // Define the invoice type code
        $invoiceTypeCode = '0100000';
        $business = Business::where('id', 52)->first();
        $transaction = Transaction::where('invoice_no', '0076')->first();
        // Example code
        return view('sell.invoice', [
            'logo' => $business->logo,
            'Qr' =>  $transaction->qr_code,
            'invoice' => $invoice,
            'seller' => $seller,
            'client' => $client,
            'invoiceTypeCode' => $invoiceTypeCode
        ]);

        // Pass the data to the Blade view and generate the PDF
        $pdf = PDF::loadView('sell.invoice', [
            'logo' => $business->logo,
            'Qr' =>  $transaction->qr_code,
            'invoice' => $invoice,
            'seller' => $seller,
            'client' => $client,
            'invoiceTypeCode' => $invoiceTypeCode
        ]);

        // Download the PDF file
        return $pdf->download('invoice.pdf');
    }
}
