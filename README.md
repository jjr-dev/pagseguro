# PagSeguro - API
### 💰 Sobre o repositório
Arquivos de configuração e uso da **API** do **PagSeguro** para pagamentos transparentes.

Lembre-se de ler a documentação completa para entender melhor como a API funciona em: https://dev.pagseguro.uol.com.br/](https://dev.pagseguro.uol.com.br/)


⚠️ **Repositório ainda não testado, podem existir erros que serão corrigidos pós testes**


### 🚀 Tecnologias
- PHP
- JavaScript
- JQuery (Ajax)

### 👨‍💻 Como usar
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

#### Variáveis Base
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

#### Obter ID da Sessão
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

#### Gerar Hash temporário
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

#### Obter carteira do cartão [Card]
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

#### Obter valores de parcelamento [Card]
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

#### Criar Token do cartão [Card]
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
