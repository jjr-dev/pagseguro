
# PagSeguro - API
![PagSeguro API versão 2](https://img.shields.io/badge/PagSeguro%20API-V2-orange?logo=pagseguro) ![Status: Em testes](https://img.shields.io/badge/Status-Testes-yellow?logo=github)

## 💰 Sobre o repositório
Arquivos de configuração e uso da **API** do **PagSeguro** para pagamentos transparentes.

Lembre-se de ler a documentação completa para entender melhor como a API funciona em: [https://dev.pagseguro.uol.com.br/](https://dev.pagseguro.uol.com.br/)

## 🚀 Tecnologias
- PHP
- JavaScript
- JQuery (Ajax)

## 👨‍💻 Como usar
O repositório utiliza o cURL do PHP para fazer as requisições e envios para a API do PagSeguro e JavaScript para obter informações do usuário.

No arquivo ```example.php``` é possível ver os exemplos de um arquivo que reune as informações do usuário (cliente) e dados do cartão ou boleto para gerar o XML e cobrança transparente.

Antes de realizar a cobrança pela API será necessário inicializar o Script do PagSeguro na página onde será exibido o formulário de pagamento:
```html
<?php
	require_once('configs.php');
?>

<script type="text/javascript" src="<?= SCRIPT_PAGSEGURO; ?>"></script>
```
> O **SCRIPT_PAGSEGURO** está definido no arquivo ```configs.php```

Após inicializar o script, é necessário executar algumas funções para gerar a cobrança corretamente. Veja o arquivo ```scripts.js``` para ver as funções na integra.

Todos os títulos com **[Card]** são de ações necessárias apenas para pagamentos por cartão de crédito.

### Configurar Credenciais
No arquivo ```configs.php``` insira as credenciais de acesso a conta do PagSeguro, sendo elas o **Email** da conta e **Token**. É possível informar os dados de **SandBox** (ambiente de testes) e **Produção**, alternando entre eles trocando o valor da variável ```$sandbox``` para ```true``` ou ```false```.
[Clique aqui](https://faq.pagseguro.uol.com.br/duvida/como-gerar-token-para-integracao-com-o-site/841#rmcl) para saber como obter o Token do PagSeguro.

#### URL de Notificação
A URL de notificação deve ser configurada também no arquivo ```configs.php``` e deve indicar o caminho para o arquivo que o PagSeguro irá executar quando ocorrer mudanças no status de pagamento de alguma cobrança, enviando por POST os dados referente ao pagamento. Veja o exemplo em ```notify.php```.

### Variáveis Base
A variável ```hash``` é obrigatória para qualquer tipo de pagamento (boleto ou cartão de crédito), já as demais variáveis são necessárias apenas para pagamento por cartão de crédito. 

As variáveis ```numCard, totalValue, cvvCard, monthCard, yearCard``` estão definidas apenas por exemplo, elas devem ser preenchidas pelos dados enviados pelo formulário preenchido pelo usuário.
```js
var hash;
var brandName;
var cardToken;
var numCard 	= '4444.444.4444.4444';
var totalValue 	= '200.00';
var cvvCard 	= '123';
var monthCard	= '10';
var yearCard	= '2030';
```

### Obter ID da Sessão
A função para obter o ID da sessão do usuário deve ser executado assim que a página de pagamentos for carregada.
```js
function getSessionId(){
	$.ajax({
		url: 'getSessionId.php',
		type: 'POST',
		dataType: 'json',
		success: function (res) {
			PagSeguroDirectPayment.setSessionId(res.id);
		}
	});
}
```
A função irá retornar o valor da sessão do usuário e armazena-lo no script do PagSeguro.

### Gerar Hash temporário
O Hash temporário deve ser gerado e armazenado em uma variável para envia-lo posteriormente ao PHP de geração da cobrança junto das demais informações de pagamento.
```js
PagSeguroDirectPayment.onSenderHashReady(function(res){
	if(res.status == 'error') {
		console.log(res.message);
		return false;
	}
	hash = res.senderHash;
});
```
A função retorna o Hash e o armazena na variável ```hash``` criada em **Variáveis Base**.

### Obter carteira do cartão [Card]
Para obter a carteira do cartão é necessário o número do mesmo, por isso é recomendável executar a função apenas quando o mesmo estiver preenchido.
```js
PagSeguroDirectPayment.getBrand({
	cardBin: numCard.replace(/\s/g, ''),
	success: function (res) {
		brandName = res.brand.name;
	},
	error: function (res) {
		console.log(res);
	}
});
```
A função irá retornar o nome da Bandeira e deve ser armazenado em uma variável para ser utilizado posteriormente, assim como o Hash Temporário.

### Obter valores de parcelamento [Card]
Para obter os valores de parcelamento do PagSeguro já com as taxas de parcelamento aplicadas, é necessário utilizar a função e definir a quantidade máxima de parcelas sem juros em ```maxInstallmentNoInterest```.

É necessário também enviar o valor total da compra e o nome da carteira do cartão (obtido anteriormente) em ```amount``` e ```brand``` respectivamente.
```js
PagSeguroDirectPayment.getInstallments({
	amount: totalValue,
	maxInstallmentNoInterest: 18,
	brand: brandName,
	success: function(res){
		console.log(res);
	},
	error: function(res) {
		console.log(res);
    }
});
```
O retorno será em objeto com todas as possíveis quantidades de parcelamento e os valores da mesma. Utilize como preferir para exibir a seleção de parcelamento e armazene a quantidade de parcelamentos selecionado pelo usuário.

### Criar Token do cartão [Card]
É necessário criar o Token do cartão para que possa transportar os dados do mesmo de forma criptografada, deixando tudo seguro. Para gerar o token é necessário o **número do cartão**, **mês e ano de expiração do cartão**, **CVV do cartão** (informados pelo usuário) e **nome da bandeira do cartão** obtida em função anteriormente.

Uma vez que a geração do Token exige todas as informações do cartão, deve ser a última função executada e apenas quando todas as informações estiverem preenchidas.
```js
PagSeguroDirectPayment.createCardToken({
	cardNumber: numCard,
	brand: brandName,
	cvv: cvvCard,
	expirationMonth: monthCard,
	expirationYear: yearCard,
	success: function(res) {
		cardToken = res.card.token;
	},
	error: function(res) {
		console.log(res);
	}
});
```
A função irá retornar o token que deve ser armazenado em uma variável para que possa ser enviado para o PHP em seguida.

### Gerar cobrança
Para gerar a cobrança, realize o envio dos dados através de um ```$_POST``` para o arquivo que irá organizar os dados e gerar o **XML** para envio ao PagSeguro. Consulte o arquivo ```example.php``` para entender qual a forma de organização na integra.

#### Requires e credenciais
Informe os dados de credenciais em ```configs.php``` conforme dito anteriormente e apenas os chame no arquivo de geração da cobrança (como ocorre em ```example.php```).
```php
require_once('config.php');
require_once('generateXML.php');

$urlPagseguro		= URL_PAGSEGURO;
$emailPagSeguro 	= EMAIL_PAGSEGURO;
$tokenPagSeguro 	= TOKEN_PAGSEGURO;
$notificationURL 	= URL_NOTIFICATION;
```


#### Dados do Usuário
Os dados do usuário são necessários para todo tipo de cobrança, sendo ela em boleto ou cartão de crédito.
```php
$client = array(
	"code" 			=> '001',
	"name" 			=> 'Julimar Gomes da Silva Junior',
	"cpf" 			=> str_replace('.', '', str_replace('-', '', '797.710.720-13')),
	"ddd"			=> '11',
	"phone" 		=> '912341234',
	"hash"    		=> '123456789',
	"email" 		=> 'contato@julimarjunior.com.br',
	"cep" 			=> '78040-290',
	"city" 			=> 'Cuiabá',
	"state" 		=> 'MT',
	"district" 		=> 'Santa Rosa',
	"address" 		=> 'Rua Polônia',
	"number" 		=> '123',
	"complement"		=> ''
);
```
São todos os dados obrigatórios para a geração da cobrança através da API do PagSeguro, eles serão utilizados para geração do XML.
> Lembrando que o HASH é gerado pelo Script e os outros dados informados pelo próprio usuário.
> Caso esteja em ambiente de testes, é obrigatório que o Email do usuário seja com @sandbox.pagseguro.com.br.

Os dados de frete serão preenchidos com o endereço informados no array ```$client```.

#### Dados do Cartão [Card]
Caso o usuário for realizar o pagamento em Cartão de Crédito, é necessário informar dados do titular do cartão junto ao token do cartão gerado pelo Script.
```php
$client['card'] = array(
	"token"     		=> '123456789',
	"quantity"    		=> '18',
	"value"    		=> '20.00',
	"valueTotal"    	=> '360.00',
	"name"			=> 'Julimar Gomes da Silva Junior',
	"cpf" 			=> str_replace('.', '', str_replace('-', '', '797.710.720-13')),
	"birthDate"		=> '2001-07-10',
	"ddd"			=> '11',
	"phone"			=> '912341234',
	"cep" 			=> '78040-290',
	"city" 			=> 'Cuiabá',
	"state" 		=> 'MT',
	"district" 		=> 'Santa Rosa',
	"address" 		=> 'Rua Polônia',
	"number" 		=> '123',
	"complement"		=> ''
);
```
Caso o token não seja informado automaticamente a geração da cobrança será feita via Boleto, por isso informe-o apenas se necessário, assim conseguindo definir a forma de pagamento através da mesma.

Os itens ```quantity```, ```value``` e ```valueTotal``` são referente a **quantidade de parcelas**, **valor da parcela** e **valor total** respectivamente.

#### Produtos
Para adicionar os produtos ao XML, adicione-os ao Array contendo todos os dados necessários:
```php
$products = array(
	0 => array(
		"code"			=> '001',
		"name"			=> 'Nome produto 01',
		"amount"		=> '2.00',
		"quantity"		=> '10'
	),
	1 => array(
		"code"			=> '002',
		"name"			=> 'Nome produto 02',
		"amount"		=> '6.00',
		"quantity"		=> '5'
	)
);
```
Lembrando que cada produto deve ser em uma chave do Array.

#### Frete
É necessário informar o frete, caso o frete for gratuito informe o valor como ```0.00```.
```php
$shipping = "20.00";
```

#### Gerar XML
Agora que todas as informações estão reunidas, basta executar a função ```generateXML()``` enviando todas as variáveis e arrays.
```php
$xml = generateXML($products, $client, $shipping, $notificationURL);
```
Assim, o XML será gerado e armazenado em ```$xml```.

#### Nome na cobrança
Para personalizar o nome que será exibido na fatura do Cartão de Crédito ou no Boleto, altere os valores dentro de ```<dynamicPaymentMethodMessage>``` do arquivo ```generateXML.php```.
> O nome que será exibido na fatura do Cartão de Crédito deve obrigatóriamente conter NO MÁXIMO 13 caracteres.

#### Efetuar geração da cobrança
Com o XML gerado, envie-o para a API do PagSeguro através de um cURL e obtenha a resposta do mesmo:
```php
$url = $urlPagseguro . "transactions?email=$emailPagseguro&token=$tokenPagseguro";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml;charset=UTF-8'));

$data = curl_exec($ch);
if($data && $data != "Internal Server Error") {
	$dataXML = simplexml_load_string($data);
	$dataArray = get_object_vars($dataXML);
	if($dataXML->error) {
		// Error
		var_dump($dataXML->error);
		exit;
	} else {
		// Success
	}
}

curl_close($ch);
```
A resposta do cURL será armazenada em ```$data``` e as ações de Erro ou Sucesso devem ser realizadas dentro do ```if{} else{}``` conforme exemplo.

Vale lembrar que por motivos de atualizações através das notificações do PagSeguro, é necessário armazenar no histório de pagamentos (banco de dados) o código da transação gerada pelo PagSeguro. O mesmo estará em ```$dataArray['code']```.

### Atualizar Status
Após uma cobrança ser gerada, o usuário poderá atualizar seu status realizando o pagamento, cancelamento e outras opções. Após a cobrança sofrer atualizações o PagSeguro irá enviar uma notificação para o arquivo ```notify.php``` (configurado anteriormente em ```configs.php```).

Para receber a notificação, é necessário fazer um cURL enviando o código da notificação para obter o código da transação e o atual status dela (por isso deve ser salvo o código da transação conforme explicado anteriormente).
```php
if($_POST['notificationType'] && $_POST['notificationCode']) {
	$urlPagSeguro		= URL_PAGSEGURO;
	$emailPagSeguro 	= EMAIL_PAGSEGURO;
	$tokenPagSeguro 	= TOKEN_PAGSEGURO;
	$notifyCode 		= $_POST['notificationCode'];

	$url = $urlPagSeguro . "transactions/notifications/$notifyCode?email=$emailPagSeguro&token=$tokenPagSeguro";

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($curl);
	curl_close($curl);
	
	$dataXML 	= simplexml_load_string($data);
	$dataArray 	= get_object_vars($dataXML);
	if($data) {
		$code_pay 	= $dataArray['code'];
		$status 	= $dataArray['status'];
	} else {
		// Error
	}
} else {
	// Error
}
```
Se existir o valor em ```$data``` então basta obter o ```$dataArray['code']``` e ```$dataArray['status']``` e atualizar o status no sistema.

## 👋 The End
Caso encontre erros ou sugestões, sinta-se a vontade para sugerir a correção. 

Veja um exemplo do XML gerado [clicando aqui](https://codebeautify.org/xmlviewer/cbfcb75e).

Lembrando que esse repositório **Não é oficial** e tem o intuito apenas de facilitar o entendimento das ações necessárias para a geração da cobrança, você pode (e deve) ver a [documentação do PagSeguro](https://dev.pagseguro.uol.com.br/) para dúvidas.

Caso deseje, assista a série de vídeos da **Celke** [Integrar PHP com PagSeguro](https://www.youtube.com/watch?v=Z-T1QlJY0jM).

Bom proveito!
