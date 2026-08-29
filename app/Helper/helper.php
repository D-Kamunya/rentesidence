<?php

use App\Models\CorePage;
use App\Models\Currency;
use App\Models\FileManager;
use App\Models\Gateway;
use App\Models\GatewayCurrency;
use App\Models\Package;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceType;
use App\Models\MaintenanceIssue;
use App\Models\TicketTopic;
use App\Models\InvoiceRecurringSetting;
use App\Models\Language;
use App\Models\Maintainer;
use App\Models\Notification;
use App\Models\OwnerPackage;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\SubscriptionOrder;
use App\Models\Setting;
use App\Models\TaxSetting;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Sms\PackageSmsCreditsService;
use App\Models\EmailTemplate;
use App\Services\Payment\Payment;
use App\Services\SmsMail\MailService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\SendPaymentsSuccessEmailJob;
use App\Jobs\SendInvoiceNotificationAndEmailJob;
use App\Jobs\SendSmsJob;
use App\Jobs\SendLoginDetailsJob;

function getOption($option_key, $default = '')
{
    $system_settings = config('settings');
    if ($option_key && isset($system_settings[$option_key])) {
        return $system_settings[$option_key];
    } else {
        return $default;
    }
}

if (!function_exists('setOption')) {
    function setOption(string $key, $value): void
    {
        \App\Models\Setting::updateOrCreate(
            ['option_key' => $key],
            ['option_value' => $value]
        );
 
        // Keep config in sync for the remainder of this request
        config(['settings.' . $key => $value]);
    }
}

if (!function_exists('getSlug')) {
    function getSlug($text)
    {
        if ($text) {
            $data = preg_replace("/[~`{}.'\"\!\@\#\$\%\^\&\*\(\)\_\=\+\/\?\>\<\,\[\]\:\;\|\\\]/", "", $text);
            $slug = preg_replace("/[\/_|+ -]+/", "-", $data);
            return $slug;
        }
        return '';
    }
}

function number_parser($value)
{
    return (float) str_replace(',', '', number_format(($value), 2));
}

function assetUrl($path)
{
    if ($path != '/') {
        return asset('storage/' . $path);
    }
    return asset('assets/images/no-image.jpg');
}

function gatewaySettings()
{
    return '{"paypal":[{"label":"Url","name":"url","is_show":0},{"label":"Client ID","name":"key","is_show":1},{"label":"Secret","name":"secret","is_show":1}],"stripe":[{"label":"Url","name":"url","is_show":0},{"label":"Secret Key","name":"key","is_show":1},{"label":"Secret Key","name":"secret","is_show":0}],"razorpay":[{"label":"Url","name":"url","is_show":0},{"label":"Key","name":"key","is_show":1},{"label":"Secret","name":"secret","is_show":1}],"instamojo":[{"label":"Url","name":"url","is_show":0},{"label":"Api Key","name":"key","is_show":1},{"label":"Auth Token","name":"secret","is_show":1}],"mollie":[{"label":"Url","name":"url","is_show":0},{"label":"Mollie Key","name":"key","is_show":1},{"label":"Secret","name":"secret","is_show":0}],"paystack":[{"label":"Url","name":"url","is_show":0},{"label":"Public Key","name":"key","is_show":1},{"label":"Secret Key","name":"secret","is_show":0}],"mercadopago":[{"label":"Url","name":"url","is_show":0},{"label":"Client ID","name":"key","is_show":1},{"label":"Client Secret","name":"secret","is_show":1}],"sslcommerz":[{"label":"Url","name":"url","is_show":0},{"label":"Store ID","name":"key","is_show":1},{"label":"Store Password","name":"secret","is_show":1}],"flutterwave":[{"label":"Hash","name":"url","is_show":1},{"label":"Public Key","name":"key","is_show":1},{"label":"Client Secret","name":"secret","is_show":1}],"coinbase":[{"label":"Hash","name":"url","is_show":0},{"label":"API Key","name":"key","is_show":1},{"label":"Client Secret","name":"secret","is_show":0}],"bank":[{"label":"Hash","name":"url","is_show":0},{"label":"API Key","name":"key","is_show":0},{"label":"Client Secret","name":"secret","is_show":0}],"cash":[{"label":"Hash","name":"url","is_show":0},{"label":"API Key","name":"key","is_show":0},{"label":"Client Secret","name":"secret","is_show":0}], "mpesa": [{"label": "Url", "name": "url", "is_show": 0},{"label": "API Key", "name": "key", "is_show": 0},{"label": "Public Key", "name": "public_key", "is_show": 0},{"label": "Secret", "name": "secret", "is_show": 0}]}';
}

function getSettingImage($option_key)
{
    try {
        $system_settings = config('settings');
        if ($option_key && isset($system_settings[$option_key])) {
            $fileManager = FileManager::find($system_settings[$option_key]);
            $destinationPath = 'files/Setting' . '/' . $fileManager->file_name;
            if (Storage::disk(config('app.STORAGE_DRIVER'))->exists($destinationPath)) {
                if (config('app.STORAGE_DRIVER') == "s3") {
                    $s3 = Storage::disk(config('app.STORAGE_DRIVER'));
                    return $s3->url($destinationPath);
                }
                return asset('storage/' . $destinationPath);
            }
        } else {
            return asset('assets/images/users/empty-user.jpg');
        }
    } catch (\Exception $e) {
        return asset('assets/images/users/empty-user.jpg');
    }
}

function settingImageStoreUpdate($option_id, $requestFile, $name)
{
    $new_file = FileManager::where('origin_type', 'App\Models\Setting')->where('origin_id', $option_id)->first();

    if ($new_file) {
        $new_file->removeFile();
        $upload = $new_file->updateUpload($new_file->id, 'Setting', $requestFile, $name);
    } else {
        $new_file = new FileManager();
        $upload = $new_file->upload('Setting', $requestFile, $name);
    }

    if ($upload['status']) {
        $upload['file']->origin_id = $option_id;
        $upload['file']->origin_type = "App\Models\Setting";
        $upload['file']->save();
        return $upload['file']->id;
    } else {
        throw new Exception($upload['message']);
    }
}

function getErrorMessage($e, $customMsg = null)
{
    if ($customMsg != null) {
        return $customMsg;
    } 
    return $e->getMessage();
}

function set_local_timezone($timezone)
{
    config(['app.timezone' => @$timezone] ?? 'UTC');
    date_default_timezone_set(@$timezone ?? 'UTC');
}

function getFileUrl($folderName, $fileName)
{
    if ($fileName == '' || $folderName == '') {
        return asset('assets/images/no-image.jpg');
    }
    $destinationPath = $folderName . '/' . $fileName;
    if (Storage::disk(config('app.STORAGE_DRIVER'))->exists($destinationPath)) {
        if (config('app.STORAGE_DRIVER') != "public") {
            $s3 = Storage::disk(config('app.STORAGE_DRIVER'));
            return $s3->url($destinationPath);
        }
        if ($destinationPath != '/') {
            return asset('storage/' . $destinationPath);
        }
    }

    return asset('assets/images/no-image.jpg');
}

function copyFolder($source, $destination) {
    if (is_dir($source)) {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true); // Create the destination directory if it doesn't exist
        }

        $dir = opendir($source);

        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                $src = $source . '/' . $file;
                $dest = $destination . '/' . $file;

                if (is_dir($src)) {
                    // If it's a directory, recursively call the function
                    copyFolder($src, $dest);
                } else {
                    // If it's a file, use copy() to copy it
                    copy($src, $dest);
                }
            }
        }

        closedir($dir);
    } else {
        copy($source, $destination);
    }
}


if (!function_exists('getCityById')) {
    function getCityById($city_id)
    {
        // Return the city name directly since we're using text inputs now
        // The $city_id from your location form is just the city name string
        if (empty($city_id)) return '';
        
        return [
            'id' => $city_id,
            'name' => $city_id,
            'state_id' => '',
        ];
    }
}

if (!function_exists('getStateById')) {
    function getStateById($state_id)
    {
        // Return the state name directly since we're using text inputs now
        if (empty($state_id)) return '';
        
        return [
            'id' => $state_id,
            'name' => $state_id,
            'country_id' => '',
        ];
    }
}

if (!function_exists('getCountryById')) {
    function getCountryById($country_id)
    {
        // Return the country name directly since we're using text inputs now
        if (empty($country_id)) return '';
        
        return [
            'id' => $country_id,
            'name' => $country_id,
            'sortname' => '',
        ];
    }
}

