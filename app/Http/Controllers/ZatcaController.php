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
use Bl\FatooraZatca\Helpers\ConfigHelper;
use Bl\FatooraZatca\Invoices\B2B;
use Bl\FatooraZatca\Objects\Client;
use Bl\FatooraZatca\Objects\Invoice;
use Bl\FatooraZatca\Objects\InvoiceItem;
use Bl\FatooraZatca\Objects\Seller;
use Bl\FatooraZatca\Objects\Setting;
use Carbon\Carbon;
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
            'method' => '',
            'amount' => 0,
            'note' => '',
            'card_transaction_number' => '',
            'card_number' => '',
            'card_type' => '',
            'card_holder_name' => '',
            'card_month' => '',
            'card_year' => '',
            'card_security' => '',
            'cheque_number' => '',
            'bank_account_number' => '',
            'is_return' => 0,
            'transaction_no' => '',
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
                DB::raw("
                IF(
                    contacts.contact_id IS NULL OR contacts.contact_id='', 
                    contacts.name, 
                    CONCAT_WS(' ', 
                        contacts.name, 
                        IF(contacts.supplier_business_name IS NULL OR contacts.supplier_business_name='', NULL, contacts.supplier_business_name),
                        IF(contacts.contact_id IS NULL OR contacts.contact_id='', NULL, CONCAT('(', contacts.contact_id, ')'))
                    )
                ) AS name
            "),
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
        return view('sell.zatca_create', compact('buyers', 'items', 'seller', 'business_locations', 'bl_attributes', 'statuses', 'business_details'));
        // return view('sell.zatca_create', compact('buyers', 'items', 'seller'));
    }

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
                null,
                $business->registration_number,
                $business->invoice_type ?? InvoiceReportType::BOTH,
            );

            // dd(ConfigHelper::environment());
            $result = \Bl\FatooraZatca\Zatca::generateZatcaSetting($settings);
            // dd($result);
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


        try {

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
            $seconds = (string) Carbon::now()->format('s');




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
                $price = $request->selected_products[$product->product_id]['price'];
                $item_tax = (($product->tax) *  $price / 100) ?? 0;
                $line_discount_amount = $request->selected_products[$product->product_id]['discount'] ?? 0;
                //$line_discount_amount = 0;
                $discount += $line_discount_amount;

                $unit_price_inc_tax = $request->selected_products[$product->product_id]['price'] - ($request->selected_products[$product->product_id]['discount'] ?? 0) + $item_tax;
                $products_arr[$key + 1] = [
                    'product_type' => $product->type,
                    "sell_line_note" => $request->selected_products[$product->product_id]['note'] ?? '',
                    "product_id" => $product->product_id,
                    "variation_id" => $product->variation_id,
                    "enable_stock" => $product->enable_stock,
                    "quantity" => $request->selected_products[$product->product_id]['quantity'],
                    "product_unit_id" => $product->unit_id,
                    "sub_unit_id" => $product->unit_id,
                    "base_unit_multiplier" => $product->base_unit_multiplier ?? 1,
                    "unit_price" => $price,
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
                'transaction_date' => $request->invoice_date . ' ' . $request->invoice_time . ':' . $seconds,
                'sell_price_tax' => $request->sell_price_tax,
                'business_enable_inline_tax' => $request->business_enable_inline_tax,
                'products' => (object)$products_arr,
                'final_total' =>  $request->final_total,
                'discount_amount' => $discount,
                'custom_field_1' => $request->invoice_from_date ?? '',
                'custom_field_2' => $request->invoice_to_date ?? '',
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



            if ($request->status == "final") {
                $this->updateBuyerInformation($request);
                $business = $this->updateZatcaSettings($business_id);
                $seller = $this->prepareSeller($business);
                $invoiceItems = [];
                $totalWithoutVAT = 0;
                $totalVAT = 0;
                $totalWithVAT = 0;
                $totalDiscount = 0;

                foreach ($validatedData['selected_products'] as $index => $product) {
                    // Calculate net amount per line

                    $quantity = $product['quantity'];
                    $discount = $product['discount'] ?? 0;
                    $price = $product['price'];
                    $taxPercent = $product['tax_percent'] ?? 0;

                    // Calculate the total price for the quantity before discount
                    $priceBeforeDiscount = round($price * $quantity, 2);

                    // Calculate the discount amount
                    $discountAmount = round($discount * $quantity, 2);

                    // Calculate the price after discount
                    $priceAfterDiscount = $priceBeforeDiscount - $discountAmount;

                    // Calculate the tax amount based on the discounted price
                    $taxAmount = round($taxPercent * $priceAfterDiscount / 100, 2);

                    $invoiceItems[] = new InvoiceItem(
                        $index,
                        $product['name'],
                        $quantity,
                        $priceBeforeDiscount,
                        $discountAmount, // Applying the discount
                        $taxAmount,
                        $taxPercent,
                        $priceAfterDiscount + $taxAmount,
                    );

                    $totalWithoutVAT += $priceBeforeDiscount;
                    $totalVAT += $taxAmount;
                    $totalDiscount += $discountAmount;
                }

                // Calculate the total amount with VAT
                $totalWithVAT = $totalWithoutVAT - $totalDiscount + $totalVAT;

                $invoiceTime = $validatedData['invoice_time'] . ':' . $seconds;

                // return $invoiceItems or use them as needed

                $discountItems = [
                    new \Bl\FatooraZatca\Objects\DiscountItem('Discount On Invoice', $totalDiscount),
                ];
                if ($totalDiscount == 0) {
                    $discountItems = [];
                }
                $subtotal = $totalWithoutVAT - $totalVAT - $totalDiscount;
                $tax = $subtotal * 0.15;
                $total = $subtotal + $tax;

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
                    $discountItems, // Total discount if applicable
                    $tax, // Total tax
                    $total, // Total after tax
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
                    'payment_status' => 'due',
                    'delivery_date' =>  $validatedData['invoice_date'],
                    'total_before_tax' =>  $totalWithoutVAT + $totalDiscount,
                    'final_total' => $totalWithVAT,
                    'tax_amount' => $totalVAT,
                    'discount_amount' => $totalDiscount,
                ]);


                $output = '';

                // return   [
                //     // '1' => $invoiceItems[0],
                //     '2' => $invoice,
                //     // '3' => $b2b->getWarningMessages(),
                //     // '4' => $b2b->getErrorMessages(),
                // ];

                if (empty($b2b->getWarningMessages()) & empty($b2b->getErrorMessages())) {
                    $output = ['success' => 1, 'msg' => __('lang_v1.converted_to_invoice_successfully', ['invoice_no' => $transaction->invoice_no])];
                } else if (empty($b2b->getWarningMessages())) {
                    $output = ['success' => 0, 'msg' => $b2b->getWarningMessages()];
                } else if (empty($b2b->getErrorMessages())) {
                    $output = ['success' => 0, 'msg' => $b2b->getErrorMessages()];
                } else {
                    $output = ['success' => 0, 'msg' => $b2b->getErrorMessages() . " " . $b2b->getWarningMessages()];
                }
            } else {
                $output = ['success' => 1, 'msg' => __('lang_v1.converted_to_invoice_successfully')];
            }
            //sell_line_note




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

            return redirect()
                ->action([\App\Http\Controllers\SellController::class, 'index'])
                ->with('status', $output);
        } catch (\Exception $e) {
            dd('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => 0, 'msg' => 'File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage()];
            return redirect()->back()->with('status', $output);
        }
    }

    public function zatcaSellReturn($id)
    {
        if (!auth()->user()->can('access_sell_return') && !auth()->user()->can('access_own_sell_return')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

        $sell = Transaction::where('business_id', $business_id)
            ->with(['sell_lines', 'location', 'return_parent', 'contact', 'tax', 'sell_lines.sub_unit', 'sell_lines.product', 'sell_lines.product.unit'])
            ->find($id);

        foreach ($sell->sell_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_sell_line = $this->transactionUtil->recalculateSellLineTotals($business_id, $value);
                $sell->sell_lines[$key] = $formated_sell_line;
            }


            $sell->sell_lines[$key]->formatted_qty = $this->transactionUtil->num_f($value->quantity, false, null, true);
        }
        return view('sell.zatca_sell_return')
            ->with(compact('sell'));
    }

    public function storeZatcaSellReturn(Request $request)
    {

        // return $request->all();
        if (!auth()->user()->can('access_sell_return') && !auth()->user()->can('access_own_sell_return')) {
            abort(403, 'Unauthorized action.');
        }

        // try {
        $input = $request->except('_token');
        $rounding_amount = $input['adjustment_amount'] ?? 0;
        if (!empty($input['products'])) {
            $business_id = $request->session()->get('user.business_id');

            //Check if subscribed or not
            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action([\App\Http\Controllers\SellReturnController::class, 'index']));
            }

            $user_id = $request->session()->get('user.id');

            DB::beginTransaction();

            $sell_return = $this->transactionUtil->addSellReturn($input, $business_id, $user_id);
            $receipt = $this->receiptContent($business_id, $sell_return->location_id, $sell_return->id);

            // return  $sell_return->id;
            $saveAutomigration = $this->transactionUtil->createTransactionJournal_entry($sell_return->id);

            DB::commit();
            Transaction::where('id', $sell_return->id)->update([
                'invoice_type' => \Bl\FatooraZatca\Classes\InvoiceType::DEBIT_NOTE,
                'payment_type' => \Bl\FatooraZatca\Classes\PaymentType::MULTIPLE,
            ]);
            ////////////////////////////////////////////////////////////////////////////////
            // extract zatca invoice info
            // $transaction_id = $input['transaction_id'];
            $transaction_id = $sell_return->id;
            $business = Business::where('id',   $business_id)->first();
            $transaction = Transaction::where('id', $transaction_id)->first();
            $output['print_title'] = $transaction->invoice_no;

            $transaction_sell_lines = TransactionSellLine::where('transaction_id', $transaction->id)
                ->leftjoin('products', 'products.id', '=', 'product_id')
                ->leftjoin('tax_rates', 'tax_rates.id', '=', 'tax_id')
                ->select(
                    'products.name as product_name',
                    'transaction_sell_lines.*',
                    'products.*',
                    'tax_rates.amount as tax_percent',
                    'tax_rates.*',
                )
                ->get();

            $sell_line_ids = [];
            foreach ($input['products'] as $tmp) {
                $sell_line_ids[] = $tmp['sell_line_id'];
            }

            $transaction_sell_lines = TransactionSellLine::whereIn('transaction_sell_lines.id', $sell_line_ids)
                ->leftjoin('products', 'products.id', '=', 'product_id')
                ->leftjoin('tax_rates', 'tax_rates.id', '=', 'tax_id')
                ->select(
                    'products.name as product_name',
                    'transaction_sell_lines.*',
                    'products.*',
                    'tax_rates.amount as tax_percent',
                    'tax_rates.*',
                )
                ->get();

            $invoiceItems = [];
            $notest = [];
            $total_discount = 0;
            $total_before_tax = 0;
            $final_total = 0;
            $total_tax = 0;

            if ($input['discount_type'] == 'fixed') {
                $total_discount += (float)$input['discount_amount'];
            }

            // dd($sell_return, $input, $transaction_sell_lines);
            foreach ($transaction_sell_lines as $index => $transaction_sell_line) {
                // foreach ($input['products'] as $index => $transaction_sell_line) {
                $transaction_sell_line = (object)$transaction_sell_line;
                $notest[$transaction_sell_line->product_id] =  $transaction_sell_line->sell_line_note;


                $discountAmount = round($transaction_sell_line->line_discount_amount * $transaction_sell_line->quantity_returned, 2);
                $total_discount += $discountAmount;
                $priceBeforeDiscount = round($transaction_sell_line->unit_price_before_discount * $transaction_sell_line->quantity_returned, 2);
                $priceAfterDiscount = $priceBeforeDiscount - $discountAmount;
                $taxAmount = round($transaction_sell_line->tax_percent * $priceAfterDiscount / 100, 2);
                $invoiceItems[] = new InvoiceItem(
                    $transaction_sell_line->product_id,
                    $transaction_sell_line->product_name,
                    $transaction_sell_line->quantity_returned,
                    $priceBeforeDiscount,
                    $discountAmount,
                    $taxAmount,
                    $transaction_sell_line->tax_percent,
                    $priceAfterDiscount + $taxAmount,
                );
                $total_before_tax += $priceAfterDiscount;
                $total_tax += $taxAmount;
                $final_total += ($priceAfterDiscount + $taxAmount);
            }
            if ($input['discount_type'] == 'percentage') {
                $total_discount += ((float)$input['discount_amount'] / 100) * ($total_before_tax);
                $final_total -= $total_discount;
            }
            // return $transaction_sell_lines;

            $transaction_date = explode(' ', $transaction->transaction_date);
            $final_total += $rounding_amount;


            $invoice = new Invoice(
                $transaction->id, // Replace with appropriate ID
                $transaction->invoice_no,
                $transaction->uuid ?? '', // Replace with actual UUID or generate dynamically
                $transaction_date[0],
                $transaction_date[1],
                $transaction->invoice_type,
                $transaction->payment_type,
                $total_before_tax, // Total before discount
                $total_discount, // Total discount if applicable
                $total_tax, // Total tax
                $final_total, // Total after tax
                $invoiceItems,
                null, // Reference to previous invoice if applicable
                1, // Adjust as needed
                null, // Additional notes or details if any
                $transaction->payment_note, // Adjust payment note as needed
                'SAR',
                15, // Average VAT percentage if needed
                $transaction->delivery_date, // Assuming due date is the same as invoice date
                $rounding_amount,
            );
            // dd($invoice);
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


            /////////
            // create zatca return
            $selected_products = [];
            $item_ids = [];
            foreach ($invoice->invoice_items as $item) {
                $id = $item->id;
                $item_ids[] =  $id;
                if ($item->quantity > 0) {
                    $selected_products[$id] = [
                        "name" => $item->product_name,
                        "quantity" =>  $item->quantity,
                        "price" => number_format($item->price / $item->quantity, 3, '.', ''),
                        "discount" => number_format($item->discount, 3, '.', ''),
                        "tax" => number_format($item->tax / $item->quantity, 3, '.', ''),
                        "tax_percent" => number_format($item->tax_percent, 3, '.', ''),
                        "total" => number_format($item->total / $item->quantity, 3, '.', ''),
                        "note" => $item->discount_reason,
                    ];
                }
            }

            $request = (object)[
                'buyer_id' => $transaction->contact_id,
                'seller_name' => $seller->registration_name,
                'seller_tax_number' => $seller->tax_number,
                'buyer_registration_name' => $client->registration_name,
                'buyer_tax_number' => $client->tax_number,
                'buyer_postal_code' => $client->postal_number,
                'buyer_street' => $client->street_name,
                'buyer_building_number' => $client->building_number,
                'buyer_plot_identification' => $client->plot_identification,
                'buyer_city_subdivision_name' => $client->city_subdivision_name,
                'buyer_city' => $client->city,
                'invoice_number' => $invoice->invoice_number,
                'invoice_type' =>   \Bl\FatooraZatca\Classes\InvoiceType::DEBIT_NOTE,
                'invoice_date' => $invoice->invoice_date,
                'invoice_time' => $invoice->invoice_time,
                'payment_type' => $invoice->payment_type,
                'payment_note' => $invoice->payment_note,
                'invoice_currency' => $invoice->currency,
                'selected_products' => $selected_products,
                'total_before_tax' => $invoice->price,
                'total_tax' => $invoice->tax,
                'final_total' => $invoice->total,
                'select_location_id' => $transaction->location_id,
                'status' => $transaction->status,
                'item_ids' => $item_ids,
            ];

            try {

                // return $request->all();
                if (!auth()->user()->can('sell.create') && !auth()->user()->can('direct_sell.access') && !auth()->user()->can('so.create')) {
                    abort(403, 'Unauthorized action.');
                }
                // try {
                $business_id = request()->session()->get('user.business_id');
                $business = Business::where('id', $business_id)->first();

                $user_id = request()->session()->get('user.id');

                if (!$this->moduleUtil->isSubscribed($business_id)) {
                    return $this->moduleUtil->expiredResponse();
                }
                $validatedData = (array) $request;
                $seconds = (string) Carbon::now()->format('s');




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
                    if (in_array($product->product_id, $selected_products)) {
                        $price = $request->selected_products[$product->product_id]['price'];
                        $item_tax = (($product->tax) *  $price / 100) ?? 0;
                        $line_discount_amount = $request->selected_products[$product->product_id]['discount'] ?? 0;
                        //$line_discount_amount = 0;
                        $discount += $line_discount_amount;

                        $unit_price_inc_tax = $request->selected_products[$product->product_id]['price'] - ($request->selected_products[$product->product_id]['discount'] ?? 0) + $item_tax;
                        $products_arr[$key + 1] = [
                            'product_type' => $product->type,
                            "sell_line_note" => $request->selected_products[$product->product_id]['note'] ?? '',
                            "product_id" => $product->product_id,
                            "variation_id" => $product->variation_id,
                            "enable_stock" => $product->enable_stock,
                            "quantity" => $request->selected_products[$product->product_id]['quantity'],
                            "product_unit_id" => $product->unit_id,
                            "sub_unit_id" => $product->unit_id,
                            "base_unit_multiplier" => $product->base_unit_multiplier ?? 1,
                            "unit_price" => $price,
                            "line_discount_amount" => $line_discount_amount,
                            "line_discount_type" => "fixed",
                            "item_tax" => $item_tax,
                            "tax_id" => $product->tax,
                            "unit_price_inc_tax" => $unit_price_inc_tax,
                            "warranty_id" => $product->warranty_id,
                        ];
                    }
                }




                if ($request->status == "final") {
                    $this->updateBuyerInformation($request);
                    $business = $this->updateZatcaSettings($business_id);
                    $seller = $this->prepareSeller($business);
                    $invoiceItems = [];
                    $totalWithoutVAT = 0;
                    $totalVAT = 0;
                    $totalWithVAT = 0;
                    $totalDiscount = 0;

                    foreach ($validatedData['selected_products'] as $index => $product) {
                        // Calculate net amount per line

                        $quantity = $product['quantity'];
                        $discount = $product['discount'] ?? 0;
                        $price = $product['price'];
                        $taxPercent = $product['tax_percent'] ?? 0;

                        // Calculate the total price for the quantity before discount
                        $priceBeforeDiscount = round($price * $quantity, 2);

                        // Calculate the discount amount
                        $discountAmount = round($discount * $quantity, 2);

                        // Calculate the price after discount
                        $priceAfterDiscount = $priceBeforeDiscount - $discountAmount;

                        // Calculate the tax amount based on the discounted price
                        $taxAmount = round($taxPercent * $priceAfterDiscount / 100, 2);

                        $invoiceItems[] = new InvoiceItem(
                            $index,
                            $product['name'],
                            $quantity,
                            $priceBeforeDiscount,
                            $discountAmount, // Applying the discount
                            $taxAmount,
                            $taxPercent,
                            $priceAfterDiscount + $taxAmount,
                        );

                        $totalWithoutVAT += $priceBeforeDiscount;
                        $totalVAT += $taxAmount;
                        $totalDiscount += $discountAmount;
                    }

                    $totalWithVAT = $totalWithoutVAT - $totalDiscount + $totalVAT;

                    $invoiceTime = $validatedData['invoice_time'];
                    $totalWithVAT += $rounding_amount;

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
                        $validatedData['invoice_date'], // Assuming due date is the same as invoice date
                        $rounding_amount
                    );

                    foreach ($invoiceItems as $invoiceItem) {
                        error_log($invoiceItem->product_name);
                        error_log($invoiceItem->quantity);
                        error_log("-----------");
                    }

                    error_log('total: ' . $invoice->total);
                    error_log('rounding: ' . $invoice->rounding_amount);





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
                        'payment_status' => 'due',
                        'delivery_date' =>  $validatedData['invoice_date'],
                        'total_before_tax' =>  $totalWithoutVAT + $totalDiscount,
                        'final_total' => $totalWithVAT,
                        'tax_amount' => $totalVAT,
                        'discount_amount' => $totalDiscount,
                    ]);


                    $output = '';



                    if (empty($b2b->getWarningMessages()) & empty($b2b->getErrorMessages())) {
                        $output = ['success' => 1, 'msg' => __('lang_v1.converted_to_invoice_successfully', ['invoice_no' => $transaction->invoice_no])];
                    } else if (empty($b2b->getWarningMessages())) {
                        $output = ['success' => 0, 'msg' => $b2b->getWarningMessages()];
                    } else if (empty($b2b->getErrorMessages())) {
                        $output = ['success' => 0, 'msg' => $b2b->getErrorMessages()];
                    } else {
                        $output = ['success' => 0, 'msg' => $b2b->getErrorMessages() . " " . $b2b->getWarningMessages()];
                    }
                } else {
                    $output = ['success' => 1, 'msg' => __('lang_v1.converted_to_invoice_successfully')];
                }

                $output = [
                    'success' => 1,
                    'msg' => __('lang_v1.success'),
                    'redirect_url' => route('sell.printZatcaRefundInvoice', ['transaction_id' => $transaction->id]),
                ];

                return response()->json($output);
            } catch (\Exception $e) {
                // return 'File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage();
                error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
                $output = ['success' => 0, 'msg' => 'File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage()];
                return redirect()->back()->with('status', $output);
            }
            ////////////////////////////////////////////////////////////////////////////////

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
                'receipt' => $receipt,
            ];
        }


        return "test return";
    }

    private function receiptContent(
        $business_id,
        $location_id,
        $transaction_id,
        $printer_type = null
    ) {
        $output = [
            'is_enabled' => false,
            'print_type' => 'browser',
            'html_content' => null,
            'printer_config' => [],
            'data' => [],
        ];

        $business_details = $this->businessUtil->getDetails($business_id);
        $location_details = BusinessLocation::find($location_id);

        //Check if printing of invoice is enabled or not.
        if ($location_details->print_receipt_on_invoice == 1) {
            //If enabled, get print type.
            $output['is_enabled'] = true;

            $invoice_layout = $this->businessUtil->invoiceLayout($business_id, $location_details->invoice_layout_id);

            //Check if printer setting is provided.
            $receipt_printer_type = is_null($printer_type) ? $location_details->receipt_printer_type : $printer_type;

            $receipt_details = $this->transactionUtil->getReceiptDetails($transaction_id, $location_id, $invoice_layout, $business_details, $location_details, $receipt_printer_type);

            $lines = [];
            foreach ($receipt_details->lines as $line) {
                if ($line['quantity'] == 0) {
                    continue;
                }
                array_push($lines, $line);
            }

            $receipt_details->lines = $lines;

            //If print type browser - return the content, printer - return printer config data, and invoice format config
            $output['print_title'] = $receipt_details->invoice_no;
            if ($receipt_printer_type == 'printer') {
                $output['print_type'] = 'printer';
                $output['printer_config'] = $this->businessUtil->printerConfig($business_id, $location_details->printer_id);
                $output['data'] = $receipt_details;
            } else {
                $output['html_content'] = view('sell_return.receipt', compact('receipt_details'))->render();
            }
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

        $business_id = $request->session()->get('user.business_id');
        $business = Business::where('id',   $business_id)->first();
        $transaction = Transaction::where('id', $transaction_id)->first();
        $output['print_title'] = $transaction->invoice_no;

        $transaction_sell_lines = TransactionSellLine::where('transaction_id', $transaction->id)
            ->leftjoin('products', 'products.id', '=', 'product_id')
            ->leftjoin('tax_rates', 'tax_rates.id', '=', 'tax_id')
            ->select(
                'products.name as product_name',
                'transaction_sell_lines.*',
                'products.*',
                'tax_rates.amount as tax_percent',
                'tax_rates.*',
            )
            ->get();

        // dd($transaction_sell_lines);

        $invoiceItems = [];
        $notest = [];
        $total_discount = 0;
        foreach ($transaction_sell_lines as $index => $transaction_sell_line) {
            $notest[$transaction_sell_line->product_id] =  $transaction_sell_line->sell_line_note;


            $discountAmount = round($transaction_sell_line->line_discount_amount * $transaction_sell_line->quantity, 2);
            $total_discount += $discountAmount;
            $priceBeforeDiscount = round($transaction_sell_line->unit_price_before_discount * $transaction_sell_line->quantity, 2);
            $priceAfterDiscount = $priceBeforeDiscount - $discountAmount;
            $taxAmount = round($transaction_sell_line->tax_percent * $priceAfterDiscount / 100, 2);
            $invoiceItems[] = new InvoiceItem(
                $transaction_sell_line->product_id,
                $transaction_sell_line->product_name,
                $transaction_sell_line->quantity,
                $priceBeforeDiscount,
                $discountAmount,
                $taxAmount,
                $transaction_sell_line->tax_percent,
                $priceAfterDiscount + $taxAmount,
            );
        }
        // return $transaction_sell_lines;

        $transaction_date = explode(' ', $transaction->transaction_date);

        $invoice = new Invoice(
            $transaction->id, // Replace with appropriate ID
            $transaction->invoice_no,
            $transaction->uuid ?? '', // Replace with actual UUID or generate dynamically
            $transaction_date[0],
            $transaction_date[1],
            $transaction->invoice_type,
            $transaction->payment_type,
            $transaction->total_before_tax, // Total before discount
            $total_discount, // Total discount if applicable
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


        $location_details = BusinessLocation::find($transaction->location_id);

        $businessUtil = new BusinessUtil();
        $invoice_layout_id = $location_details->invoice_layout_id;
        $invoice_layout = $businessUtil->invoiceLayout($business_id, $invoice_layout_id);
        $footer_text = $invoice_layout->footer_text ?? '';

        $fromDate = $transaction->custom_field_1;
        $toDate = $transaction->custom_field_2;
        // return [
        //     '1' => $invoice,
        // ];

        // return [
        //     'logo' => $business->logo ?? '',
        //     'Qr' =>  $transaction->qr_code ? \SimpleSoftwareIO\QrCode\Facades\QrCode::size(180)->generate($transaction->qr_code) :  '',
        //     'invoice' =>  $invoice,
        //     'seller' =>   $seller,
        //     'client' => $client,
        //     'invoiceTypeCode' =>  $business->invoice_type,
        //     'footer_text' => $footer_text,
        //     'fromDate' => $fromDate,
        //     'toDate' => $toDate,
        //     'notest' =>   $notest,
        // ];

        return view('sell.invoice', [
            'logo' => $business->logo ?? '',
            'Qr' =>  $transaction->qr_code ? \SimpleSoftwareIO\QrCode\Facades\QrCode::size(180)->generate($transaction->qr_code) :  '',
            'invoice' =>  $invoice,
            'seller' =>   $seller,
            'client' => $client,
            'invoiceTypeCode' =>  $business->invoice_type,
            'footer_text' => $footer_text,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'notest' =>   $notest,
        ]);
    }


    public function printZatcaRefundInvoice(Request $request, $transaction_id)
    {

        $business_id = $request->session()->get('user.business_id');
        $business = Business::where('id',   $business_id)->first();
        $transaction = Transaction::where('id', $transaction_id)->first();
        $output['print_title'] = $transaction->invoice_no;
        $parent_transaction =  Transaction::where('id', $transaction->return_parent_id)->first();
        $transaction_sell_lines = TransactionSellLine::where('transaction_id', $parent_transaction->id)
            ->leftjoin('products', 'products.id', '=', 'product_id')
            ->leftjoin('tax_rates', 'tax_rates.id', '=', 'tax_id')
            ->select(
                'products.name as product_name',
                'transaction_sell_lines.*',
                'products.*',
                'tax_rates.amount as tax_percent',
                'tax_rates.*',
            )
            ->get();

        // dd($transaction_sell_lines);

        $invoiceItems = [];
        $notest = [];
        $total_discount = 0;
        $total_before_tax = 0;
        $total_tax = 0;
        $final_total = 0;
        $rounding_amount = $transaction->adjustment_amount ?? 0;
        foreach ($transaction_sell_lines as $index => $transaction_sell_line) {
            $notest[$transaction_sell_line->product_id] =  $transaction_sell_line->sell_line_note;


            $discountAmount = round($transaction_sell_line->line_discount_amount * $transaction_sell_line->quantity_returned, 2);
            $total_discount += $discountAmount;
            $priceBeforeDiscount = round($transaction_sell_line->unit_price_before_discount * $transaction_sell_line->quantity_returned, 2);
            $priceAfterDiscount = $priceBeforeDiscount - $discountAmount;
            $taxAmount = round($transaction_sell_line->tax_percent * $priceAfterDiscount / 100, 2);
            $invoiceItems[] = new InvoiceItem(
                $transaction_sell_line->product_id,
                $transaction_sell_line->product_name,
                $transaction_sell_line->quantity_returned,
                $priceBeforeDiscount,
                $discountAmount,
                $taxAmount,
                $transaction_sell_line->tax_percent,
                $priceAfterDiscount + $taxAmount,
            );
            $total_before_tax += $priceAfterDiscount;
            $total_tax += $taxAmount;
            $final_total += ($priceAfterDiscount + $taxAmount);
        }
        // return $transaction_sell_lines;
        $final_total += $rounding_amount;
        $transaction_date = explode(' ', $transaction->transaction_date);

        $invoice = new Invoice(
            $transaction->id, // Replace with appropriate ID
            $transaction->invoice_no,
            $transaction->uuid ?? '', // Replace with actual UUID or generate dynamically
            $transaction_date[0],
            $transaction_date[1],
            \Bl\FatooraZatca\Classes\InvoiceType::DEBIT_NOTE,
            $transaction->payment_type,
            $total_before_tax, // Total before discount
            $total_discount, // Total discount if applicable
            $total_tax, // Total tax
            $final_total, // Total after tax
            $invoiceItems,
            null, // Reference to previous invoice if applicable
            1, // Adjust as needed
            null, // Additional notes or details if any
            $transaction->payment_note, // Adjust payment note as needed
            'SAR',
            15, // Average VAT percentage if needed
            $transaction->delivery_date, // Assuming due date is the same as invoice date
            $rounding_amount,

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


        $location_details = BusinessLocation::find($transaction->location_id);

        $businessUtil = new BusinessUtil();
        $invoice_layout_id = $location_details->invoice_layout_id;
        $invoice_layout = $businessUtil->invoiceLayout($business_id, $invoice_layout_id);
        $footer_text = $invoice_layout->footer_text ?? '';

        $fromDate = $parent_transaction->custom_field_1;
        $toDate = $parent_transaction->custom_field_2;
        // return [
        //     '1' => $invoice,
        // ];

        // return [
        //     'logo' => $business->logo ?? '',
        //     'Qr' =>  $transaction->qr_code ? \SimpleSoftwareIO\QrCode\Facades\QrCode::size(180)->generate($transaction->qr_code) :  '',
        //     'invoice' =>  $invoice,
        //     'seller' =>   $seller,
        //     'client' => $client,
        //     'invoiceTypeCode' =>  $business->invoice_type,
        //     'footer_text' => $footer_text,
        //     'fromDate' => $fromDate,
        //     'toDate' => $toDate,
        //     'notest' =>   $notest,
        // ];
        $debit_total = $invoice_layout->cn_amount_label ?? null;
        $debit_title = $invoice_layout->cn_heading ?? null;
        $debit_number = $invoice_layout->cn_no_label ?? null;




        return view('sell.refund_invoice', [
            'parent_invoice_number' => $parent_transaction->invoice_no,
            'logo' => $business->logo ?? '',
            'Qr' =>  $transaction->qr_code ? \SimpleSoftwareIO\QrCode\Facades\QrCode::size(180)->generate($transaction->qr_code) :  '',
            'invoice' =>  $invoice,
            'seller' =>   $seller,
            'client' => $client,
            'invoiceTypeCode' =>  $business->invoice_type,
            'footer_text' => $footer_text,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'notest' =>   $notest,
            'debit_total' => $debit_total,
            'debit_title' => $debit_title,
            'debit_number' => $debit_number,
        ]);
    }

    public function verifySettings(Request $request)
    {

        try {
            $settingsData = $request->input('zatca_settings');
            $sellerData = $request->input('zatca_seller');



            $zatca_settings = [
                'fatoora_otp' =>  $settingsData['otp'] ?? null,
                'email' =>  $settingsData['emailAddress'] ?? null,
                'organizational_unit_name' =>  $settingsData['organizationalUnitName'] ?? null,
                'organization_name' =>  $settingsData['organizationName'] ?? null,
                'registered_address' =>  $settingsData['registeredAddress'] ?? null,
                'business_category' =>   $settingsData['businessCategory'] ?? null,
                'registration_number' =>   $settingsData['registrationNumber'] ?? null,
                'egs_serial_number' =>   $settingsData['egsSerialNumber'] ?? null,
                'common_name' =>  $settingsData['commonName'] ?? null,
                'tax_number_1' =>  $settingsData['taxNumber'] ?? null,
                'postal_number' =>  $sellerData['postal_number'] ?? null,
                'city' =>  $sellerData['city'] ?? null,
                'street_name' =>  $sellerData['street_name'] ?? null,
                'building_number' =>  $sellerData['building_number'] ?? null,
                'plot_identification' =>  $sellerData['plot_identification'] ?? null,
                'city_sub_division' =>  $sellerData['city_sub_division'] ?? null,
                'invoice_type' =>   $settingsData['invoiceType'] ?? null,
            ];

            $zatca_settings = array_filter($zatca_settings, function ($value) {
                return !is_null($value);
            });

            $business_id = $request->session()->get('user.business_id');
            $business = Business::where('id',   $business_id)->first();


            $settings = new Setting(
                $business->fatoora_otp,
                $business->email,
                $business->common_name,
                $business->organizational_unit_name,
                $business->organization_name,
                $business->tax_number_1,
                $business->registered_address,
                $business->business_category,
                null,
                $business->registration_number,
                $business->invoice_type ?? InvoiceReportType::BOTH,
            );

            // dd(ConfigHelper::environment());
            $result = \Bl\FatooraZatca\Zatca::generateZatcaSetting($settings);
            // dd($result);
            $privateKey = $result->private_key ?? null;
            $certificate = $result->cert_production ?? null;
            $secret = $result->secret_production ?? null;
            if ($privateKey && $certificate && $secret) {
                $business->update($zatca_settings);
                Business::where('id', $business_id)->update([
                    'zatca_secret' => $secret,
                    'zatca_certificate' => $certificate,
                    'zatca_private_key' => $privateKey,
                ]);
                return response()->json(['success' => 1, 'msg' => __('zatca.settings_are_good')]);
            }
            return response()->json(['success' => 0, 'msg' => __('zatca.settings_are_bad')]);
        } catch (\Exception $e) {

            return response()->json(['success' => 0, 'msg' => $e->getMessage()]);
        }
    }
}
