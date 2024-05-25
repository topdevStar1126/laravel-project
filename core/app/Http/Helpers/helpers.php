<?php

use App\Constants\Status;
use App\Lib\GoogleAuthenticator;
use App\Models\Cart;
use App\Models\Extension;
use App\Models\Frontend;
use App\Models\GeneralSetting;
use Carbon\Carbon;
use App\Lib\Captcha;
use App\Lib\ClientInfo;
use App\Lib\CurlRequest;
use App\Lib\FileManager;
use App\Models\ApiKey;
use App\Models\OrderItem;
use App\Notify\Notify;
use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

function systemDetails()
{
    $system['name']          = 'codeplus';
    $system['version']       = '1.1';
    $system['build_version'] = '4.4.10';
    return $system;
}

function slug($string)
{
    return Illuminate\Support\Str::slug($string);
}

function verificationCode($length)
{
    if ($length == 0)
        return 0;
    $min = pow(10, $length - 1);
    $max = (int) ($min - 1) . '9';
    return random_int($min, $max);
}

function getNumber($length = 8)
{
    $characters = '1234567890';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


function activeTemplate($asset = false)
{
    $general = gs();
    $template = session('template') ?? $general->active_template;
    if ($asset)
        return 'assets/templates/' . $template . '/';
    return 'templates.' . $template . '.';
}

function activeTemplateName()
{
    $general = gs();
    $template = session('template') ?? $general->active_template;
    return $template;
}

function siteLogo($type = null)
{
    $name = $type ? "/logo_$type.png" : '/logo.png';
    return getImage(getFilePath('logoIcon') . $name) . "?v=v1";
}
function siteFavicon()
{
    return getImage(getFilePath('logoIcon') . '/favicon.png') . "?v=v2";
}

function loadReCaptcha()
{
    return Captcha::reCaptcha();
}

function loadCustomCaptcha($width = '100%', $height = 46, $bgColor = '#003')
{
    return Captcha::customCaptcha($width, $height, $bgColor);
}

function verifyCaptcha()
{
    return Captcha::verify();
}

function loadExtension($key)
{
    $extension = Extension::where('act', $key)->where('status', Status::ENABLE)->first();
    return $extension ? $extension->generateScript() : '';
}

function getTrx($length = 12)
{
    $characters = 'ABCDEFGHJKMNOPQRSTUVWXYZ123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getAmount($amount, $length = 2)
{
    $amount = round($amount ?? 0, $length);
    return $amount + 0;
}

function showAmount($amount, $decimal = 2, $separate = true, $exceptZeros = false)
{
    $separator = '';
    if ($separate) {
        $separator = ',';
    }
    $printAmount = number_format($amount, $decimal, '.', $separator);
    if ($exceptZeros) {
        $exp = explode('.', $printAmount);
        if ($exp[1] * 1 == 0) {
            $printAmount = $exp[0];
        } else {
            $printAmount = rtrim($printAmount, '0');
        }
    }
    return $printAmount;
}


function removeElement($array, $value)
{
    return array_diff($array, (is_array($value) ? $value : array($value)));
}

function cryptoQR($wallet)
{
    return "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=$wallet&choe=UTF-8";
}


function keyToTitle($text)
{
    return ucfirst(preg_replace("/[^A-Za-z0-9 ]/", ' ', $text));
}


function titleToKey($text)
{
    return strtolower(str_replace(' ', '_', $text));
}


function strLimit($title = null, $length = 10)
{
    return Str::limit($title, $length);
}


function getIpInfo()
{
    $ipInfo = ClientInfo::ipInfo();
    return $ipInfo;
}


function osBrowser()
{
    $osBrowser = ClientInfo::osBrowser();
    return $osBrowser;
}


function getTemplates()
{
    $param['purchasecode'] = env("PURCHASECODE");
    $param['website'] = @$_SERVER['HTTP_HOST'] . @$_SERVER['REQUEST_URI'] . ' - ' . env("APP_URL");
    $url = 'https://license.viserlab.com/updates/templates/' . systemDetails()['name'];
    $response = CurlRequest::curlPostContent($url, $param);
    if ($response) {
        return $response;
    } else {
        return null;
    }
}


function getPageSections($arr = false)
{
    $jsonUrl = resource_path('views/') . str_replace('.', '/', activeTemplate()) . 'sections.json';
    $sections = json_decode(file_get_contents($jsonUrl));
    if ($arr) {
        $sections = json_decode(file_get_contents($jsonUrl), true);
        ksort($sections);
    }
    return $sections;
}


function getImage($image, $size = null)
{
    $clean = '';

    if (file_exists($image) && is_file($image)) {
        return asset($image) . $clean;
    }

    if ($size) {
        return route('placeholder.image', $size);
    }

    return asset('assets/images/default.png');
}


function notify($user, $templateName, $shortCodes = null, $sendVia = null, $createLog = true)
{
    $general = gs();
    $globalShortCodes = [
        'site_name' => $general->site_name,
        'site_currency' => $general->cur_text,
        'currency_symbol' => $general->cur_sym,
    ];

    if (gettype($user) == 'array') {
        $user = (object) $user;
    }

    $shortCodes = array_merge($shortCodes ?? [], $globalShortCodes);

    $notify = new Notify($sendVia);
    $notify->templateName = $templateName;
    $notify->shortCodes = $shortCodes;
    $notify->user = $user;
    $notify->createLog = $createLog;
    $notify->userColumn = isset($user->id) ? $user->getForeignKey() : 'user_id';
    $notify->send();
}

function getPaginate($paginate = 20)
{
    return $paginate;
}

function paginateLinks($data)
{
    return $data->appends(request()->all())->links();
}


function menuActive($routeName, $type = null, $param = null)
{
    if ($type == 3)
        $class = 'side-menu--open';
    elseif ($type == 2)
        $class = 'sidebar-submenu__open';
    else
        $class = 'active';

    if (is_array($routeName)) {
        foreach ($routeName as $key => $value) {
            if (request()->routeIs($value))
                return $class;
        }
    } elseif (request()->routeIs($routeName)) {
        if ($param) {
            $routeParam = array_values(@request()->route()->parameters ?? []);
            if (strtolower(@$routeParam[0]) == strtolower($param))
                return $class;
            else
                return;
        }
        return $class;
    }
}


function fileUploader($file, $location, $size = null, $old = null, $thumb = null)
{
    $fileManager = new FileManager($file);
    $fileManager->path = $location;
    $fileManager->size = $size;
    $fileManager->old = $old;
    $fileManager->thumb = $thumb;
    $fileManager->upload();
    return $fileManager->filename;
}

function fileManager()
{
    return new FileManager();
}

function getFilePath($key)
{
    return fileManager()->$key()->path;
}

function getFileSize($key)
{
    return fileManager()->$key()->size;
}

function getFileThumb($key)
{
    return fileManager()->$key()->thumbnail;
}

function getFileExt($key)
{
    return fileManager()->$key()->extensions;
}

function diffForHumans($date)
{
    $lang = session()->get('lang');
    Carbon::setlocale($lang);
    return Carbon::parse($date)->diffForHumans();
}


function showDateTime($date, $format = 'Y-m-d h:i A')
{
    $lang = session()->get('lang');
    Carbon::setlocale($lang);
    return Carbon::parse($date)->translatedFormat($format);
}


function getContent($dataKeys, $singleQuery = false, $limit = null, $orderById = false)
{

    $templateName = activeTemplateName();
    if ($singleQuery) {
        $content = Frontend::where('tempname', $templateName)->where('data_keys', $dataKeys)->orderBy('id', 'desc')->first();
    } else {
        $article = Frontend::where('tempname', $templateName);
        $article->when($limit != null, function ($q) use ($limit) {
            return $q->limit($limit);
        });
        if ($orderById) {
            $content = $article->where('data_keys', $dataKeys)->orderBy('id')->get();
        } else {
            $content = $article->where('data_keys', $dataKeys)->orderBy('id', 'desc')->get();
        }
    }
    return $content;
}


function gatewayRedirectUrl($type = false)
{
    if ($type) {
        return 'user.deposit.history';
    } else {
        return 'user.deposit.index';
    }
}

function verifyG2fa($user, $code, $secret = null)
{
    $authenticator = new GoogleAuthenticator();
    if (!$secret) {
        $secret = $user->tsc;
    }
    $oneCode = $authenticator->getCode($secret);
    $userCode = $code;
    if ($oneCode == $userCode) {
        $user->tv = 1;
        $user->save();
        return true;
    } else {
        return false;
    }
}


function urlPath($routeName, $routeParam = null)
{
    if ($routeParam == null) {
        $url = route($routeName);
    } else {
        $url = route($routeName, $routeParam);
    }
    $basePath = route('home');
    $path = str_replace($basePath, '', $url);
    return $path;
}


function showMobileNumber($number)
{
    $length = strlen($number);
    return substr_replace($number, '***', 2, $length - 4);
}

function showEmailAddress($email)
{
    $endPosition = strpos($email, '@') - 1;
    return substr_replace($email, '***', 1, $endPosition);
}


function getRealIP()
{
    $ip = $_SERVER["REMOTE_ADDR"];
    //Deep detect ip
    if (filter_var(@$_SERVER['HTTP_FORWARDED'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_FORWARDED'];
    }
    if (filter_var(@$_SERVER['HTTP_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_FORWARDED_FOR'];
    }
    if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    if (filter_var(@$_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }
    if (filter_var(@$_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    if ($ip == '::1') {
        $ip = '127.0.0.1';
    }

    return $ip;
}


function appendQuery($key, $value = null)
{
    if (is_array($key)) {
        $queries = $key;

        $existingQueries = request()->query();
        $queries = array_merge($existingQueries, $queries);

        return request()->fullUrlWithQuery($queries);
    }

    return request()->fullUrlWithQuery([$key => $value]);
}

function dateSort($a, $b)
{
    return strtotime($a) - strtotime($b);
}

function dateSorting($arr)
{
    usort($arr, "dateSort");
    return $arr;
}

function gs($key = null)
{
    $general = Cache::get('GeneralSetting');
    if (!$general) {
        $general = GeneralSetting::first();
        Cache::put('GeneralSetting', $general);
    }
    if ($key)
        return @$general->$key;
    return $general;
}

function isImage($string)
{
    $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
    $fileExtension = pathinfo($string, PATHINFO_EXTENSION);
    if (in_array($fileExtension, $allowedExtensions)) {
        return true;
    } else {
        return false;
    }
}

function isHtml($string)
{
    if (preg_match('/<.*?>/', $string)) {
        return true;
    } else {
        return false;
    }
}

function ratingStar($rating = 0)
{
    $ratingStar = '';

    for ($i = 0; $i < floor($rating); $i++) {
        $ratingStar .= '<li class="rating-list__item"><i class="fa fa-star"></i></li>';
    }
    if (0 < $rating - floor($rating) && $rating - floor($rating) <= 0.25) {
        $ratingStar .= '<li class="rating-list__item"><i class="far fa-star"></i></li>';
    } elseif (0.25 < $rating - floor($rating) && $rating - floor($rating) <= 0.75) {
        $ratingStar .= '<li class="rating-list__item"><i class="fas fa-star-half-alt"></i></li>';
    } elseif (0.75 <= $rating - floor($rating) && $rating - floor($rating) < 1) {
        $ratingStar .= '<li class="rating-list__item"><i class="fa fa-star"></i></li>';
    }

    for ($i = 0; $i < 5 - ceil($rating); $i++) {
        $ratingStar .= '<li class="rating-list__item"><i class="far fa-star"></i></li>';
    }

    return $ratingStar;
}

function productInCart($productId)
{
    return Cart::where('product_id', $productId)->where(function ($cart) {
        $userId = auth()->id();

        if ($userId) {
            $cart->where('user_id', $userId);
        } else {
            $sessionIds = collect(session('cart'))->pluck('session_id')->toArray();
            return $cart->whereIn('session_id', $sessionIds)->count() > 0;
        }
    })->exists();
}


function displayRating($averageRating)
{
    $averageRating = $averageRating > 5 ? 5 : $averageRating;
    $precisionThreshold1 = 0.25;
    $precisionThreshold2 = 0.75;
    $starCount = 5;
    $precision = round($averageRating, 2) - intval($averageRating);
    $output = '';
    if ($precision > $precisionThreshold1) {
        $averageRating = intval($averageRating) + 0.5;
    }
    if ($precision > $precisionThreshold2) {
        $averageRating = intval($averageRating) + 1;
    }
    for ($i = 0; $i < intval($averageRating); $i++) {
        $output .= '<i class="rating-list__item la la-star"></i>';
    }
    if ($averageRating - intval($averageRating) == 0.5) {
        $i++;
        $output .= '<i class="rating-list__item las la-star-half-alt"></i>';
    }
    for ($k = 0; $k < $starCount - $i; $k++) {
        $output .= '<i class="rating-list__item lar la-star"></i>';
    }
    return $output;
}

function cartCount()
{
    if (gs('maintenance_mode') == Status::ENABLE)
        return 0;

    if (auth()->user()) {
        return Cart::where('user_id', auth()->id())->count();
    }

    return Cart::where('session_id', session()->getId())->count();
}

function getPurchaseCode()
{
    $randomPart1 = rand(100000, 999999);
    $randomPart2 = rand(10, 99);
    $randomPart3 = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 6);
    $randomPart4 = rand(100000, 999999);

    while (true) {
        $uniqueCode = $randomPart1 . '-' . $randomPart2 . '-' . $randomPart3 . '-' . $randomPart4;
        if (!OrderItem::where('purchase_code', $uniqueCode)->exists()) break;
    }
    return $uniqueCode;
}

function generateApiKey()
{
    while (0 == 0) {
        $token = base64_encode(random_bytes(32));
        if (!ApiKey::where('key', $token)->exists()) break;
    }
    return $token;
}

function isFavorite($productId)
{
    if (!auth()->user())
        return false;

    $user = auth()->user();
    return $user->favoriteProducts->contains($productId);
}

function productSlug($title)
{
    $slug  = slug($title);
    $slug .= '-' . getNumber(5);
    return $slug;
}

function updateCartByUsers()
{
    $sessionIds = collect(session('cart'))->pluck('session_id')->toArray();
    Cart::whereIn('session_id', $sessionIds)->update(['user_id' => auth()->id()]);
    session()->forget('cart');
}

function getCartAmount($cartItems)
{
    if (gettype($cartItems) == 'array') $cartItems = collect($cartItems);

    $amount = $cartItems->sum(function ($cartItem) {
        return $cartItem->price + $cartItem->buyer_fee + $cartItem->extended_amount;
    });

    return $amount;
}

function productFilePath($product, $colName)
{
    return '/' . @$product->slug . '/' . @$product->$colName;
}

function imageUrl($directory = null, $image = null, $size = null)
{
    if (!$image) {
        return getImage('/', $size);
    }

    $general = gs();

    if ($general->storage_type == 2) {
        return $general->ftp->host_domain . '/images/' . $image;
    } elseif ($general->storage_type == 3 || $general->storage_type == 4 || $general->storage_type == 5) {
        return getS3FileUri($image);
    } else {
        $image = $directory ? $directory . '/' . $image : $image;
        return getImage($image, $size);
    }
}

function getS3FileUri($fileName, $type = "image")
{
    $general = gs();
    $servers = [3 => "wasabi", 4 => "digital_ocean", 5 => "vultr"];
    $server  = $servers[$general->storage_type];

    $accessKey  = @$general?->{$server}?->key;
    $secretKey  = @$general?->{$server}?->secret;
    $bucketName = @$general?->{$server}?->bucket;

    $objectKey = $type == 'image' ? 'images/' . $fileName : 'files/' . $fileName;
    $endpoint = $general->{$server}->endpoint;

    $credentials = new Credentials($accessKey, $secretKey);
    $s3Client = new S3Client([
        'version'     => 'latest',
        'region'      => @$general?->{$server}?->region ?? '',
        'endpoint'    => $endpoint,
        'credentials' => $credentials
    ]);

    $command = $s3Client->getCommand('GetObject', [
        'Bucket' => $bucketName,
        'Key' => $objectKey,
    ]);

    return (string) $s3Client->createPresignedRequest($command, '+1 hour')->getUri();
}



function fileUrl($fileName)
{
    $general = gs();
    if ($general->storage_type == 2) {
        return @$general->ftp->host_domain . '/files/' . $fileName;
    } elseif ($general->storage_type == 3 || $general->storage_type == 4 || $general->storage_type == 5) {
        return getS3FileUri($fileName, 'file');
    } else {
        return getFilePath('stockFile') . '/' . $fileName;
    }
}