if (!function_exists('csvToArray')) {
    function csvToArray($filename = '', $delimiter = ',', $filterKey = null, $filterValue = null)
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            return [];
        }

        $data = [];
        $handle = fopen($filename, 'r');

        if ($handle !== false) {
            $header = fgetcsv($handle, 1000, $delimiter, '"', '\\');

            while (($row = fgetcsv($handle, 1000, $delimiter, '"', '\\')) !== false) {
                if (count($row) === count($header)) {
                    $rowData = array_combine($header, $row);

                    if ($filterKey !== null && isset($rowData[$filterKey]) && $rowData[$filterKey] == $filterValue) {
                        $data[] = $rowData;
                    }

                    if (count($data) >= 5000) {
                        break;
                    }
                }
            }

            fclose($handle);
        }
        return $data;
    }
}

function convertToReadableSize($size)
{
    $base = log($size) / log(1024);
    $suffix = array("", "KB", "MB", "GB", "TB");
    $f_base = floor($base);
    return round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];
}

function getSystemCurrency()
{
    return Currency::where('current_currency', 'on')
        ->select(['currency_code', 'symbol', 'currency_placement', 'current_currency'])
        ->first();
}

function getCurrencySymbol()
{
    $currency = Currency::where('current_currency', 'on')->first();
    if ($currency) {
        $symbol = $currency->symbol . ' ';
        return $symbol;
    }
    return '';
}

function getCurrencyPlacement()
{
    $currency = Currency::where('current_currency', 'on')->first();
    $placement = 'before';
    if ($currency) {
        $placement = $currency->currency_placement;
        return $placement;
    }

    return $placement;
}

function currencyPrice($price)
{
    if ($price == null) {
        return 0;
    }
    if (getCurrencyPlacement() == 'after')
        return number_format($price, 2) . ' ' . getCurrencySymbol();
    else {
        return getCurrencySymbol() . number_format($price, 2);
    }
}

function setEnvironmentValue($envKey, $envValue)
{
    try {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);
        $str .= "\n"; // In case the searched variable is in the last line without \n
        $keyPosition = strpos($str, "{$envKey}=");
        if ($keyPosition) {
            if (PHP_OS_FAMILY === 'Windows') {
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
            } else {
                $endOfLinePosition = strpos($str, PHP_EOL, $keyPosition);
            }
            $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);
            $envValue = str_replace(chr(92), "\\\\", $envValue);
            $envValue = str_replace('"', '\"', $envValue);
            $newLine = "{$envKey}=\"{$envValue}\"";
            if ($oldLine != $newLine) {
                $str = str_replace($oldLine, $newLine, $str);
                $str = substr($str, 0, -1);
                $fp = fopen($envFile, 'w');
                fwrite($fp, $str);
                fclose($fp);
            }
        } else if (strtoupper($envKey) == $envKey) {
            $envValue = str_replace(chr(92), "\\\\", $envValue);
            $envValue = str_replace('"', '\"', $envValue);
            $newLine = "{$envKey}=\"{$envValue}\"\n";
            $str .= $newLine;
            $str = substr($str, 0, -1);
            $fp = fopen($envFile, 'w');
            fwrite($fp, $str);
            fclose($fp);
        }
        return true;
    } catch (\Exception $e) {
        return false;
    }
}

if (!function_exists('languages')) {
    function languages()
    {
        $data = Language::where('status', 1)->get();
        if ($data) {
            return $data;
        }
        return [];
    }
}
if (!function_exists('languageLocale')) {
    function languageLocale($locale)
    {
        $data = Language::where('code', $locale)->first();
        if ($data) {
            return $data->code;
        }
        return 'en';
    }
}

function selectedLanguage()
{
    $language = Language::where('code', session()->get('local'))->first();
    if (!$language) {
        $language = Language::first();
        if ($language) {
            $ln = $language->code;
            session(['local' => $ln]);
            Carbon::setLocale($ln);
            App::setLocale(session()->get('local'));
        }
    }

    return $language;
}

function appLanguages()
{
    $languages = Language::where('status', 1)->get();
    return $languages?->makeHidden(['created_at', 'updated_at', 'deleted_at']);
}

function propertyTotalRoom($property_id)
{
    if ($property_id) {
        return PropertyUnit::where('property_id', $property_id)->sum('bedroom');
    }
    return 0;
}

function invoiceItemTotalAmount($invoice_id)
{
    return InvoiceItem::where('invoice_id', $invoice_id)->sum('amount');
}

if (!function_exists('getLayout')) {
    function getLayout()
    {
        $output = [
            USER_ROLE_ADMIN => 'admin',
            USER_ROLE_OWNER => 'owner',
            USER_ROLE_TENANT => 'tenant',
            USER_ROLE_MAINTAINER => 'maintainer',
            USER_ROLE_AFFILIATE => 'affiliate',
        ];

        return $output[auth()->user()->role];
    }
}

if (!function_exists('updateEnv')) {
    function updateEnv($values)
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);
        if (count($values) > 0) {
            foreach ($values as $envKey => $envValue) {
                $str .= "\n";
                $keyPosition = strpos($str, "{$envKey}=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

                if (!$keyPosition || !$endOfLinePosition || !$oldLine) {
                    $str .= "{$envKey}=" . json_encode($envValue) . "\n";
                } else {
                    Log::info("{$envKey}=" . json_encode($envValue));
                    $str = str_replace($oldLine, "{$envKey}=" . json_encode($envValue), $str);
                }
            }
        }
        $str = substr($str, 0, -1);
        if (!file_put_contents($envFile, $str)) {
            return false;
        } else {
            return true;
        }
    }
}

function addNotification($title, $body = null, $url = null, $image = null, $user_id = null, $sender_id = null)
{
    $notification = new Notification();
    $notification->title = $title;
    $notification->body = $body;
    $notification->url = $url;
    $notification->image = $image;
    $notification->user_id = $user_id;
    $notification->sender_id = $sender_id;
    $notification->save();
}

function getNotification($user_id)
{
    $fetchNotification = Notification::join('users', 'users.id', '=', 'notifications.sender_id')
        ->leftJoin('file_managers', function ($join) {
            $join->on('file_managers.origin_id', '=', 'notifications.sender_id')
                ->where('file_managers.origin_type', '=', 'App\Models\User');
        })
        ->select('notifications.*', 'users.first_name', 'users.last_name', 'file_managers.file_name', 'file_managers.folder_name')
        ->where(function ($query) use ($user_id) {
            $query->where('notifications.user_id', $user_id);
        })
        ->latest()
        ->get();

    return $fetchNotification;
}

function getNotificationLimit($user_id)
{
    $fetchNotification = Notification::join('users', 'users.id', '=', 'notifications.sender_id')
        ->leftJoin('file_managers', function ($join) {
            $join->on('file_managers.origin_id', '=', 'notifications.sender_id')
                ->where('file_managers.origin_type', '=', 'App\Models\User');
        })
        ->select('notifications.*', 'users.first_name', 'users.last_name', 'file_managers.file_name', 'file_managers.folder_name')
        ->where(function ($query) use ($user_id) {
            $query->where('notifications.user_id', $user_id);
        })
        ->where('notifications.is_seen', DEACTIVATE) 
        ->take(10)
        ->latest()
        ->get();

    return $fetchNotification;
}

if (!function_exists('taxSetting')) {
    function taxSetting($userId = null)
    {
        $userId = isset($userId) ? $userId : auth()->id();
        $tax = TaxSetting::where('owner_user_id', $userId)->first();
        if (is_null($tax)) {
            $tax = TaxSetting::updateOrCreate(['owner_user_id' => auth()->id()], [
                'owner_user_id' => auth()->id(),
            ]);
        }
        return $tax;
    }
}

if (!function_exists('getCustomerCurrentBuildVersion')) {
    function getCustomerCurrentBuildVersion()
    {
        $buildVersion = getOption('build_version');

        if (is_null($buildVersion)) {
            return 1;
        }

        return (int) $buildVersion;
    }
}

if (!function_exists('getCustomerAddonBuildVersion')) {
    function getCustomerAddonBuildVersion($code)
    {
        $buildVersion = getOption($code . '_build_version', 0);
        if (is_null($buildVersion)) {
            return 0;
        }
        return (int) $buildVersion;
    }
}

if (!function_exists('isAddonInstalled')) {
    function isAddonInstalled($code)
    {
        $buildVersion = getOption($code . '_build_version', 0);
        $codeBuildVersion = getAddonCodeBuildVersion($code);
        if (is_null($buildVersion) || $codeBuildVersion == 0) {
            return 0;
        }
        return (int) $buildVersion;
    }
}

