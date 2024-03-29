<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=gb18030">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
   
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"> </script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

</head>
<style>
    body {
        height: 100%;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        display: block;
        position: relative;
        background: url(<?php echo $_POST['urlImgBackground'] ?>) no-repeat center center fixed;
    }

    body::after {
        content: "";
        -webkit-background-size: cover;
        -moz-background-size: cover;
        background-size: cover;
        -o-background-size: cover;
        /*opacity: 0.5;*/
        filter: brightness(60%);
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        position: absolute;
        z-index: -1;   
    }

    .parent_form-open-value {
        width: 100%;
        display: flex;
        height: 80%;
        margin: 0;
    }

    .form-open-value {
        width: 100%;
        max-width: 330px;
        padding: 15px;
        margin: auto;
        background-color: white;
        border-radius: 1rem;
        opacity: 0.9;        
        margin-left: 20%;
    }
    .form-open-value .form-title {
        font-weight: 150;
        font-size: 2.5rem;
    }
    .form-open-value .form-instructions {
        margin:10px;
        font-weight: 100;
        font-size: 1rem;
    }
    .form-open-value .message-confirmation {
        color: #007bff;
    }
    .form-open-value .message-confirmation-name {
        margin-top: 25px;
        color: #007bff;
        font-weight: 700;
    }
    .form-open-value .message-confirmation-lastname {
        color: #007bff;
        font-weight: 700;
    }
    .form-open-value .message-confirmation-email {
        color: #007bff;
        font-size: 17px;
        font-weight: 400;
        margin-bottom: 25px;
    }
    .form-open-value .message-confirmation-amount {
        color: #007bff;
        font-weight: 900;
    }
    .form-open-value .style-payment-button {
        margin-top: 25px;
    }
    .form-open-value .style-payment-gateway-logo {
        width: 35%;
    }
    .hanabiImage img {
        width: 70%;
        height: 70%;
    }

    /* For mobile phones: */
    @media only screen and (max-width: 576px) {
        .parent_form-open-value {
            width: 100%;
            display: flex;
            height: 80%;
            margin: 0;
        }
        .form-open-value {
            width: 100%;
            max-width: 330px;
            padding: 15px;
            margin: auto;
            background-color: white;
            border-radius: 1rem;
            opacity: 0.9;
        }
    }
</style>


<!-- Server side -->
<?PHP
session_start();
// muestra el formato internacional para la configuración regional en_US
$_SESSION["showFormWithOpenPayAttributes"] = false;

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
    require_once ("./frontend.phtml");
}

if ($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['btnFormOpenValue']))
{
    $currencySelected = trim($_POST['currency']);
    $URL = trim($_POST['url']);
    $password = '';
    // credenciales de cuenta dashboard en SOLES
    if ($currencySelected == "PEN")
    {
        setlocale(LC_MONETARY, 'es_PE');
        $username = 'sk_a8911895d40e493587ba2d066a6f7624';
        $URL .= '/v1/mdtoknue6hyxbmr2qk1o/checkouts';
    }
    // credenciales de cuenta dashboard en DOLARES
    else if ($currencySelected == "USD")
    {
        setlocale(LC_MONETARY, 'en_US');
        $username = 'sk_62b7449c813c40269848ec4387246209';
        $URL .= '/v1/mnktdknv8yev3rjuibqj/checkouts';
    }

    $_SESSION["showFormWithOpenPayAttributes"] = true;
    $customer = array(
        'name' => trim($_POST['txtName']) ,
        'last_name' => trim($_POST['txtLastname']) ,
        'phone_number' => "",
        'email' => trim($_POST['txtEmail'])
    );

    $chargeRequest = array(
        'amount' => trim($_POST['txtOpenAmount']) ,
        'description' => trim($_POST['description']) ,
        'order_id' => time() ,
        'currency' => $currencySelected,
        'redirect_url' => $_POST['approvedResponseUrl'],
        'expiration_date' => "2024-08-31 12:50",
        'send_email' => false,
        'customer' => $customer,
    );

    $fields_string = json_encode($chargeRequest);
    
    try{
        $response = createOpenPayPaymenyLink($URL, $fields_string, $username, $password);
        $paymentLink  = json_decode($response)->checkout_link;
        ConfimacionDatosFormularioMontoAbierto($paymentLink);
    } catch (Exception $e) {
        var_dump("Exception",$e);
        require_once ("./pago-exitoso.phtml");
    }
}

