<?php

namespace App\Services;

use App\Models\Bank;
use App\Models\MpesaAccount;
use App\Models\Gateway;
use App\Models\GatewayCurrency;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GatewayService
{
    use ResponseTrait;

    public function getAll()
    {
        return Gateway::where('owner_user_id', auth()->id())->get();
    }

    public function getActiveAll($ownerUserId)
    {
        $gateway = Gateway::where('owner_user_id', $ownerUserId)->where('status', ACTIVE)->get();
        return $gateway?->makeHidden(['created_at', 'deleted_at', 'updated_at', 'owner_user_id']);
    }

    public function getActiveAllWithCurrencies($ownerUserId)
    {
        $gateways = Gateway::where('owner_user_id', $ownerUserId)->where('status', ACTIVE)->get();
        foreach ($gateways as $gateway) {
            $gateway->image = asset($gateway->image);
            $gateway->currencies = GatewayCurrency::where('gateway_id', $gateway->id)->get();
        }
        return $gateways?->makeHidden(['created_at', 'deleted_at', 'updated_at', 'owner_user_id']);
    }

    public function getActiveBanks()
    {
        $data = Bank::where('owner_user_id', auth()->user()->owner_user_id)->where('status', ACTIVE)->get();
        return $data?->makeHidden(['created_at', 'deleted_at', 'updated_at', 'owner_user_id']);
    }

    public function getActiveMpesaAccounts()
    {
        $data = MpesaAccount::where('owner_user_id', auth()->user()->owner_user_id)->where('status', ACTIVE)->get();
        return $data?->makeHidden(['created_at', 'deleted_at', 'updated_at', 'owner_user_id']);
    }

    public function getInfo($id)
    {
        return Gateway::findOrFail($id);
    }

    public function getCurrenciesByGatewayId($id)
    {
        $data['gateway'] = $this->getInfo($id);
        if ($data['gateway']->slug == 'bank') {
            $data['banks'] = $this->banks();
        }elseif ($data['gateway']->slug == 'mpesa') {
           $data['mpesaAccounts'] = $this->mpesaAccounts();
        }
        $data['image'] = $data['gateway']->icon;
        $currencies = GatewayCurrency::where('owner_user_id', auth()->id())->where('gateway_id', $id)->get();
        foreach ($currencies as $currency) {
            $currency->symbol;
        }
        $data['currencies'] = $currencies;
        return $this->success($data);
    }

    public function banks()
    {
        return Bank::where('owner_user_id', auth()->id())->get();
    }

    public function mpesaAccounts()
    {
        return MpesaAccount::where('owner_user_id', auth()->id())->get();
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $ownerUserId = auth()->id();
            $id = $request->get('id', '');
            if ($id != '') {
                $gateway = Gateway::where('owner_user_id', $ownerUserId)->findOrFail($request->id);
            } else {
                $gateway = new Gateway();
            }
            if ($gateway->slug == 'bank') {
                if ($request->status == ACTIVE) {
                    $bankIds = [];
                    for ($i = 0; $i < count($request->bank['name']); $i++) {
                        $bank = Bank::updateOrCreate([
                            'id' => $request->bank['id'][$i],
                            'owner_user_id' => $ownerUserId
                        ], [
                            'gateway_id' => $gateway->id,
                            'owner_user_id' => $ownerUserId,
                            'gateway_id' => $gateway->id,
                            'name' => $request->bank['name'][$i],
                            'details' => $request->bank['details'][$i],
                            'status' => $request->bank['status'][$i],

                        ]);
                        array_push($bankIds, $bank->id);
                    }
                    Bank::where('owner_user_id', $ownerUserId)->whereNotIn('id', $bankIds)->delete();
                }
            }elseif ($gateway->slug == 'mpesa') {
                if ($request->status == ACTIVE) {
                    $mpesaAccountIds = [];
                    
                    for ($i = 0; $i < count($request->mpesaAccount['passkey']); $i++) {
                        $accountType = $request->mpesaAccount['account_type'][$i];
                        // Set default values for fields
                        $paybill = null;
                        $accountName = null;
                        $tillNumber = null;
                        // Check account type and set values accordingly
                        if ($accountType === 'PAYBILL') {
                            $paybill = $request->mpesaAccount['paybill_number'][$i];
                            $accountName = $request->mpesaAccount['account_name'][$i];
                        } else if ($accountType === 'TILLNUMBER') {
                            $tillNumber = $request->mpesaAccount['till_number'][$i];
                        }
                        
                        // Update or create MpesaAccount records
                        $mpesaAccount = MpesaAccount::updateOrCreate(
                            [
                                'id' => $request->mpesaAccount['id'][$i],
                                'owner_user_id' => $ownerUserId
                            ],
                            [
                                'gateway_id' => $gateway->id,
                                'owner_user_id' => $ownerUserId,
                                'account_type' => $accountType,
                                'status' => $request->mpesaAccount['status'][$i],
                                'paybill' => $paybill,
                                'account_name' => $accountName,
                                'till_number' => $tillNumber,
                                'passkey' => $request->mpesaAccount['passkey'][$i],
                            ]
                        );
                        
                        // Store the IDs for later use
                        array_push($mpesaAccountIds, $mpesaAccount->id);
                    }
                    
                    // Delete removed MpesaAccount records
                    MpesaAccount::where('owner_user_id', $ownerUserId)->whereNotIn('id', $mpesaAccountIds)->delete();
                }
            } else {
                $gateway->mode = $request->mode;
                $gateway->url = $request->url;
                $gateway->key = $request->key;
                $gateway->secret = $request->secret;
            }
            $gateway->status = $request->status;
            $gateway->owner_user_id = $ownerUserId;
            $gateway->save();

            if (is_null($request->currency)) {
                throw new Exception('Please Add one currency at least');
            }
            $gatewayCurrencyIds = [];
            foreach ($request->currency as $key => $currency) {
                $gatewayCurrency =   GatewayCurrency::updateOrCreate([
                    'id' => $request->currency_id[$key],
                    'owner_user_id' => $ownerUserId
                ], [
                    'gateway_id' => $gateway->id,
                    'owner_user_id' => $ownerUserId,
                    'currency' => $currency,
                    'conversion_rate' => $request->conversion_rate[$key],
                ]);
                array_push($gatewayCurrencyIds, $gatewayCurrency->id);
            }
            GatewayCurrency::where('owner_user_id', $ownerUserId)->whereNotIn('id', $gatewayCurrencyIds)->where('gateway_id', $gateway->id)->delete();

            DB::commit();
            $message = $request->id ? __(UPDATED_SUCCESSFULLY) : __(CREATED_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    public function getCurrencyByGatewayId($id)
    {
        $currencies = GatewayCurrency::where('gateway_id', $id)->get();
        foreach ($currencies as $currency) {
            $currency->symbol =  $currency->symbol;
        }
        return $currencies;
    }
}
