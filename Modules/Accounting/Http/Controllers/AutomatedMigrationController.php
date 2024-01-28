<?php

namespace Modules\Accounting\Http\Controllers;

use App\BusinessLocation;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountType;
use Modules\Accounting\Entities\AccountingAccTransMappingSettingAutoMigration;
use Modules\Accounting\Entities\AccountingMappingSettingAutoMigration;
use Modules\Accounting\Utils\AccountingUtil;

class AutomatedMigrationController extends Controller
{
    protected $util;
    protected $moduleUtil;
    protected $accountingUtil;

    public function __construct(Util $util, ModuleUtil $moduleUtil, AccountingUtil $accountingUtil)
    {
        $this->util = $util;
        $this->moduleUtil = $moduleUtil;
        $this->accountingUtil = $accountingUtil;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) || auth()->user()->can('accounting.index_autoMigration')) {
            // abort(403, 'Unauthorized action.');
        }
        $mappingSetting = AccountingMappingSettingAutoMigration::all();
        return view('accounting::AutomatedMigration.index', compact('mappingSetting'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) || auth()->user()->can('accounting.create_autoMigration')) {
            // abort(403, 'Unauthorized action.');
        }

        $business_locations = BusinessLocation::where('business_id', $business_id)->get();
        return view('accounting::AutomatedMigration.create', compact('business_locations'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        // try {
        DB::beginTransaction();

        $user_id = request()->session()->get('user.id');

        $account_ids_1 = $request->get('account_id1');
        $account_ids_2 = $request->get('account_id2');

        $type_1 = $request->get('type1');
        $type_2 = $request->get('type2');
        $amount_type_1 = $request->get('amount_type1');
        $amount_type_2 = $request->get('amount_type2');
        $journal_date = $request->get('journal_date');

        $accounting_settings = $this->accountingUtil->getAccountingSettings($business_id);


        $ref_count = $this->util->setAndGetReferenceCount('journal_entry');
        $prefix = !empty($accounting_settings['journal_entry_prefix']) ?
            $accounting_settings['journal_entry_prefix'] : '';



        $mappingSetting = AccountingMappingSettingAutoMigration::create([
            'name' => $request->get('migration_name'),
            'type' => $request->get('type'),
            'location_id' => $request->input('business_location_id'),
            'status' => 'final',
            'payment_status' => $request->get('payment_status'),
            'method' => $request->get('method'),
            'created_by' => $user_id,
        ]);


        $ref_no = $this->util->generateReferenceNumber('journal_entry', $ref_count, $business_id, $prefix);
        $_ref_no = $this->util->generateReferenceNumber('journal_entry', $ref_count, $business_id, $prefix);
        foreach ($account_ids_1 as $index => $account_id) {
            if (!empty($account_id)) {

                $transaction_row = [];
                $transaction_row['accounting_account_id'] = $account_id;
                $transaction_row['type'] =  $type_1[$index];
                $transaction_row['created_by'] = $user_id;
                $transaction_row['business_id'] = $business_id;
                $transaction_row['ref_no'] = $ref_no;
                $transaction_row['amount'] = $amount_type_1[$index];
                $transaction_row['operation_date'] = $this->util->uf_date($journal_date, true);
                $transaction_row['sub_type'] = 'journal_entry';
                $transaction_row['journal_entry_number'] = 1;
                $transaction_row['mapping_setting_id'] = $mappingSetting->id;


                $accounts_transactions = new AccountingAccTransMappingSettingAutoMigration();
                $accounts_transactions->fill($transaction_row);
                $accounts_transactions->save();
            }
        }

        //save details in account trnsactions table
        // if ($account_ids_2[1] != null) {
        foreach ($account_ids_2 as $index => $account_id_) {
            if (!empty($account_id_)) {

                $transaction_row_ = [];
                $transaction_row_['accounting_account_id'] = $account_id_;
                $transaction_row_['type'] =  $type_2[$index];
                $transaction_row_['created_by'] = $user_id;
                $transaction_row_['business_id'] = $business_id;
                $transaction_row_['ref_no'] = $_ref_no;
                $transaction_row_['amount'] = $amount_type_2[$index];
                $transaction_row_['operation_date'] = $this->util->uf_date($journal_date, true);
                $transaction_row_['sub_type'] = 'journal_entry';
                $transaction_row_['journal_entry_number'] = 2;

                $transaction_row_['mapping_setting_id'] = $mappingSetting->id;

                $accounts_transactions_ = new AccountingAccTransMappingSettingAutoMigration();
                $accounts_transactions_->fill($transaction_row_);
                $accounts_transactions_->save();
            }
        }
        // }

        DB::commit();

        $output = [
            'success' => 1,
            'msg' => __('lang_v1.added_success')
        ];


        return redirect()->route('automated-migration.index')->with('status', $output);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('accounting::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) || auth()->user()->can('accounting.edit_autoMigration')) {
            // abort(403, 'Unauthorized action.');
        }
        $mappingSetting = AccountingMappingSettingAutoMigration::find($id);

        $AccTransMappingSetting =  AccountingAccTransMappingSettingAutoMigration::where('mapping_setting_id', $mappingSetting->id)->get();
        $journal_entry_1 = [];
        $journal_entry_2 = [];


        foreach ($AccTransMappingSetting as $trans) {
            $account =  AccountingAccount::find($trans->accounting_account_id);
            $AccountType = AccountingAccountType::find($account->account_sub_type_id);
            $trans['account_name'] =  $account->name;
            $trans['account_primary_type'] =  $account->account_primary_type;
            $trans['account_sub_type'] =  $AccountType->name;
            if ($trans->journal_entry_number == 1)
                array_push($journal_entry_1, $trans);
            else
                array_push($journal_entry_2, $trans);
        }


        $business_locations = BusinessLocation::where('business_id', $business_id)->get();

        return view('accounting::AutomatedMigration.edit', compact('mappingSetting', 'business_locations', 'journal_entry_1', 'journal_entry_2'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');
        // return $request;
        if (
            !(auth()->user()->can('superadmin') ||
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            !(auth()->user()->can('accounting.add_journal'))
        ) {
            abort(403, 'Unauthorized action.');
        }
        // try {
        DB::beginTransaction();

        $user_id = request()->session()->get('user.id');

        $account_ids_1 = $request->get('account_id1');
        $account_ids_2 = $request->get('account_id2');

        $type_1 = $request->get('type1');
        $type_2 = $request->get('type2');
        $amount_type_1 = $request->get('amount_type1');
        $amount_type_2 = $request->get('amount_type2');
        $journal_date = $request->get('journal_date');

        $accounting_settings = $this->accountingUtil->getAccountingSettings($business_id);


        $ref_count = $this->util->setAndGetReferenceCount('journal_entry');
        $prefix = !empty($accounting_settings['journal_entry_prefix']) ?
            $accounting_settings['journal_entry_prefix'] : '';



        $mappingSetting = AccountingMappingSettingAutoMigration::find($id);
        $mappingSetting->update([
            'name' => $request->get('migration_name'),
            'type' => $request->get('type'),
            'payment_status' => $request->get('payment_status'),
            'method' => $request->get('method'),
            'location_id' => $request->input('business_location_id'),

        ]);

        AccountingAccTransMappingSettingAutoMigration::where('mapping_setting_id', $id)->delete();
        $ref_no = $this->util->generateReferenceNumber('journal_entry', $ref_count, $business_id, $prefix);
        $_ref_no = $this->util->generateReferenceNumber('journal_entry', $ref_count, $business_id, $prefix);
        foreach ($account_ids_1 as $index => $account_id) {
            if (!empty($account_id)) {

                $transaction_row = [];
                $transaction_row['accounting_account_id'] = $account_id;
                $transaction_row['type'] =  $type_1[$index];
                $transaction_row['created_by'] = $user_id;
                $transaction_row['business_id'] = $business_id;
                $transaction_row['ref_no'] = $ref_no;
                $transaction_row['amount'] = $amount_type_1[$index];
                $transaction_row['operation_date'] = $this->util->uf_date($journal_date, true);
                $transaction_row['sub_type'] = 'journal_entry';
                $transaction_row['journal_entry_number'] = 1;
                $transaction_row['mapping_setting_id'] = $mappingSetting->id;


                $accounts_transactions = new AccountingAccTransMappingSettingAutoMigration();
                $accounts_transactions->fill($transaction_row);
                $accounts_transactions->save();
            }
        }


        foreach ($account_ids_2 as $index => $account_id_) {
            if (!empty($account_id_)) {

                $transaction_row_ = [];
                $transaction_row_['accounting_account_id'] = $account_id_;
                $transaction_row_['type'] =  $type_2[$index];
                $transaction_row_['created_by'] = $user_id;
                $transaction_row_['business_id'] = $business_id;
                $transaction_row_['ref_no'] = $_ref_no;
                $transaction_row_['amount'] = $amount_type_2[$index];
                $transaction_row_['operation_date'] = $this->util->uf_date($journal_date, true);
                $transaction_row_['sub_type'] = 'journal_entry';
                $transaction_row_['journal_entry_number'] = 2;

                $transaction_row_['mapping_setting_id'] = $mappingSetting->id;

                $accounts_transactions_ = new AccountingAccTransMappingSettingAutoMigration();
                $accounts_transactions_->fill($transaction_row_);
                $accounts_transactions_->save();
            }
        }


        DB::commit();
        $output = [
            'success' => 1,
            'msg' => __('lang_v1.updated_success')
        ];


        return redirect()->route('automated-migration.index')->with('status', $output);
    }

    public function delete_dialog($id)
    {

        return view('accounting::AutomatedMigration.deleteDialog')->with(compact('id'));
    }
    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $mappingSetting = AccountingMappingSettingAutoMigration::find($id);
        AccountingAccTransMappingSettingAutoMigration::where('mapping_setting_id', $mappingSetting->id)->delete();
        $mappingSetting->delete();
        $output = [
            'success' => 1,
            'msg' => __('lang_v1.updated_success')
        ];
        return redirect()->route('automated-migration.index')->with('status', $output);
    }


    public function destroy_acc_trans_mapping_setting($id)
    {
        // $mappingSetting = AccountingMappingSettingAutoMigration::find($id);
        $acc_trans = AccountingAccTransMappingSettingAutoMigration::find($id);
        $mappingSetting_id = $acc_trans->mapping_setting_id;
        $acc_trans->delete();
        $output = [
            'success' => 1,
            'msg' => __('lang_v1.updated_success')
        ];
        return redirect()->back()->with('status', $output);
    }


    public function active_toggle($id)
    {
        $mappingSetting = AccountingMappingSettingAutoMigration::find($id);
        $new_state = $mappingSetting->active ? false : true;
        $mappingSetting->update([
            'active' => $new_state,
        ]);

        $output = [
            'success' => 1,
            'msg' => __('lang_v1.updated_success')
        ];
        return redirect()->route('automated-migration.index')->with('status', $output);
    }
}