if (!function_exists('setCustomerAddonCurrentVersion')) {
    function setCustomerAddonCurrentVersion($code)
    {
        $option = Setting::firstOrCreate(['option_key' => $code . '_current_version']);
        if (getAddonCodeCurrentVersion($code)) {
            $option->option_value = getAddonCodeCurrentVersion($code);
            $option->save();
        }
    }
}

if (!function_exists('setCustomerAddonBuildVersion')) {
    function setCustomerAddonBuildVersion($code, $version)
    {
        $option = Setting::firstOrCreate(['option_key' => $code . '_build_version']);
        $option->option_value = $version;
        $option->save();
    }
}

if (!function_exists('setCustomerBuildVersion')) {
    function setCustomerBuildVersion($version)
    {
        $option = Setting::firstOrCreate(['option_key' => 'build_version']);
        $option->option_value = $version;
        $option->save();
    }
}

if (!function_exists('setCustomerCurrentVersion')) {
    function setCustomerCurrentVersion()
    {
        $option = Setting::firstOrCreate(['option_key' => 'current_version']);
        $option->option_value = config('app.current_version');
        $option->save();
    }
}

if (!function_exists('setOwnerGateway')) {
    function setOwnerGateway($userId)
    {
        $data = [
            ['owner_user_id' => $userId, 'title' => 'Paypal', 'slug' => 'paypal', 'status' => DEACTIVATE, 'mode' => GATEWAY_MODE_SANDBOX, 'url' => '', 'key' => '', 'secret' => '', 'image' => 'assets/images/gateway-icon/paypal.jpg'],
            ['owner_user_id' => $userId, 'title' => 'Stripe', 'slug' => 'stripe', 'status' => DEACTIVATE, 'mode' => GATEWAY_MODE_SANDBOX, 'url' => '', 'key' => '', 'secret' => '', 'image' => 'assets/images/gateway-icon/stripe.jpg'],
            ['owner_user_id' => $userId, 'title' => 'Razorpay', 'slug' => 'razorpay', 'status' => DEACTIVATE, 'mode' => GATEWAY_MODE_SANDBOX, 'url' => '', 'key' => '', 'secret' => '', 'image' => 'assets/images/gateway-icon/razorpay.jpg'],
            ['owner_user_id' => $userId, 'title' => 'Instamojo', 'slug' => 'instamojo', 'status' => DEACTIVATE, 'mode' => GATEWAY_MODE_SANDBOX, 'url' => '', 'key' => '', 'secret' => '', 'image' => 'assets/images/gateway-icon/instamojo.jpg'],
            ['owner_user_id' => $userId, 'title' => 'Mollie', 'slug' => 'mollie', 'status' => DEACTIVATE, 'mode' => GATEWAY_MODE_SANDBOX, 'url' => '', 'key' => '', 'secret' => '', 'image' => 'assets/images/gateway-icon/mollie.jpg'],
            ['owner_user_id' => $userId, 'title' => 'Paystack', 'slug' => 'paystack', 'status' => DEACTIVATE, 'mode' => GATEWAY_MODE_SANDBOX, 'url' => '', 'key' => '', 'secret' => '', 'image' => 'assets/images/gateway-icon/paystack.jpg'],
            ['owner_user_id' => $userId, 'title' => 'Sslcommerz', 'slug' => 'sslcommerz', 'status' => DEACTIVATE, 'mode' => GATEWAY_MODE_SANDBOX, 'url' => '', 'key' => '', 'secret' => '', 'image' => 'assets/images/gateway-icon/sslcommerz.jpg'],
            ['owner_user_id' => $userId, 'title' => 'Flutterwave', 'slug' => 'flutterwave', 'status' => DEACTIVATE, 'mode' => GATEWAY_MODE_SANDBOX, 'url' => '', 'key' => '', 'secret' => '', 'image' => 'assets/images/gateway-icon/flutterwave.jpg'],
            ['owner_user_id' => $userId, 'title' => 'Mercadopago', 'slug' => 'mercadopago', 'status' => DEACTIVATE, 'mode' => GATEWAY_MODE_SANDBOX, 'url' => '', 'key' => '', 'secret' => '', 'image' => 'assets/images/gateway-icon/mercadopago.jpg'],
            ['owner_user_id' => $userId, 'title' => 'Bank', 'slug' => 'bank', 'status' => DEACTIVATE, 'mode' => GATEWAY_MODE_SANDBOX, 'url' => '', 'key' => '', 'secret' => '', 'image' => 'assets/images/gateway-icon/bank.jpg'],
            ['owner_user_id' => $userId, 'title' => 'Cash', 'slug' => 'cash', 'status' => ACTIVE, 'mode' => GATEWAY_MODE_LIVE, 'url' => '', 'key' => '', 'secret' => '', 'image' => 'assets/images/gateway-icon/cash.jpg'],
            ['owner_user_id' => $userId, 'title' => 'Mpesa', 'slug' => 'mpesa', 'status' => DEACTIVATE, 'mode' => GATEWAY_MODE_SANDBOX, 'url' => '', 'key' => '', 'secret' => '', 'image' => 'assets/images/gateway-icon/mpesa.jpg'],
        ];
        Gateway::insert($data);
        
        // Get the Cash gateway (must use same slug as inserted)
        $cashGateway = Gateway::where('owner_user_id', $userId)
            ->where('slug', 'cash')
            ->first();

        // Create default currency record for Cash gateway
        if ($cashGateway) {
            GatewayCurrency::create([
                'gateway_id' => $cashGateway->id,
                'owner_user_id' => $userId,
                'currency' => 'KES',
                'conversion_rate' => 1,
            ]);
        }
    }
}

if (!function_exists('setUserPackage')) {
    function setUserPackage($userId, $package, $duration, $quantity = 1, $orderId = NULL)
    {
        OwnerPackage::where(['user_id' => $userId])->whereIn('status', [ACTIVE])->update(['status' => DEACTIVATE]);

        OwnerPackage::create([
            'user_id'           => $userId,
            'package_id'        => $package->id,
            'package_type'      => $package->type ?? PACKAGE_TYPE_UNIT,
            'pricing_model'     => $package->pricing_model,
            'quantity'          => $package->max_unit,
            'name'              => $package->name,
            'max_maintainer'    => $package->max_maintainer,
            'max_property'      => $package->max_property,
            'max_unit'          => $package->max_unit,
            'max_tenant'        => $package->max_tenant,
            'max_invoice'       => $package->max_invoice,
            'max_auto_invoice'  => $package->max_auto_invoice,
            'ticket_support'    => $package->ticket_support,
            'notice_support'    => $package->notice_support,
            'monthly_price'     => $package->monthly_price,
            'yearly_price'      => $package->yearly_price,
            'per_monthly_price' => $package->per_monthly_price,
            'per_yearly_price'  => $package->per_yearly_price,
            'order_id'          => $orderId,
            'is_trail'          => $package->is_trail,
            'start_date'        => now(),
            'end_date'          => Carbon::now()->addDays($duration),
            'status'            => ACTIVE,
        ]);

        // ââ Grant monthly SMS credits for this package ââââââââââââââââ
        // Wrapped in try/catch so a credit failure never breaks activation
        try {
            PackageSmsCreditsService::grantOnActivation($userId, $package->id);
        } catch (\Exception $e) {
            Log::error(
                "setUserPackage: SMS credit grant failed for user_id={$userId}, package_id={$package->id} â " . $e->getMessage()
            );
        }
        // âââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââ

        // ââ Centresidence: keep module billing in step with the pricing mode ââ
        // Activating a package can change the owner's pricing model, so re-tag
        // their modules' billing_model to match (mode is authoritative). Guarded
        // and try/catch so it can never break activation on any install.
        try {
            app(\App\Centresidence\Services\PaymentModeService::class)->syncModulesToOwnerMode((int) $userId);
        } catch (\Throwable $e) {
            Log::error("setUserPackage: module billing sync failed for user_id={$userId} â " . $e->getMessage());
        }
        // âââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââ

        // ââ MERGE (B2 stage 4): settle bundled module-infra on activation ââ
        // Central chokepoint for EVERY paid-order confirmation path (M-Pesa callback,
        // gateway confirmation, â¦). When the paying order bundled the owner's infra
        // (infra_amount > 0), settle those invoices in the same success. Idempotent
        // (markPaid no-ops if nothing is outstanding) and guarded so a settlement
        // hiccup never breaks the plan activation the owner already paid for.
        if ($orderId) {
            try {
                $paidOrder = \App\Models\SubscriptionOrder::find($orderId);
                if ($paidOrder && (float) ($paidOrder->infra_amount ?? 0) > 0) {
                    app(\App\Centresidence\Services\InfraBillPaymentService::class)
                        ->markPaid((int) $userId, $paidOrder->payment_id ?? null);
                }
            } catch (\Throwable $e) {
                Log::error("setUserPackage: infra settlement failed for user_id={$userId}, order={$orderId} â " . $e->getMessage());
            }
        }
        // âââââââââââââââââââââââââââââââââââââââââââââââââââââââââââââ
    }
}

