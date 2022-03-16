<head><meta http-equiv="Content-Type" content="text/html; charset=gb18030">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js">
    </script>
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
        font-weight: 100;
        font-size: 1rem;
    }

    .form-open-value .message-confirmation {
        color: #007bff;
    }

    .form-open-value .message-confirmation-amount {
        color: #007bff;
        font-weight: 900;
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
$_SESSION["showFormWithPayUAttributes"] = false;



if($_SERVER['REQUEST_METHOD'] == "GET") {

	// $urlImg = $_GET['imagehanabi'];
	require_once("./formulario-ingrese-monto-pago.phtml");
}

if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['btnFormOpenValue'])) {
	
	if($_POST['currency'] == "PEN") {
		setlocale(LC_MONETARY, 'es_PE');
	} else if ($_POST['currency'] == "USD") {
		setlocale(LC_MONETARY, 'en_US');
	}

	$_SESSION["showFormWithPayUAttributes"] = true;

	CrearFormularioMontoAbierto();
}

Class PayU_Model {
	public $apikey;
	public $url;
	public $merchantId;
	public $accountId;
	public $description;
	public $referenceCode;
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
}

function CrearFormularioMontoAbierto() {

    if(empty($_POST['txtOpenAmount']))
    {
        echo "caught post empty";
        $this->HandleError("txtOpenAmount is empty!");
        return false;
    }
    
    $GLOBALS['$payU'] = new PayU_Model();

    $payU->apikey = "eUUdi6J4qPr5qFQ75l1ES5Cvd3";
    $payU->url = trim($_POST['url']);
    $payU->merchantId = trim($_POST['merchantId']);
    $payU->accountId = trim($_POST['accountId']);
    $payU->description = trim($_POST['description']);
    $payU->referenceCode = time();
    $payU->amount = trim($_POST['txtOpenAmount']);
    $payU->tax = trim($_POST['tax']);
    $payU->taxReturnBase = trim($_POST['taxReturnBase']);
    $payU->currency = trim($_POST['currency']);
    $payU->test = trim($_POST['test']);
    $payU->buyerEmail = trim($_POST['buyerEmail']);
    $payU->responseUrl = trim($_POST['responseUrl']);
    $payU->confirmationUrl = trim($_POST['confirmationUrl']);
    $payU->approvedResponseUrl = trim($_POST['approvedResponseUrl']);
    $payU->declinedResponseUrl = trim($_POST['declinedResponseUrl']);
    $payU->pendingResponseUrl = trim($_POST['pendingResponseUrl']);
    $payU->messagePaymentButton = trim($_POST['messagePaymentButton']);
    $payU->messagePaymentTitle = trim($_POST['messagePaymentTitle']);
    $payU->messagePaymentConfirmation = trim($_POST['messagePaymentConfirmation']);

    // sSignature formaat = hash('md5',"11111~11111~11111~5~PEN");
    $payU->signature = GenerateSignature($payU->apikey, $payU->merchantId, $payU->referenceCode, $payU->amount, $payU->currency);

	$payU->messageWebTabTitle = trim($_POST['messageWebTabTitle']);
	$payU->messagePaymentInstructions = trim($_POST['messagePaymentInstructions']);
	$payU->messagePaymentCopyRight = trim($_POST['messagePaymentCopyRight']);
	$payU->urlImgLogo = trim($_POST['urlImgLogo']);
	$payU->urlImgBackground = trim($_POST['urlImgBackground']);

   	RenderOpenValueButtonHTML($payU);
    
    return true;	
}

function GenerateSignature($apikey, $merchantId, $referenceCode, $amount, $currency) {
	
	$signature =  $apikey."~".$merchantId."~".$referenceCode."~".$amount."~".$currency;
	return hash('md5',$signature);
}

