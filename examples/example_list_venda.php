<?php
session_start();

require '../Meli/meli.php';
require '../configApp.php';


// Create our Application instance (replace this with your appId and secret).
//$meli = new Meli(array(
//	'appId'  	=> 'XXXXXXXXXXXX',
//	'secret' 	=> 'XXXXXXXXXXXXXXXXXXXX',
//));

$meli = new Meli($appId, $secretKey);

if(isset($_GET['code']) || isset($_SESSION['access_token'])) {

	// If code exist and session is empty
	if(isset($_GET['code']) && !isset($_SESSION['access_token'])) {
		// //If the code was in get parameter we authorize
		try{
			$user = $meli->authorize($_GET["code"], $redirectURI);
			
			// Now we create the sessions with the authenticated user
			$_SESSION['access_token'] = $user['body']->access_token;
			$_SESSION['expires_in'] = time() + $user['body']->expires_in;
			$_SESSION['refresh_token'] = $user['body']->refresh_token;
		}catch(Exception $e){
			echo "Exception: ",  $e->getMessage(), "\n";
		}
	} else {
		// We can check if the access token in invalid checking the time
		if($_SESSION['expires_in'] < time()) {
			try {
				// Make the refresh proccess
				$refresh = $meli->refreshAccessToken();

				// Now we create the sessions with the new parameters
				$_SESSION['access_token'] = $refresh['body']->access_token;
				$_SESSION['expires_in'] = time() + $refresh['body']->expires_in;
				$_SESSION['refresh_token'] = $refresh['body']->refresh_token;
			} catch (Exception $e) {
			  	echo "Exception: ",  $e->getMessage(), "\n";
			}
		}
	}

	echo '<pre>';
		print_r($_SESSION);
	echo '</pre>';

} else {
	echo '<a href="' . $meli->getAuthUrl($redirectURI, Meli::$AUTH_URL[$siteId]) . '">Login using MercadoLibre oAuth 2.0</a>';
}
// Login or logout url will be needed depending on current user state.
if ($_SESSION['access_token'] ){
	
	if(isset($_REQUEST['orders_id']) == 1):

        $response = $meli -> postWithAccessToken('/orders', array('orders_id' => $_REQUEST['buyer'], 'id' => $_REQUEST['nickname']));

        $_SESSION['orders_id'] = true;

        header("Location: " . $meli -> getCurrentUrl(), TRUE, 302);

    endif;

    $user = $meli -> getWithAccessToken('/users/me');

   
	$orders = $meli -> getWithAccessToken('/orders/search/recent',   array('seller' => $user['json']['id']));
	
}
?>
<!doctype html>
<html>
<head>
	<meta charset="UTF-8"/>
	<title>Pedidos</title>