if (!function_exists('setOwnerInvoiceType')) {
    function setOwnerInvoiceType($userId)
    {   
        $invoiceType= new InvoiceType;
        $invoiceType->name='Rent';
        $invoiceType->tax='0.00';
        $invoiceType->owner_user_id=$userId;
        $invoiceType->status=ACTIVE;
        if (\Illuminate\Support\Facades\Schema::hasColumn('invoice_types', 'is_default')) {
            $invoiceType->is_default = true;
        }
        $invoiceType->save();
    }
}

if (!function_exists('setOwnerDefaultMaintenanceIssue')) {
    function setOwnerDefaultMaintenanceIssue($userId)
    {   
        $defaultIssues = ['Leakage', 'Blockage', 'Other'];
        $markDefault = \Illuminate\Support\Facades\Schema::hasColumn('maintenance_issues', 'is_default');

        foreach ($defaultIssues as $issueName) {
            $maintenanceIssue = new MaintenanceIssue;
            $maintenanceIssue->name = $issueName;
            $maintenanceIssue->owner_user_id = $userId;
            $maintenanceIssue->status = ACTIVE;
            if ($markDefault) {
                $maintenanceIssue->is_default = true;
            }
            $maintenanceIssue->save();
        }
    }
}

if (!function_exists('setOwnerDefaultTicketTopics')) {
    function setOwnerDefaultTicketTopics($userId)
    {   
        $defaultTopics = [
            'Maintenance Request',
            'Rent Payment Issue',
            'Billing Inquiry',
            'Lease Agreement Question',
            'Move-in / Move-out Notice',
            'Utility Issue (Water, Electricity, etc.)',
            'Security Concern',
            'Noise Complaint',
            'Neighbor Dispute',
            'Parking Issue',
            'Cleaning / Garbage Collection',
            'Internet / Connectivity Issue',
            'Access / Key / Lock Problem',
            'Property Damage Report',
            'General Inquiry',
            'Other'
        ];

        foreach ($defaultTopics as $topicName) {
            $ticketTopic = new TicketTopic;
            $ticketTopic->name = $topicName;
            $ticketTopic->owner_user_id = $userId;
            $ticketTopic->status = ACTIVE;
            $ticketTopic->is_default = true;
            $ticketTopic->save();
        }
    }
}

if (!function_exists('setOwnerDefaultDocumentConfig')) {
    /**
     * Seed a plug-and-play set of document requests for a new owner, so a tenant can
     * upload documents WITHOUT the owner having to configure anything first (same class
     * of empty-state block we fixed for ticket topics). tenant_id = null â applies to
     * every tenant of this owner. Owners can edit/remove these in Settings â Document
     * Config. Idempotent-safe: only call at owner creation / backfill for owners with none.
     */
    function setOwnerDefaultDocumentConfig($userId)
    {
        $defaults = [
            ['name' => 'National ID',              'details' => 'A clear copy of the front and back of your national ID card.', 'is_both' => ACTIVE],
            ['name' => 'Good Conduct Certificate', 'details' => 'A valid police clearance (certificate of good conduct).',       'is_both' => DEACTIVATE],
            ['name' => 'KRA PIN Certificate',      'details' => 'Your KRA PIN certificate for tax identification.',              'is_both' => DEACTIVATE],
            ['name' => 'Passport Photo',           'details' => 'A recent passport-size photograph.',                            'is_both' => DEACTIVATE],
        ];

        // Guarded so seeding still works if the schema migration hasn't run yet (defaults
        // just aren't marked protected until it does) â keeps live setup zero-touch.
        $markDefault = \Illuminate\Support\Facades\Schema::hasColumn('kyc_configs', 'is_default');

        foreach ($defaults as $doc) {
            $config = new \App\Models\KycConfig;
            $config->name = $doc['name'];
            $config->details = $doc['details'];
            $config->owner_user_id = $userId;
            $config->tenant_id = null; // applies to all of this owner's tenants
            $config->is_both = $doc['is_both'];
            $config->status = ACTIVE;
            if ($markDefault) {
                $config->is_default = true;
            }
            $config->save();
        }
    }
}

if (!function_exists('setOwnerDefaultAgreementTemplate')) {
    /**
     * Plug-and-play default agreement template — a general Kenyan residential tenancy body
     * with {{placeholders}} autofilled per tenant at send time. Seeded so an owner can send
     * agreements without crafting anything; they can edit it or upload their own, and keep
     * reusing it until they change it. Marked is_default (undeletable) when the column exists.
     */
    function setOwnerDefaultAgreementTemplate($userId)
    {
        $body = <<<'HTML'
<h2 style="text-align:center;margin:0 0 4px;">RESIDENTIAL TENANCY AGREEMENT</h2>
<p style="text-align:center;color:#555;margin:0 0 18px;">Made on {{today}}</p>

<p>This Tenancy Agreement is made between:</p>
<p><strong>Landlord:</strong> {{owner_name}} ({{owner_contact}}) ("the Landlord"), and<br>
<strong>Tenant:</strong> {{tenant_name}} ({{tenant_contact}}) ("the Tenant").</p>

<p><strong>1. Premises.</strong> The Landlord lets to the Tenant the premises known as
<strong>{{unit_name}}, {{property_name}}</strong> ("the Premises"), for residential use only.</p>

<p><strong>2. Term.</strong> The tenancy begins on {{lease_start}} and continues on a monthly basis
until terminated in accordance with this Agreement.</p>

<p><strong>3. Rent.</strong> The Tenant shall pay rent of <strong>{{rent_amount}}</strong> per month,
in advance, on or before the due date each month, through the channels provided by the Landlord.</p>

<p><strong>4. Deposit.</strong> The Tenant has paid a deposit of {{deposit_amount}}, refundable at the
end of the tenancy less any lawful deductions for unpaid rent, utilities or damage beyond fair wear and tear.</p>

<p><strong>5. Use &amp; Care.</strong> The Tenant shall keep the Premises clean and in good condition,
shall not sublet without written consent, and shall not use the Premises for any unlawful purpose.</p>

<p><strong>6. Utilities.</strong> The Tenant is responsible for utilities consumed at the Premises
(water, electricity and any metered services) unless otherwise agreed in writing.</p>

<p><strong>7. Repairs.</strong> The Tenant shall report defects promptly. The Landlord shall keep the
structure and installations in reasonable repair.</p>

<p><strong>8. Termination.</strong> Either party may terminate this tenancy by giving one (1) month's
written notice. The Landlord may terminate for breach, including non-payment of rent.</p>

<p><strong>9. Entire Agreement.</strong> This document, once signed electronically by the Tenant, forms
the whole agreement between the parties for the Premises.</p>

<p style="margin-top:24px;">Signed by the Tenant, {{tenant_name}}, having read and agreed to the terms above.</p>
HTML;

        $markDefault = \Illuminate\Support\Facades\Schema::hasColumn('agreement_templates', 'is_default');

        $template = new \App\Models\AgreementTemplate;
        $template->owner_user_id = $userId;
        $template->name = 'Residential Tenancy Agreement';
        $template->source = \App\Models\AgreementTemplate::SOURCE_TEMPLATE;
        $template->body = $body;
        $template->is_active = true;
        if ($markDefault) {
            $template->is_default = true;
        }
        $template->save();
    }
}