function RenderOpenValueButtonHTML($payU) {

	// var_dump($payu);
		
    echo '<!DOCTYPE html>';
    echo '<html>';

    	echo '<head>';
		    echo '<title>' . $payU->messageWebTabTitle . '</title>';
	    echo '</head>';


	    echo '<body class="text-center">';


	    	echo '<nav class="navbar navbar-light bg-dark">';
		    	echo '<a class="navbar-brand" href="#">';
		    		echo '<img src="' . $payU->urlImgLogo . '" width="215" height="63" alt="">';
		    	echo '</a>';
	    	echo '</nav>';


		    echo '<div class="parent_form-open-value">';
		   		echo '<form class="form-open-value" method="post" action="' . $payU->url . '" accept-charset="UTF-8">';

		    		// echo '<div class="container">';
	    				echo '<h1 class="form-title">' . $payU->messagePaymentTitle . '</h1>';
	    				echo '<br></br>';

					    echo '<h4 class="message-confirmation">' . $payU->messagePaymentConfirmation . '</h4>';
					    // echo '<h4 class="message-confirmation-amount">'. money_format('%=*(#10.2n', $payU->amount) . ' ' .  $payU->currency . '</h4>'; 
					    echo '<h4 class="message-confirmation-amount">'. money_format('%=*(#10.2n', $payU->amount) . '</h4>';
					    // echo '<div class="alert alert-primary" role="alert">';
					     	// echo 'Usted pagará la suma de :' . $payU->amount . 'nuevos soles.';
					    // echo '</div>';
					    // echo '<input class="btn btn-lg btn-primary btn-block" name="Submit"        type="submit"  value="' . $payU->messagePaymentButton . '"onclick="this.form.urlOrigen.value = //window.location.href;">';
					    echo '<input type="image" border="0" alt="" src="http://www.payulatam.com/img-secure-2015/boton_pagar_pequeno.png" onclick="this.form.urlOrigen.value = window.location.href;">';
					    echo '<input name="merchantId"    type="hidden"  value="' . $payU->merchantId . '">';
					    echo '<input name="accountId"     type="hidden"  value="' . $payU->accountId . '">';
					    echo '<input name="description"   type="hidden"  value="' . $payU->description . '">';
					    echo '<input name="referenceCode" type="hidden"  value="' . $payU->referenceCode . '">';
					    echo '<input name="amount"        type="hidden"  value="' . $payU->amount . '">';
					    echo '<input name="tax"           type="hidden"  value="' . $payU->tax . '">';
					    echo '<input name="taxReturnBase" type="hidden"  value="' . $payU->taxReturnBase . '">';
					    echo '<input name="currency"      type="hidden"  value="' . $payU->currency . '">';
					    echo '<input name="test"          type="hidden"  value="' . $payU->test . '">';
					    echo '<input name="buyerEmail"    type="hidden"  value="' . $payU->buyerEmail . '">';
					    // echo '<input name="displayShippingInformation"    type="hidden"  value="YES">';
					    // echo '<input name="responseUrl"     type="hidden"  value="' . $payU->responseUrl . '">';
					    // echo '<input name="confirmationUrl" type="hidden"  value="' . $payU->confirmationUrl . '">';
					    echo '<input name="lng"    type="hidden"  value="es">';
					    echo '<input name="approvedResponseUrl" type="hidden"  value="' . $payU->approvedResponseUrl . '">';
					    echo '<input name="declinedResponseUrl" type="hidden"  value="' . $payU->declinedResponseUrl . '">';
					    echo '<input name="pendingResponseUrl" type="hidden"  value="' . $payU->pendingResponseUrl . '">';
					    echo '<input name="signature"     type="hidden"  value="' . $payU->signature . '">';

	    				echo '<br></br>';
					    echo '<p class="form-instructions">' . $payU->messagePaymentInstructions . '</p>';
					    echo '<p class="mt-5 mb-3 text-muted">' . $payU->messagePaymentCopyRight . '</p>';
		    		// echo '</div>';
		    	echo '</form>';
			echo '</div>';

	    echo '</body>';
    echo '</html>';
	
	// remove all session variables
	session_unset(); 

	// destroy the session 
	session_destroy(); 
}

?>