class OpenPay_Model
{
    // public $apikey;
    public $name;
    public $lastname;
    public $email;
    public $phoneNumber;
    public $urlRedirectLinkPayment;
    public $merchantId;
    public $accountId;
    public $description;
    // public $referenceCode;
    public $amount;
    public $tax;
    public $taxReturnBase;
    public $currency;
    public $signature;
    public $test;
    public $buyerEmail;
    public $responseUrl;
    public $confirmationUrl;
    public $approvedResponseUrl;
    public $declinedResponseUrl;
    public $pendingResponseUrl;

    // custom messages and urls
    public $messageWebTabTitle;
    public $messagePaymentTitle;
    public $messagePaymentButton;
    public $messagePaymentInstructions;
    public $messagePaymentCopyRight;
    public $urlImgLogo;
    public $urlImgBackground;
    public $messagePaymentConfirmation;
    public $urlPaymentGatewayLogo;
}

function ConfimacionDatosFormularioMontoAbierto($paymentLink)
{

    if (empty($_POST['txtOpenAmount']))
    {
        echo "caught post empty";
        $this->HandleError("txtOpenAmount is empty!");
        return false;
    }

    $GLOBALS['$openPay'] = new OpenPay_Model();

    $openPay->name = trim($_POST['txtName']);
    $openPay->lastname = trim($_POST['txtLastname']);
    $openPay->email = trim($_POST['txtEmail']);
    $openPay->phoneNumber = trim($_POST['txtPhoneNumber']);
    // $openPay->apikey = "eUUdi6J4qPr5qFQ75l1ES5Cvd3";
    $openPay->urlRedirectLinkPayment = $paymentLink;
    $openPay->merchantId = trim($_POST['merchantId']);
    $openPay->accountId = trim($_POST['accountId']);
    $openPay->description = trim($_POST['description']);
    // $openPay->referenceCode = time();
    $openPay->amount = trim($_POST['txtOpenAmount']);
    $openPay->tax = trim($_POST['tax']);
    $openPay->taxReturnBase = trim($_POST['taxReturnBase']);
    $openPay->currency = trim($_POST['currency']);
    $openPay->test = trim($_POST['test']);
    $openPay->buyerEmail = trim($_POST['buyerEmail']);
    $openPay->responseUrl = trim($_POST['responseUrl']);
    $openPay->confirmationUrl = trim($_POST['confirmationUrl']);
    $openPay->approvedResponseUrl = trim($_POST['approvedResponseUrl']);
    $openPay->declinedResponseUrl = trim($_POST['declinedResponseUrl']);
    $openPay->pendingResponseUrl = trim($_POST['pendingResponseUrl']);
    $openPay->messagePaymentButton = trim($_POST['messagePaymentButton']);
    $openPay->messagePaymentTitle = trim($_POST['messagePaymentTitle']);
    $openPay->messagePaymentConfirmation = trim($_POST['messagePaymentConfirmation']);
    $openPay->urlPaymentGatewayLogo = trim($_POST['urlPaymentGatewayLogo']);

    $openPay->messageWebTabTitle = trim($_POST['messageWebTabTitle']);
    $openPay->messagePaymentInstructions = trim($_POST['messagePaymentInstructions']);
    $openPay->messagePaymentCopyRight = trim($_POST['messagePaymentCopyRight']);
    $openPay->urlImgLogo = trim($_POST['urlImgLogo']);
    $openPay->urlImgBackground = trim($_POST['urlImgBackground']);

    RenderConfirmationOpenValueFrom($openPay);

    return true;
}

