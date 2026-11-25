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
            // Self-heal: guarantee the locked KES = 1.00 row exists so the shilling-only gateway
            // always carries its fixed rate (covers owners who activated M-Pesa before the lock).
            GatewayCurrency::firstOrCreate(
                ['gateway_id' => $id, 'owner_user_id' => auth()->id(), 'currency' => 'KES'],
                ['conversion_rate' => 1],
            );
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
                $bankIds = [];
                if (isset($request->bank['name']) && is_array($request->bank['name'])) {
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
                }
                Bank::where('owner_user_id', $ownerUserId)->whereNotIn('id', $bankIds)->delete();
            }elseif ($gateway->slug == 'mpesa') {
                $mpesaAccountIds = [];
                if (isset($request->mpesaAccount['account_type']) && is_array($request->mpesaAccount['account_type'])) {
                    for ($i = 0; $i < count($request->mpesaAccount['account_type']); $i++) {
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
                            ]
                        );
                        
                        // Store the IDs for later use
                        array_push($mpesaAccountIds, $mpesaAccount->id);
                    }
                }
                
                // Delete removed MpesaAccount records
                MpesaAccount::where('owner_user_id', $ownerUserId)->whereNotIn('id', $mpesaAccountIds)->delete();
            } else {
                $gateway->url = $request->url;
                $gateway->key = $request->key;
                $gateway->secret = $request->secret;
            }
            $gateway->mode = $request->mode;
            $gateway->status = $request->status;
            $gateway->owner_user_id = $ownerUserId;
            $gateway->save();

            // M-Pesa settles in Kenyan Shillings only, so its conversion is LOCKED at KES = 1.00 and
            // enforced server-side — never taken from the request. This can't be an owner setting: a
            // mistyped rate would silently scale every rent/credit amount. We overwrite whatever was
            // posted with the single fixed currency, and the owner-facing form shows it read-only.
            if ($gateway->slug == 'mpesa') {
                $locked = GatewayCurrency::updateOrCreate(
                    ['gateway_id' => $gateway->id, 'owner_user_id' => $ownerUserId, 'currency' => 'KES'],
                    ['conversion_rate' => 1],
                );
                GatewayCurrency::where('owner_user_id', $ownerUserId)
                    ->where('gateway_id', $gateway->id)
                    ->where('id', '!=', $locked->id)
                    ->delete();

                DB::commit();
                $message = $request->id ? __(UPDATED_SUCCESSFULLY) : __(CREATED_SUCCESSFULLY);
                return $this->success([], $message);
            }

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

    /**
     * One-shot inline M-Pesa activation from the dashboard checklist: ensure the owner's M-Pesa
     * gateway exists and is ACTIVE, store the single Till/Paybill as an ACTIVE account, and lock the
     * KES = 1.00 currency. Additive and idempotent — it never deletes the owner's other accounts
     * (unlike the full settings form), so an owner who later adds more in Settings keeps them.
     *
     * @param  array{account_type:string,till_number?:?string,paybill_number?:?string,account_name?:?string}  $data
     */
    public function quickMpesaSetup(array $data)
    {
        DB::beginTransaction();
        try {
            $ownerUserId = auth()->id();

            // Ensure the M-Pesa gateway row exists (owners are seeded one at signup; firstOrCreate
            // self-heals a bare/legacy account) and flip it ACTIVE so rent can actually be collected.
            $gateway = Gateway::firstOrCreate(
                ['owner_user_id' => $ownerUserId, 'slug' => 'mpesa'],
                ['title' => 'Mpesa', 'mode' => GATEWAY_MODE_LIVE, 'image' => 'assets/images/gateway-icon/mpesa.jpg']
            );
            $gateway->status = ACTIVE;
            if (empty($gateway->mode)) {
                $gateway->mode = GATEWAY_MODE_LIVE;
            }
            $gateway->save();

            $accountType = $data['account_type'];
            $paybill     = $accountType === 'PAYBILL' ? ($data['paybill_number'] ?? null) : null;
            $accountName = $accountType === 'PAYBILL' ? ($data['account_name'] ?? null) : null;
            $tillNumber  = $accountType === 'TILLNUMBER' ? ($data['till_number'] ?? null) : null;

            // Keyed on (owner, account_type) so re-running updates the same row instead of piling up
            // duplicates; set ACTIVE so the checklist's "get paid" step ticks immediately.
            MpesaAccount::updateOrCreate(
                ['owner_user_id' => $ownerUserId, 'account_type' => $accountType],
                [
                    'gateway_id'   => $gateway->id,
                    'status'       => ACTIVE,
                    'paybill'      => $paybill,
                    'account_name' => $accountName,
                    'till_number'  => $tillNumber,
                ]
            );

            // M-Pesa moves Kenyan Shillings only — lock the conversion at 1:1, never owner-set.
            GatewayCurrency::updateOrCreate(
                ['gateway_id' => $gateway->id, 'owner_user_id' => $ownerUserId, 'currency' => 'KES'],
                ['conversion_rate' => 1],
            );

            DB::commit();

            return $this->success([], __('You\'re set up to get paid on M-Pesa.'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('quickMpesaSetup failed: ' . $e->getMessage());

            return $this->error([], __('Could not save your M-Pesa details. Please try again.'));
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
