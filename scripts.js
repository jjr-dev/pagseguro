var brandName;
var hash;
var cardToken;
var numCard 	= '4444.444.4444.4444';
var totalValue 	= '200.00';
var cvvCard 	= '123';
var monthCard	= '10';
var yearCard	= '2030';

// getSessionId
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

// Hash Generate
PagSeguroDirectPayment.onSenderHashReady(function(res){
	if(res.status == 'error') {
		console.log(res.message);
		return false;
	}
	hash = res.senderHash;
});

// getBrand
PagSeguroDirectPayment.getBrand({
	cardBin: numCard.replace(/\s/g, ''),
	success: function (res) {
		brandName = res.brand.name;
	},
	error: function (res) {
		console.log(res);
	}
});

// getInstallments
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

// Create CardToken
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