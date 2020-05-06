<?php
session_start();

require '../Meli/meli.php';
require '../configApp.php';

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
    $user = $meli->get('/users/me', array('access_token' => $_SESSION['access_token'])); 
    $user_id = $user[body]->id; 
    $vendas = $meli->get('/orders/search/recent', array('access_token' => $_SESSION['access_token'], 'sort' =>'date_desc', 'seller' => $user_id)); 
    
    //print_r($);
	echo '<pre>';
		print_r($vendas->body->results);
    echo '</pre>';
    

} else {
	echo '<a href="' . $meli->getAuthUrl($redirectURI, Meli::$AUTH_URL[$siteId]) . '">Login using MercadoLibre oAuth 2.0</a>';
}

?>
<div class="container" style="padding-top: 20px;">
    <div class="row">
        <h3>Lista de Vendas</h3>
        <table class="table">
            <thead>
            <tr>
                <th scope="col">#ID</th>
                <th scope="col">Status</th>
                <th scope="col">Valor</th>
                <th scope="col">Data da venda</th>
                <th scope="col">+Detalhes</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($vendas->body->results as $venda): ?>
                <tr>
                    <th scope="row"><?php echo $venda->id ?></th>
                    <td><?php echo $venda->status ?></td>
                    <td><?php echo "R$ " . number_format($venda->total_amount, 2, ',', '.') ?></td>
                    <td><?php echo date_format(date_create($venda->date_created), 'd-m-Y H:i:s') ?></td>
                    <td>
                        <a href="https://api.mercadolibre.com/orders/<?php echo $venda->id ?>?access_token=<?php echo $_SESSION['access_token'] ?>"
                           target="_blank">+Detalhes</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