function RenderConfirmationOpenValueFrom($openPay)
{
    echo '<!DOCTYPE html>';
    echo '<html>';

    echo '<head>';
    echo '<title>' . $openPay->messageWebTabTitle . '</title>';
    echo '</head>';

    echo '<body class="text-center">';

    echo '<nav class="navbar navbar-light bg-dark">';
    echo '<a class="navbar-brand" href="#">';
    echo '<img src="' . $openPay->urlImgLogo . '" width="auto" height="63" alt="">';
    echo '</a>';
    echo '</nav>';

    echo '<div class="parent_form-open-value">';
    echo '<form class="form-open-value" method="get" action="' . $openPay->urlRedirectLinkPayment . '" accept-charset="UTF-8">';

    echo '<h1 class="form-title">' . $openPay->messagePaymentTitle . '</h1>';
    echo '<h4 class="message-confirmation-name">' . $openPay->name . '</h4>';
    echo '<h4 class="message-confirmation-lastname">' . $openPay->lastname . '</h4>';
    echo '<h4 class="message-confirmation-email">' . $openPay->email . '</h4>';
    // echo '<h4 class="message-confirmation-phoneNumber">' . $openPay->phoneNumber . '</h4>';

    echo '<h4 class="message-confirmation">' . $openPay->messagePaymentConfirmation . '</h4>';
    echo '<h4 class="message-confirmation-amount">' . money_format('%=*(#10.2n', $openPay->amount) . '</h4>';

    // OpenPay payment link button
    echo '<input class="btn btn-lg btn-primary btn-block style-payment-button" name="Submit" type="submit"  value="' . $openPay->messagePaymentButton . '"onclick="this.form.urlOrigen.value = //window.location.href;">';

    echo '<input name="merchantId"    type="hidden"  value="' . $openPay->merchantId . '">';
    echo '<input name="accountId"     type="hidden"  value="' . $openPay->accountId . '">';
    echo '<input name="description"   type="hidden"  value="' . $openPay->description . '">';
    echo '<input name="referenceCode" type="hidden"  value="' . $openPay->referenceCode . '">';
    echo '<input name="amount"        type="hidden"  value="' . $openPay->amount . '">';
    echo '<input name="tax"           type="hidden"  value="' . $openPay->tax . '">';
    echo '<input name="taxReturnBase" type="hidden"  value="' . $openPay->taxReturnBase . '">';
    echo '<input name="currency"      type="hidden"  value="' . $openPay->currency . '">';
    echo '<input name="test"          type="hidden"  value="' . $openPay->test . '">';
    echo '<input name="buyerEmail"    type="hidden"  value="' . $openPay->buyerEmail . '">';
    // echo '<input name="displayShippingInformation"    type="hidden"  value="YES">';
    // echo '<input name="responseUrl"     type="hidden"  value="' . $openPay->responseUrl . '">';
    // echo '<input name="confirmationUrl" type="hidden"  value="' . $openPay->confirmationUrl . '">';
    echo '<input name="lng"    type="hidden"  value="es">';
    echo '<input name="approvedResponseUrl" type="hidden"  value="' . $openPay->approvedResponseUrl . '">';
    echo '<input name="declinedResponseUrl" type="hidden"  value="' . $openPay->declinedResponseUrl . '">';
    echo '<input name="pendingResponseUrl" type="hidden"  value="' . $openPay->pendingResponseUrl . '">';
    echo '<input name="signature"     type="hidden"  value="' . $openPay->signature . '">';

    echo '<p class="form-instructions">' . $openPay->messagePaymentInstructions . '</p>';
    echo '<input type="image" class="style-payment-gateway-logo" border="0" alt="" src="' . $openPay->urlPaymentGatewayLogo . '" onclick="this.form.urlOrigen.value = window.location.href;">';
    echo '<p class="mt-3 mb-1 text-muted">' . $openPay->messagePaymentCopyRight . '</p>';

    echo '</form>';
    echo '</div>';

    echo '</body>';
    echo '</html>';

    // remove all session variables
    session_unset();

    // destroy the session
    session_destroy();
}

function createOpenPayPaymenyLink($url, $fields_string, $username, $password)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($fields_string)
    ));
    $response = curl_exec($ch);

    if(curl_error ($ch)){
        throw new Exception(curl_error($ch));
    }

    curl_close($ch);
    return $response;
}

?> 