</head>
<body>
	
	<h1>Pedidos</h1>
	<?php if ($userId): ?>
		<p>olá, <?php echo $user['json']['first_name'] ?></p>
		
		<a href="<?php echo $meli -> getLogoutUrl();?>">Sair</a>
		
	<h2>Orders </h2>
	<ul>
		
	
		<?php foreach ($orders['json']['results'] as &$order): ?>
		<li>
		
			
			<?php echo "<p> Numero do Pedido: ". $order[id]; "</p>"; ?>
			
			<?php echo "<p> Status: ". $order[status]; "</p>"; ?>
			<?php echo "<p> Data de Criação: ". $order[date_created]; "</p>"; ?>
			<?php echo "<p> Data de Fechamento: ". $order[date_closed]; "</p>"; ?>
			<?php echo "<p> Moeda: ". $order[currency_id]; "</p>"; ?>
			
			<p><b>Dados do produto</b></p>
			<?php echo "<p> Moeda: ". $order[order_items][0][currency_id]; "</p>"; ?>
			<?php echo "<p> Id do Produto: ". $order[order_items][0][item][id]; "</p>"; ?>
			<?php echo "<p> Nome do Produto: ". $order[order_items][0][item][title]; "</p>"; ?>
			<?php echo "<p> Id da Categoria: ". $order[order_items][0][item][category_id]; "</p>"; ?>
			<?php echo "<p> Quantidade: ". $order[order_items][0][quantity]; "</p>"; ?>
			<?php echo "<p> Preço do Produto: ". $order[order_items][0][unit_price]; "</p>"; ?>
			<?php echo "<p> Total do pedido: ". $order[total_amount]; "</p>"; ?>
			
			<p><b>Informações do Cliente</b></p>
			<?php echo "<p> Id do Cliente: ". $order[buyer][id]; "</p>"; ?>
			<?php echo "<p> Apelido do Cliente: ". $order[buyer][nickname]; "</p>"; ?>
			<?php echo "<p> Email do Cliente: ". $order[buyer][email]; "</p>"; ?>
			<?php echo "<p> Nome do Cliente: ". $order[buyer][first_name]; "</p>"; ?>
			<?php echo "<p> Sobrenome do Cliente: ". $order[buyer][last_name]; "</p>"; ?>
			<p><b>Telefone</b></p>
			<?php echo "<p> Codigo de area: ". $order[buyer][phone][area_code]; "</p>"; ?>
			<?php echo "<p> Telefone: ". $order[buyer][phone][number]; "</p>"; ?>
			<?php echo "<p> Codigo de area: ". $order[buyer][alternative_phone][area_code]; "</p>"; ?>
			<?php echo "<p> Telefone: ". $order[buyer][alternative_phone][number]; "</p>"; ?>
			<p><b>Documento</b></p>
			<?php echo "<p> Tipo: ". $order[buyer][billing_info][doc_type]; "</p>"; ?>
			<?php echo "<p> Numero: ". $order[buyer][billing_info][doc_number]; "</p>"; ?>
			
			<p><b>Informações de pagamento</b></p>
			<?php echo "<p> Id de Pagamento: ". $order[payments][0][id]; "</p>"; ?>
			<?php echo "<p> Status: ". $order[payments][0][status]; "</p>"; ?>
			<?php echo "<p> Valor da transação: ". $order[payments][0][transaction_amount]; "</p>"; ?>
			<?php echo "<p> Moeda: ". $order[payments][0][currency_id]; "</p>"; ?>
			<?php echo "<p> Data de criação: ". $order[payments][0][date_created]; "</p>"; ?>
			<?php echo "<p> Data da Ultima Atualização: ". $order[payments][0][date_last_updated]; "</p>"; ?>
			<?php echo "<p> Razão: ". $order[payments][0][reason]; "</p>"; ?>
			<?php echo "<p> Detalhe de status: ". $order[payments][0][status_detail]; "</p>"; ?>
			<?php echo "<p> Metodo de pagamento: ". $order[payments][0][payment_method_id]; "</p>"; ?>
			<?php echo "<p> Tipo de operação: ". $order[payments][0][operation_type]; "</p>"; ?>
			<?php echo "<p> Tipo de pagamento: ". $order[payments][0][payment_type]; "</p>"; ?>
			<?php echo "<p> Tipo: ". $order[payments][0][id]; "</p>"; ?>
			
			<p><b>Informações de envio</b></p>
			<?php echo "<p> Id de envio: ". $order[shipping][id]; "</p>"; ?>
			<?php echo "<p> Status: ". $order[shipping][status]; "</p>"; ?>
			<?php echo "<p> Tipo de Envio: ". $order[shipping][shipment_type]; "</p>"; ?>
			<?php echo "<p> Data de Criação: ". $order[shipping][date_created]; "</p>"; ?>
			<?php echo "<p> Moeda: ". $order[shipping][currency_id]; "</p>"; ?>
			<?php echo "<p> Valor: ". $order[shipping][cost]; "</p>"; ?>
			<?php echo "<p> Id: ". $order[shipping][receiver_address][id]; "</p>"; ?>
			<?php echo "<p> Endereço: ". $order[shipping][receiver_address][address_line]; "</p>"; ?>
			<?php echo "<p> Cep: ". $order[shipping][receiver_address][zip_code]; "</p>"; ?>
			<?php echo "<p> Complemento: ". $order[shipping][receiver_address][comment]; "</p>"; ?>
			<?php echo "<p> Nome da rua: ". $order[shipping][receiver_address][street_name]; "</p>"; ?>
			<?php echo "<p> Nº : ". $order[shipping][receiver_address][street_number]; "</p>"; ?>
			<?php echo "<p> Id da Cidade: ". $order[shipping][receiver_address][city][id]; "</p>"; ?>
			<?php echo "<p> Cidade: ". $order[shipping][receiver_address][city][name]; "</p>"; ?>
			<?php echo "<p> Id do Estado: ". $order[shipping][receiver_address][state][id]; "</p>"; ?>
			<?php echo "<p> Estado: ". $order[shipping][receiver_address][state][name]; "</p>"; ?>
			<?php echo "<p> Id do Pais: ". $order[shipping][receiver_address][country][id]; "</p>"; ?>
			<?php echo "<p> Pais: ". $order[shipping][receiver_address][country][name]; "</p>"; ?>
			
			<?php echo "<br/>"; ?>
			<?php echo "<br/>"; ?>
			
			<?php echo "-----------------------------------------------------------------------------"; ?>
						
			<?php echo "<br/>"; ?>
			<?php echo "<br/>"; ?>
			
						
		</li>
		
	
		<?php endforeach; ?>
	</ul>
		
		
		
	<?php else:?>
		<div>
			<p>Login usando OAuth 2.0:</p>
			<a href="<?php echo $meli -> getLoginUrl(array('scope' => array('orders')));?>">Login no Mercado Livre</a>
		</div>
	<?php endif?>
	
	
</body>
</html>