if (!function_exists('ensureOwnerDefaults')) {
    /**
     * Self-healing plug-and-play seeding, shared across features. Seeds an owner's
     * defaults the FIRST time the feature is read (owner opens the settings page, or a
     * tenant opens the feature) â so nothing needs to be run on the live shared host.
     * Uses withTrashed() (when the model soft-deletes) so an owner who INTENTIONALLY
     * cleared all of theirs is never re-seeded. Guarded so it can never break the page.
     *
     * @param  int|null   $ownerUserId
     * @param  string     $modelClass   e.g. App\Models\TicketTopic::class
     * @param  callable   $seeder       e.g. 'setOwnerDefaultTicketTopics'
     */
    function ensureOwnerDefaults($ownerUserId, string $modelClass, callable $seeder): void
    {
        if (! $ownerUserId) {
            return;
        }

        try {
            $usesSoftDeletes = in_array('Illuminate\\Database\\Eloquent\\SoftDeletes', class_uses_recursive($modelClass));
            $query = $usesSoftDeletes ? $modelClass::withTrashed() : $modelClass::query();

            if (! $query->where('owner_user_id', $ownerUserId)->exists()) {
                $seeder($ownerUserId);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("ensureOwnerDefaults: seed failed for {$modelClass} owner {$ownerUserId} â " . $e->getMessage());
        }
    }
}

if (!function_exists('effectiveOwnerPrintDetails')) {
    /**
     * Plug-and-play print/company details for owner invoices. When an owner hasn't customised
     * their print details, default the invoice header to their OWN profile (name + phone)
     * rather than the platform — otherwise invoices go out branded as the app (Centresidence)
     * with a blank contact number. Address falls back to the platform location (owners have no
     * dedicated address field). Owners can still override all three in Profile → Print Details.
     *
     * @param  object|null $profile carries first_name / last_name / contact_number
     * @return array{0:string,1:string,2:string} [print_name, print_address, print_contact]
     */
    function effectiveOwnerPrintDetails($printName, $printAddress, $printContact, $profile = null): array
    {
        $printName    = trim((string) $printName);
        $printAddress = trim((string) $printAddress);
        $printContact = trim((string) $printContact);

        if ($printName === '') {
            $ownerName = trim((string) ($profile->first_name ?? '') . ' ' . (string) ($profile->last_name ?? ''));
            $printName = $ownerName !== '' ? $ownerName : (string) getOption('app_name');
        }
        if ($printContact === '') {
            $printContact = (string) ($profile->contact_number ?? '') ?: (string) getOption('app_contact_number');
        }
        if ($printAddress === '') {
            $printAddress = (string) getOption('app_location');
        }

        return [$printName, $printAddress, $printContact];
    }
}

if (!function_exists('handleSubscriptionPaymentConfirmation')) {
    function handleSubscriptionPaymentConfirmation($order, $payerId = null, $gateway_slug, $paymentCheck = null)
    {
        try {
            $gateway = Gateway::find($order->gateway_id);
            DB::beginTransaction();
            if ($order->gateway_id == $gateway->id && $gateway->slug == MERCADOPAGO) {
                $order->payment_id = $payment_id;
                $order->save();
            }

            $payment_id = $order->payment_id;

            $gatewayBasePayment = new Payment($gateway->slug, ['currency' => $order->gateway_currency, 'type' => 'subscription']);
            $payment_data = $gatewayBasePayment->paymentConfirmation($payment_id, $payerId);
            
            if ($payment_data['success']) {
                if ($payment_data['data']['payment_status'] == 'success') {
                    $order->payment_status = ORDER_PAYMENT_STATUS_PAID;
                    $order->transaction_id = str_replace('-', '', uuid_create());
                    $order->save();

                    $package = Package::find($order->package_id);
                    $duration = 0;
                    if ($order->duration_type == PACKAGE_DURATION_TYPE_MONTHLY) {
                        $duration = 30;
                    } elseif ($order->duration_type == PACKAGE_DURATION_TYPE_YEARLY) {
                        $duration = 365;
                    }

                    setUserPackage(auth()->id(), $package, $duration, $order->quantity, $order->id);

                    DB::commit();

                    $invoiceUrl = route('owner.subscription.index');
                    $title = __("Subscription activated");
                    $body = __("Your subscription payment was received — your plan is now active.");
                    $adminUser = User::where('role', USER_ROLE_ADMIN)->first();
                    addNotification($title, $body, $invoiceUrl, null,$order->user_id,$adminUser->id);

                    if (getOption('send_email_status', 0) == ACTIVE) {
                        $emails = [$order->user->email];
                        $subject = __('Subscription Payment Successful!');
                        $title = __('Congratulations!');
                        $message = __('You have successfully made the payment');
                        $ownerUserId =$order->user_id;
                        $method = $gateway->slug;
                        $status = 'Paid';
                        $amount = $order->amount;
                        $paymentType = "subscription";

                        SendPaymentsSuccessEmailJob::dispatch(
                            $emails, $subject, $message, $title, $method, 
                            $status, $amount,$paymentType, $order, $duration
                        );
                    }
                    
                    if ($gateway_slug == 'mpesa') {
                        return redirect()->route('owner.subscription.index')->with('success', __('Mpesa Payment Successful!'));
                    }

                    return redirect()->route('owner.subscription.index')->with('success', __('Payment Successful!'));
                }
            } else {
                if ($gateway_slug == 'mpesa') {
                    // if ($paymentCheck!==null){
                    //     $paymentCheck->increment('check_count');
                    //     $paymentCheck->last_check_at=now();
                    //     $paymentCheck->save();
                    //     DB::commit();
                    // }
                    if (($payment_data['data']['error']  ?? null)=== MPESA_REQUEST_CANCELLED) {
                        $order->payment_status = ORDER_PAYMENT_STATUS_CANCELLED;
                        $order->transaction_id = str_replace('-', '', uuid_create());
                        $order->save();

                        DB::commit();
                    }
                    return redirect()->route('owner.subscription.index')->with('error', __($payment_data['data']['error']));
                }
                // if ($paymentCheck!==null){
                //     $paymentCheck->increment('check_count');
                //     $paymentCheck->last_check_at=now();
                //     $paymentCheck->save();
                //     DB::commit();
                // }
                return redirect()->route('owner.subscription.index')->with('error', __('Payment Failed!'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('owner.subscription.index')->with('error', __('Payment Failed!'));
        }
    }
}


if (!function_exists('handleProductPaymentConfirmation')) {
    function handleProductPaymentConfirmation($order, $payerId = null, $gateway_slug, $paymentCheck = null)
    {
        try {
            $gateway = Gateway::find($order->gateway_id);
            DB::beginTransaction();

            if ($order->gateway_id == $gateway->id && $gateway->slug == MERCADOPAGO) {
                $order->payment_id = $payment_id;
                $order->save();
            }

            $payment_id = $order->payment_id;

            $ownerNumber = $order->gateway->owner->contact_number ?? null;

            $gatewayBasePayment = new Payment($gateway->slug, [
                'currency' => $order->gateway_currency,
                'type'     => 'ProductOrder',
            ]);

            $payment_data = $gatewayBasePayment->paymentConfirmation($payment_id, $payerId);

            if ($payment_data['success'] && $payment_data['data']['payment_status'] === 'success') {

                // ââ Guard: only update + process if not already paid âââââââââ
                if ($order->payment_status !== ORDER_PAYMENT_STATUS_PAID) {
                    $order->payment_status = ORDER_PAYMENT_STATUS_PAID;
                    $order->transaction_id = str_replace('-', '', uuid_create());
                    $order->save();
                }

                // ââ Guard: only process commission if not already recorded âââ
                $alreadyProcessed = \App\Models\WalletTransaction::where('product_order_id', $order->id)->exists();

                if (!$alreadyProcessed) {
                    try {
                        $order->load('orderItems.product');
                        $commissionService = new \App\Services\CommissionService();
                        $commissionService->processOrderCommission($order);
                    } catch (\Exception $commissionException) {
                        \Illuminate\Support\Facades\Log::error('Commission processing failed in handleProductPaymentConfirmation', [
                            'order_id' => $order->id,
                            'error'    => $commissionException->getMessage(),
                            'trace'    => $commissionException->getTraceAsString(),
                        ]);
                    }
                }

                DB::commit();

                // ââ Notifications ââââââââââââââââââââââââââââââââââââââââââââ
                $invoiceUrl  = route('tenant.order.index');
                $title       = __('Payment received');
                $body        = __('Your product order payment was received successfully.');
                $ownerUserID = $gateway->owner_user_id;
                addNotification($title, $body, $invoiceUrl, null, $order->user_id, $ownerUserID);

                if (getOption('send_email_status', 0) == ACTIVE) {
                    $emails       = [$order->user->email];
                    $subject      = __('Product Payment Successful!');
                    $title        = __('Congratulations!');
                    $message      = __('You have successfully made the product order payment');
                    $tenantUserId = $order->user_id;
                    $method       = $gateway->slug;
                    $status       = 'Paid';
                    $amount       = $order->amount;
                    $paymentType  = 'ProductOrder';

                    SendPaymentsSuccessEmailJob::dispatch(
                        $emails, $subject, $message, $title, $method,
                        $status, $amount, $paymentType, $order
                    );
                }

                // Only send SMS if owner number is available
                if ($ownerNumber) {
                    $message = __('New order :id from :app. Please dispatch.', ['id' => $order->order_id, 'app' => getOption('app_name') ?: 'Centresidence']);
                    SendSmsJob::dispatch([$ownerNumber], $message, $order->user_id);
                }

                if ($gateway_slug === 'mpesa') {
                    return redirect()->route('tenant.product.order.receipt', $order->id)
                        ->with('success', __('Mpesa Payment Successful!'));
                }

                return redirect()->route('tenant.product.order.receipt', $order->id)
                    ->with('success', __('Payment Successful!'));

            } else {

                // ââ Payment not successful âââââââââââââââââââââââââââââââââââ
                if ($gateway_slug === 'mpesa') {
                    $errorMessage = $payment_data['data']['error'] ?? null;

                    if ($errorMessage === MPESA_REQUEST_CANCELLED) {
                        if ($order->payment_status === ORDER_PAYMENT_STATUS_PENDING) {
                            $order->payment_status = PRODUCT_ORDER_STATUS_CANCELLED;
                            $order->transaction_id = str_replace('-', '', uuid_create());
                            $order->save();
                            DB::commit();
                        } else {
                            DB::rollBack();
                        }
                    } else {
                        DB::rollBack();
                    }

                    return redirect()->route('tenant.product.index')
                        ->with('error', __($errorMessage ?? 'Payment Failed!'));
                }

                DB::rollBack();
                return redirect()->route('tenant.product.index')
                    ->with('error', __('Payment Failed!'));
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('handleProductPaymentConfirmation failed', [
                'order_id' => $order->id ?? null,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);
            return redirect()->route('tenant.product.index')
                ->with('error', __('Payment Failed!'));
        }
    }
}


if (!function_exists('handlePaymentConfirmation')) {
    function handlePaymentConfirmation(
        $order,
        $payment_token,
        $payerId,
        $gateway_slug,
        $paymentCheck = null,
        bool $isRentTransaction = false
    ) {
        $redirect = auth()->check()
            ? route('tenant.invoice.index')
            : route('instant.invoice.pay', ['token' => $payment_token]);

        try {
            $gateway          = Gateway::find($order->gateway_id);
            $formattedGateway = ucfirst(strtolower($gateway_slug));

            DB::beginTransaction();

            // ââ MercadoPago guard ââââââââââââââââââââââââââââââââââââââââââââ
            if ($order->gateway_id == $gateway->id && $gateway->slug == MERCADOPAGO) {
                $payment_id        = $order->payment_id;
                $order->payment_id = $payment_id;
                $order->save();
            }

            $payment_id = $order->payment_id;

            $gatewayBasePayment = new Payment(
                $gateway->slug,
                ['currency' => $order->gateway_currency, 'type' => 'RentPayment']
            );

            $payment_data = $gatewayBasePayment->paymentConfirmation($payment_id, $payerId);

            if ($payment_data['success'] && ($payment_data['data']['payment_status'] ?? null) === 'success') {

                // ââ Guard: only mark paid if not already paid ââââââââââââââââ
                // Prevents double-processing when both Pusher callback and
                // timeout redirect reach this function for the same order.
                if ($order->payment_status !== INVOICE_STATUS_PAID) {
                    $order->payment_status = INVOICE_STATUS_PAID;
                    $order->transaction_id = str_replace('-', '', uuid_create());
                    $order->save();
                }

                // ââ Mark invoice paid ââââââââââââââââââââââââââââââââââââââââ
                // Use order->invoice_id (reliable) NOT Invoice::where('order_id')
                // because invoices.order_id may not be set yet at this point.
                $invoice = $order->invoice ?? \App\Models\Invoice::find($order->invoice_id);

                if ($invoice) {
                    $invoice->status   = INVOICE_STATUS_PAID;
                    $invoice->order_id = $order->id;
                    $invoice->save();
                } else {
                    \Illuminate\Support\Facades\Log::warning('handlePaymentConfirmation: invoice not found', [
                        'order_id'   => $order->id,
                        'invoice_id' => $order->invoice_id,
                    ]);
                }

                // ââ Rent commission ââââââââââââââââââââââââââââââââââââââââââ
                // Uses WalletTransaction.invoice_order_id as the idempotency key.
                // Safe to call from multiple code paths â will not double-credit.
                if ($isRentTransaction) {
                    $alreadyProcessed = \App\Models\WalletTransaction::where('invoice_order_id', $order->id)->exists();

                    if (!$alreadyProcessed) {
                        try {
                            $commissionService = new \App\Services\CommissionService();
                            $commissionService->processRentCommission($order);
                        } catch (\Exception $commissionException) {
                            // Commission failure must NOT roll back the payment.
                            // The tenant's rent is paid â commission is secondary.
                            // Log for manual review.
                            \Illuminate\Support\Facades\Log::error(
                                'Rent commission failed in handlePaymentConfirmation',
                                [
                                    'order_id' => $order->id,
                                    'error'    => $commissionException->getMessage(),
                                    'trace'    => $commissionException->getTraceAsString(),
                                ]
                            );
                        }
                    }
                }

                // ââ Centresidence facility repayment / settlement ââââââââââââ
                // Layered on top of the owner-wallet credit above: deducts active
                // facility repayments + overdue commission from this rent in strict
                // priority order. Idempotent per order; a no-op for properties with
                // no Centresidence obligations; never rolls back the rent payment.
                if (
                    $isRentTransaction && $invoice && config('centresidence.enabled', true)
                    && ($rentPortion = min((float) $invoice->rentPortion(), (float) $order->transaction_amount)) > 0
                    && app(\App\Centresidence\Services\PaymentModeService::class)
                        ->isTransactionMode((int) $invoice->owner_user_id)
                ) {
                    try {
                        app(\App\Centresidence\Services\RentSettlementService::class)->handleRentPayment(
                            (int) $invoice->property_id,
                            (int) $invoice->owner_user_id,
                            \App\Centresidence\Support\Money::fromDecimal((string) $rentPortion),
                            ['rent_transaction_id' => (int) $order->id]
                        );
                    } catch (\Throwable $settlementException) {
                        \Illuminate\Support\Facades\Log::error(
                            'Centresidence rent settlement failed in handlePaymentConfirmation',
                            ['order_id' => $order->id, 'error' => $settlementException->getMessage()]
                        );
                    }
                }

                DB::commit();

                // ââ Invoice paid notification ââââââââââââââââââââââââââââââââ
                // Bell notification only (mirrors the marketplace order flow). The
                // tenant-facing email is the rent receipt dispatched below via
                // SendPaymentsSuccessEmailJob, so we do NOT also send the generic
                // invoice email here to avoid double-emailing on one payment.
                if ($invoice) {
                    addNotification(
                        __('Rent payment successful'),
                        $invoice->invoice_no . ' ' . __('paid successfully'),
                        route('tenant.invoice.receipt', $invoice->id),
                        null,
                        $invoice->tenant->user->id,
                        $invoice->owner_user_id
                    );
                }

                // ââ Owner SMS notification âââââââââââââââââââââââââââââââââââ
                try {
                    $ownerNumber = $order->gateway->owner->contact_number ?? null;
                    if ($ownerNumber) {
                        $message = __('New rent payment received for invoice :no from :app.', [
                            'no'  => $invoice->invoice_no ?? $order->invoice_id,
                            'app' => getOption('app_name') ?: 'Centresidence',
                        ]);
                        SendSmsJob::dispatch([$ownerNumber], $message, $order->user_id);
                    }
                } catch (\Exception $notifyException) {
                    \Illuminate\Support\Facades\Log::warning(
                        'Notification failed after rent payment confirmation',
                        ['order_id' => $order->id, 'error' => $notifyException->getMessage()]
                    );
                }

                // ââ Payment success email ââââââââââââââââââââââââââââââââââââ
                if (getOption('send_email_status', 0) == ACTIVE) {
                    try {
                        SendPaymentsSuccessEmailJob::dispatch(
                            [$order->user->email],
                            __('Invoice Payment Successful!'),
                            __('You have successfully made your payment.'),
                            __('Congratulations!'),
                            $gateway->slug,
                            'Paid',
                            $order->amount,
                            'RentPayment',
                            $order
                        );
                    } catch (\Exception $emailException) {
                        \Illuminate\Support\Facades\Log::warning(
                            'Email failed after rent payment confirmation',
                            ['order_id' => $order->id, 'error' => $emailException->getMessage()]
                        );
                    }
                }

                // Authenticated tenants land on the rent receipt (mirrors the marketplace
                // order receipt); guests paying via instant link keep their token page.
                $successRedirect = (auth()->check() && $invoice)
                    ? route('tenant.invoice.receipt', $invoice->id)
                    : $redirect;

                return redirect($successRedirect)
                    ->with('success', __($formattedGateway . ' Payment Successful. Rent Paid!'));

            } else {

                // ââ Payment not successful âââââââââââââââââââââââââââââââââââ
                if ($gateway_slug === 'mpesa') {
                    $errorMessage = $payment_data['data']['error'] ?? null;

                    if ($errorMessage === MPESA_REQUEST_CANCELLED) {
                        // Only cancel if still pending â never overwrite a paid status
                        if ($order->payment_status === INVOICE_STATUS_PENDING) {
                            $order->payment_status = ORDER_PAYMENT_STATUS_CANCELLED;
                            $order->transaction_id = str_replace('-', '', uuid_create());
                            $order->save();
                            DB::commit();
                        } else {
                            DB::rollBack();
                        }
                    } else {
                        DB::rollBack();
                    }

                    return redirect($redirect)
                        ->with('error', __($errorMessage ?? 'Payment Failed! Rent Not Paid'));
                }

                DB::rollBack();
                return redirect($redirect)->with('error', __('Payment Failed!'));
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('handlePaymentConfirmation failed', [
                'order_id' => $order->id ?? null,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);
            return redirect($redirect)->with('error', __('Payment Failed!'));
        }
    }
}


if (!function_exists('corePages')) {
    function corePages($take = null)
    {
        return CorePage::where('status', ACTIVE)->take($take ?? 4)->get();
    }
}

if (!function_exists('getAddonCodeCurrentVersion')) {
    function getAddonCodeCurrentVersion($appCode)
    {
        Artisan::call("config:clear");
        if ($appCode == 'PROTYSAAS') {
            return config('addon.PROTYSAAS.current_version', 0);
        } elseif ($appCode == 'PROTYSMS') {
            return config('smsmail.PROTYSMS.current_version', 0);
        } elseif ($appCode == 'PROTYAGREEMENT') {
            return config('agreement.PROTYAGREEMENT.current_version', 0);
        } elseif ($appCode == 'PROTYTENANCY') {
            return config('tenancy.PROTYTENANCY.current_version', 0);
        } elseif ($appCode == 'PROTYLISTING') {
            return config('flutter.PROTYLISTING.current_version', 0);
        }
    }
}

if (!function_exists('getAddonCodeBuildVersion')) {
    function getAddonCodeBuildVersion($appCode)
    {
        Artisan::call("config:clear");
        if ($appCode == 'PROTYSAAS') {
            return config('addon.PROTYSAAS.build_version', 0);
        } elseif ($appCode == 'PROTYSMS') {
            return config('smsmail.PROTYSMS.build_version', 0);
        } elseif ($appCode == 'PROTYAGREEMENT') {
            return config('agreement.PROTYAGREEMENT.build_version', 0);
        } elseif ($appCode == 'PROTYTENANCY') {
            return config('tenancy.PROTYTENANCY.build_version', 0);
        } elseif ($appCode == 'PROTYLISTING') {
            return config('listing.PROTYLISTING.build_version', 0);
        }
    }
}

function getEmailTemplate($body, $customizedFieldsArray = [])
{
    if ($body) {
        $body = $body;
        if ($customizedFieldsArray) {
            foreach (emailTemplateFields() as $key => $item) {
                if (isset($customizedFieldsArray[$key])) {
                    $body = str_replace($key, $customizedFieldsArray[$key], $body);
                }
            }
        }
        return $body;
    }
    return '';
}

if (!function_exists('get_domain_name')) {
    function get_domain_name($url)
    {
        $parseUrl = parse_url(trim($url));
        if (isset($parseUrl['host'])) {
            $host = $parseUrl['host'];
        } else {
            $path = explode('/', $parseUrl['path']);
            $host = $path[0];
        }
        return trim($host);
    }
}

if (!function_exists('reviewStar')) {
    function reviewStar($star)
    {
        $html = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i > $star) {
                $html .= '<span class="iconify" data-icon="ic:baseline-star"></span>';
            } else {
                $html .= '<span class="iconify star-filled" data-icon="ic:baseline-star"></span>';
            }
        }
        return $html;
    }
}

if (!function_exists('ownerCurrentPackage')) {
    function ownerCurrentPackage($userId)
    {
        return OwnerPackage::query()
            ->where('status', ACTIVE)
            ->where('user_id', $userId)
            ->whereDate('end_date', '>=', now()->toDateTimeString())
            ->first();
    }
}

if (!function_exists('checkExpiredOwnerPackage')) {
    function checkExpiredOwnerPackage($userId)
    {   
        return OwnerPackage::latest()
            ->where('status', ACTIVE)
            ->where('user_id', $userId)
            ->whereDate('end_date', '<=', now()->toDateTimeString())
            ->first();
    }
}

if (!function_exists('getOwnerLimit')) {
    function getOwnerLimit($type, $userId = NULL)
    {
        if (isAddonInstalled('PROTYSAAS') < 1) {
            return true;
        }
        // Plan gating is UNIT-ONLY by product decision — property- and tenant-count limiting are
        // disabled (their max_* columns are dormant), so those channels are always unlimited.
        // Units (RULES_UNIT) is the single gate. This neutralises every property/tenant limit
        // check in one place, so the choice is honoured in code, not just greyed out in the admin.
        if ($type === RULES_PROPERTY || $type === RULES_TENANT) {
            return PHP_INT_MAX;
        }
        $userId = is_null($userId) ? auth()->id() : $userId;
        $ownerPlan = OwnerPackage::where('status', ACTIVE)->where('user_id', $userId)->whereDate('end_date', '>=', now()->toDateTimeString())->first();

        // custom package limit check
        if (!is_null($ownerPlan)) {
            if (in_array($ownerPlan->package_type, [PACKAGE_TYPE_PROPERTY, PACKAGE_TYPE_UNIT, PACKAGE_TYPE_TENANT])) {
                $quantity = $ownerPlan->quantity;
                if ($type == RULES_INVOICE && $ownerPlan->max_invoice != -1) {
                    $limit = $ownerPlan->max_invoice;
                    $used = Invoice::where('owner_user_id', $userId)->count();
                    $remain = $limit - $used;
                    $remain = $remain < 0 ? 0 : $remain;
                    $remain;
                } else if ($type == RULES_AUTO_INVOICE && $ownerPlan->max_auto_invoice != -1) {
                    $limit = $ownerPlan->max_auto_invoice;
                    $used = InvoiceRecurringSetting::where('owner_user_id', $userId)->count();
                    $remain = $limit - $used;
                    $remain = $remain < 0 ? 0 : $remain;
                    $remain;
                } else if ($type == RULES_MAINTAINER && $ownerPlan->max_maintainer != -1) {
                    $limit = $ownerPlan->max_maintainer;
                    $used = Maintainer::where('owner_user_id', $userId)->count();
                    $remain = $limit - $used;
                    $remain = $remain < 0 ? 0 : $remain;
                    $remain;
                } else {
                    $remain = PHP_INT_MAX;
                }
                if ($ownerPlan->package_type == PACKAGE_TYPE_PROPERTY) {
                    if ($type != RULES_PROPERTY) {
                        return $remain;
                    }
                    $used = Property::where('owner_user_id', $userId)->count();
                    $remain = $quantity - $used;
                    $remain = $remain < 0 ? 0 : $remain;
                    return $remain;
                } else if ($ownerPlan->package_type == PACKAGE_TYPE_UNIT) {
                    if ($type != RULES_UNIT) {
                        return $remain;
                    }
                    $propertiesIds = Property::query()
                        ->where('owner_user_id', $userId)
                        ->select('id')
                        ->pluck('id')
                        ->toArray();
                    $used = PropertyUnit::whereIn('property_id', $propertiesIds ?? [])->count();
                    $remain = $quantity - $used;
                    $remain = $remain < 0 ? 0 : $remain;
                    return $remain;
                } else if ($ownerPlan->package_type == PACKAGE_TYPE_TENANT) {
                    if ($type != RULES_TENANT) {
                        return $remain;
                    }
                    $used = Tenant::where('owner_user_id', $userId)->count();
                    $remain = $quantity - $used;
                    $remain = $remain < 0 ? 0 : $remain;
                    return $remain;
                }
            }
        }

        // old package limit check method
        if ($type == RULES_PROPERTY) {
            if (is_null($ownerPlan)) {
                return 0;
            }
            $limit = $ownerPlan->max_property;
            $used = Property::where('owner_user_id', $userId)->count();
            $remain = $limit - $used;
            $remain = $remain < 0 ? 0 : $remain;
            return $remain;
        } elseif ($type == RULES_MAINTAINER) {
            if (is_null($ownerPlan)) {
                return 0;
            }
            $limit = $ownerPlan->max_maintainer;
            $used = Maintainer::where('owner_user_id', $userId)->count();
            $remain = $limit - $used;
            $remain = $remain < 0 ? 0 : $remain;
            return $remain;
        } elseif ($type == RULES_TENANT) {
            if (is_null($ownerPlan)) {
                return 0;
            }
            $limit = $ownerPlan->max_tenant;
            $used = Tenant::where('owner_user_id', $userId)->count();
            $remain = $limit - $used;
            $remain = $remain < 0 ? 0 : $remain;
            return $remain;
        } elseif ($type == RULES_INVOICE) {
            if (is_null($ownerPlan)) {
                return 0;
            }
            $limit = $ownerPlan->max_invoice;
            $used = Invoice::where('owner_user_id', $userId)->count();
            $remain = $limit - $used;
            $remain = $remain < 0 ? 0 : $remain;
            return $remain;
        } elseif ($type == RULES_AUTO_INVOICE) {
            if (is_null($ownerPlan)) {
                return 0;
            }
            $limit = $ownerPlan->max_auto_invoice;
            $used = InvoiceRecurringSetting::where('owner_user_id', $userId)->count();
            $remain = $limit - $used;
            $remain = $remain < 0 ? 0 : $remain;
            return $remain;
        }
    }
}
if (!function_exists('getExistingMaintainer')) {
    function getExistingMaintainers($userId = null)
    {
        $userId = is_null($userId) ? auth()->id() : $userId;
        $ownerPackage = ownerCurrentPackage($userId);

        if (is_null($ownerPackage)) {
            return 0;
        } else {
            $totalCount = User::query()
                ->where('owner_user_id', $userId)
                ->where('role', USER_ROLE_MAINTAINER)
                ->count();
            return $totalCount;
        }
    }
}

if (!function_exists('getExistingProperty')) {
    function getExistingProperty($userId = null)
    {
        $userId = is_null($userId) ? auth()->id() : $userId;
        $ownerPackage = ownerCurrentPackage($userId);

        if (is_null($ownerPackage)) {
            return 0;
        } else {
            $totalCount = Property::query()
                ->where('owner_user_id', $userId)
                ->count();
            return $totalCount;
        }
    }
}

if (!function_exists('getExistingUnit')) {
    function getExistingUnit($userId = null)
    {
        $userId = is_null($userId) ? auth()->id() : $userId;
        $ownerPackage = ownerCurrentPackage($userId);

        if (is_null($ownerPackage)) {
            return 0;
        } else {
            $propertyIds = Property::query()
                ->where('owner_user_id', $userId)
                ->select('id')
                ->pluck('id')
                ->toArray();

            $totalCount = PropertyUnit::query()
                ->whereIn('property_id', $propertyIds)
                ->count();
            return $totalCount;
        }
    }
}

if (!function_exists('getExistingTenant')) {
    function getExistingTenant($userId = null)
    {
        $userId = is_null($userId) ? auth()->id() : $userId;
        $ownerPackage = ownerCurrentPackage($userId);

        if (is_null($ownerPackage)) {
            return 0;
        } else {
            $totalCount = User::query()
                ->where('owner_user_id', $userId)
                ->where('role', USER_ROLE_TENANT)
                ->count();
            return $totalCount;
        }
    }
}

if (!function_exists('getExistingInvoice')) {
    function getExistingInvoice($userId = null)
    {
        $userId = is_null($userId) ? auth()->id() : $userId;
        $ownerPackage = ownerCurrentPackage($userId);

        if (is_null($ownerPackage)) {
            return 0;
        } else {
            $totalCount = Invoice::query()
                ->where('owner_user_id', $userId)
                ->count();
            return $totalCount;
        }
    }
}

if (!function_exists('getExistingAutoInvoice')) {
    function getExistingAutoInvoice($userId = null)
    {
        $userId = is_null($userId) ? auth()->id() : $userId;
        $ownerPackage = ownerCurrentPackage($userId);

        if (is_null($ownerPackage)) {
            return 0;
        } else {
            $totalCount = InvoiceRecurringSetting::query()
                ->where('owner_user_id', $userId)
                ->count();
            return $totalCount;
        }
    }
}

if (!function_exists('sendLoginDetails')) {
    function sendLoginDetails($user,$password)
    {
        try {
            SendLoginDetailsJob::dispatch($user, $password);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

if (!function_exists('mpesaPhoneFromReference')) {
    function mpesaPhoneFromReference($reference)
    {
        $last9 = substr(preg_replace('/\D/', '', $reference), -9);
        return '0' . $last9;
    }
}

if (!function_exists('mpesaStkConfirmed')) {
    /**
     * Server-side confirmation that an M-Pesa STK payment actually succeeded — queries
     * Safaricom (stkquery) on the CheckoutRequestID stored at push time. The browser
     * `stk_success` flag is appended CLIENT-SIDE (see the pay JS) and must NOT authorise
     * any money effect (mark-paid / commission / credit) on its own — otherwise a payer
     * could self-settle for free by hitting the verify URL with stk_success=1 without
     * paying. Gate those effects on THIS instead. Fail-closed: any error / inconclusive
     * result returns false — the authenticated M-Pesa server callback remains the primary
     * settlement path, so a false here only defers (the user refreshes), it never loses a
     * real payment.
     */
    function mpesaStkConfirmed(?string $checkoutRequestId): bool
    {
        if (empty($checkoutRequestId)) {
            return false;
        }
        try {
            $confirm = (new \App\Services\Payment\Payment('mpesa', ['type' => 'RentPayment']))
                ->paymentConfirmation($checkoutRequestId);

            return ($confirm['success'] ?? false)
                && (($confirm['data']['payment_status'] ?? null) === 'success');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('mpesaStkConfirmed failed: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('maskPhone')) {
    /**
     * Mask a phone number for display on a BEARER (unauthenticated, forwardable)
     * page — keeps the leading area and the last 3 digits, hides the middle:
     * "0712345678" -> "0712 xxx 678". The payer already knows their own number;
     * masking only costs someone the link was forwarded/leaked to.
     */
    function maskPhone($number)
    {
        $digits = preg_replace('/\D/', '', (string) $number);
        if (strlen($digits) < 7) {
            return $number; // too short to mask meaningfully
        }
        $head = substr($digits, 0, 4);
        $tail = substr($digits, -3);
        return $head . ' xxx ' . $tail;
    }
}

if (!function_exists('invoicePayTokenExpiry')) {
    /**
     * Lifetime for an invoice's instant-pay (bearer) link. Derived from the invoice
     * DUE DATE + a grace window so the link stays live through the whole payable
     * period — a flat "N days from generation" can lapse before the invoice is even
     * due when the owner's due-day is far out, stranding on-time payers who click the
     * SMS/email link. Floored at 7 days from now so it never regresses below the old
     * behaviour, and so re-issuing an OVERDUE invoice's link (due date already past)
     * still yields a fresh live window.
     */
    function invoicePayTokenExpiry($dueDate = null, int $graceDays = 14): \Carbon\Carbon
    {
        $floor = now()->addDays(7)->endOfDay();
        if (empty($dueDate)) {
            return $floor;
        }
        $candidate = \Carbon\Carbon::parse($dueDate)->addDays($graceDays)->endOfDay();
        return $candidate->greaterThan($floor) ? $candidate : $floor;
    }
}
if (!function_exists('getYoutubeId')) {

    function getYoutubeId($url)
    {
        preg_match('/(youtu\.be\/|v=)([^&]+)/', $url, $matches);
        return $matches[2] ?? null;
    }

}