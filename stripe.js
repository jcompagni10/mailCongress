var stripe = Stripe('pk_live_x3RevrWT6bSqRnFXT7T6QddA');
var elements = stripe.elements();

var stripeSize  ='15px';

if ($("#mailCongress").width() < 400){
	stripeSize = '14px';
}

var style = {
  base: {
  	  iconColor: '#666EE8',
      color: '#31325F',
      lineHeight: '40px',
      fontWeight: 300,
      fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
      fontSize: stripeSize
  }
};

// Create an instance of the card Element
var card = elements.create('card', {style: style});

// Add an instance of the card Element into the `card-element` <div>
card.mount('#card-element');

card.addEventListener('change', function(event) {
  var displayError = document.getElementById('card-errors');
  if (event.error) {
    displayError.textContent = event.error.message;
  } else {
    displayError.textContent = '';
  }
});

var form = document.getElementById('payment-form');
form.addEventListener('submit', function(event) {
  event.preventDefault();
  $("#payButton").hide();
  $("#loadingGif").css('opacity', 1);

  stripe.createToken(card).then(function(result) {
    if (result.error) {
      // Inform the user if there was an error
      var errorElement = document.getElementById('card-errors');
      errorElement.textContent = result.error.message;
      $("#card-errors").show();
      $("#payButton").show();
      $("#loadingGif").css('opacity', 0);

    } else {
      // Send the token to your server
      stripeTokenHandler(result.token);
    }
  });
});

function cardResponse(data){
	console.log(a= data);
	if (data['success']){
		$("#payment-form").hide();
		$("#card-success").show();
		$(".backButton").hide();
  		$("#loadingGif").css('opacity', 0);
	}
	else{
		$("#card-errors").html(data['errors']);
		$("#card-errors").show();
		$("#payButton").show();
  		$("#loadingGif").css('opacity', 0);
	}
}

function stripeTokenHandler(token) {
  var userInfo = JSON.stringify(vm.userInfo);
  var repInfo = JSON.stringify(vm.condenseCongressInfo());
  var numLetters = vm.repSelected.length
  // Insert the token ID into the form so it gets submitted to the server
  var form = document.getElementById('payment-form');
  var hiddenInput = document.createElement('input');
  var hiddenAmount = document.createElement('input');  
  var hiddenUserInfo = document.createElement('input'); 
  var hiddenRepInfo = document.createElement('input');
  var hiddenMessage = document.createElement('input');

  hiddenInput.setAttribute('type', 'hidden');
  hiddenInput.setAttribute('name', 'stripeToken');
  hiddenInput.setAttribute('value', token.id);
  hiddenAmount.setAttribute('type', 'hidden');
  hiddenAmount.setAttribute('name', 'qty');
  hiddenAmount.setAttribute('value', numLetters);
  hiddenUserInfo.setAttribute('type', 'hidden');
  hiddenUserInfo.setAttribute('name', 'userInfo');
  hiddenUserInfo.setAttribute('value', userInfo);
  hiddenRepInfo.setAttribute('type', 'hidden');
  hiddenRepInfo.setAttribute('name', 'repInfo');
  hiddenRepInfo.setAttribute('value', repInfo);
  hiddenMessage.setAttribute('type', 'hidden');
  hiddenMessage.setAttribute('name', 'message');
  hiddenMessage.setAttribute('value', vm.message);
  form.appendChild(hiddenInput);
  form.appendChild(hiddenAmount);
  form.appendChild(hiddenUserInfo);
  form.appendChild(hiddenRepInfo);
  form.appendChild(hiddenMessage);
  


  // Submit the form
  var formData = $(form).serialize();
  $.post('charge.php', formData, function(data){
  	cardResponse(data);
  	}, 'json')
  	.fail(function(data){console.log(data)});  
}
