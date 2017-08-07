<?php
		if ( $_SERVER["REMOTE_ADDR"] !== "::1"){
			header('Content-Type: application/liquid');
			echo "{% assign pageName = 'mailCongress' %}";

		}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
	<?php 
		if ( $_SERVER["REMOTE_ADDR"] == "::1"){
			echo  "<link href=  https://cdn.shopify.com/s/files/1/1810/9023/t/2/assets/timber.scss.css   rel=  stylesheet   type=  text/css   media=  all   />
					<link href=  https://cdn.shopify.com/s/files/1/1810/9023/t/2/assets/theme.scss.css?1   rel=  stylesheet   type=  text/css   media=  all   />";
		}
	?>
		<script src="https://js.stripe.com/v3/"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
		<script src="https://unpkg.com/vue/dist/vue.min.js"></script>
		<script>!window.Vue && document.write('<script src="script/vue.min.js"><\/script>')</script>
		
		
		<!-------------- FB Ads --> 
		<script>(function(d, s, id) {    var js, fjs = d.getElementsByTagName(s)[0];    if (d.getElementById(id)) return;    js = d.createElement(s); js.id = id;    js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.9&appId=1491115217847107";    fjs.parentNode.insertBefore(js, fjs);  }(document, 'script', 'facebook-jssdk'));</script>
	</head>
	<div id="fb-root"></div>
 <div class="mailCongressWrapper">
	<div class="grid">
      <div class="grid__item large--three-fifths push--large--one-fifth">
        <div id="mailCongress">
            <div id="page1" v-if = "page == 1">
              	<form id="userInfo" action="##" v-on:submit.prevent="congressApi()">
                  <div class="pageTitle">
                  	<h2>Let's Find Your Representatives</h2>
                  </div>
                  <hr class="hr--small">
                  <div class="inputSectLg">
                    <label for="address">Address</label><br>
                    <input type="text" id="address" placeholder="123 Your Street" v-model="userInfo['address1']" required  >
                  </div>
                  <div class="addLine2 inputSectLg" v-if="!isLine2" v-on:click="isLine2 = true;">
                  	<span> <img src="https://juliancompagniportis.com/mailCongress/img/plusIcon.png"> Address Line 2 </span>
                  </div>
                  <div class="inputSectLg" v-if ="isLine2">
                    <label for="address">Address Line 2</label><br>
                    <input type="text"  id="address" placeholder="Apt. #, suite, etc. (optional) " v-model="userInfo['address2']" >
                  </div>
                  <div class="inputSectSm zip">
                    <label for="zip">Zip</label><br>
                    <input type="text" required id="zip" placeholder="12345" v-on:blur="zipToStateApi(userInfo['zip'])" v-model="userInfo['zip']" required>
                  </div>
                  <div class="inputSectSm">
                    <label for="city">City</label><br>
                    <input type="text" required id="city" placeholder="Your City" v-model="userInfo['city']" required> <br>
                  </div>
                  <div class="error" v-if ="invalidAddress"> Invalid Address </div>
                <div class="navButtons">
                  <input type="submit" id="submit" value="Find My Congress People">
                </div>
              </form>
            </div>			
            <div id="page2" v-if = "page == 2">
              <div class="pageTitle">
              	<h3>Which Representatives would you like to write to?</h3>
              	<hr class="hr--small">
              </div>

              <table id="repTable">
                <tr v-for="(value, key, index) in congressInfo" class="repRow">
                  <td class="selectCol">
                    <div class="checkBox" v-bind:name="key" v-bind:class="setCheckBox(key)" v-on:click="checkBoxClick(key)"></div>
                  </td>
                  <td class="pictureCol">
                      <img class="repPicture" v-bind:src="getPicture(value)">
                  </td>
                  <td class="infoCol">
                    <span class="name" v-text="value['name']"></span><br>
                    <span class="body" v-text="value['body']"></span>
                  </td>
                </tr>
              </table>
              <div class="navButtons">
                  <button type="button" class="backButton" v-on:click="back()">Back</button>
                  <button v-on:click="forward">Write Postcards</button>
              </div>
            </div>
            <div id="page3" v-if = "page ==3">
              <div class="pageTitle">
                <h4> You are writing to 
                	<span v-text="formatRecipients"></span>
              	</h4>
              	<hr class="hr--small">
              </div>
              <form id="messageForm" action ="#" v-on:submit.prevent="forward">
                <div id="letter">
				  <b><span class="letterRecipients" v-text="'Dear ' +formatRecipients+',*'"></span></b>
				  <textarea class="msgTxtArea" type="textArea" rows="10" cols="48" v-model="message"  maxlength="700"></textArea>
				  <div class="signature">
					Sincerely,<br>
					<input type="text" required id="signatureName" v-bind:class = "checkForText('name')" v-model="userInfo['name']" placeholder="Your Name" required><br>
					<input type="email" id="signatureEmail" v-bind:class = "checkForText('email')" v-model="userInfo['email']" placeholder="Email" required>
				  </div>
				</div>
				<div id ="charLimit">
					<span v-text="messageLength"></span> of 700 characters
				</div>
				<div class="astrisk"> *Your final postcards will each be addressed and mailed individually </div>
				<div class="navButtons">
				  <button type="button" class="backButton" v-on:click="back()">Back</button>
				  <input type="submit" value = "Next" class="forwardButton">
				</div>
			  </form>
            </div>
            <div id="page4" v-if = "page == 4">
			  <div class="pageTitle">
			  	<h2>Choose Postcard Image</h2>
			  	<hr class="hr--small"></hr>
			  </div>
			  <div class="grid cardGrid">
			  	<div class= "grid__item one-third small--one-half cardOption" v-for = "image in postcards">
			  		<div class="cardOptWrapper"> 
						<label>
						  <input type="radio" name="postcardOpt" v-bind:value="image" v-model="userInfo['image']"/></input>
						  <img v-bind:src="'https://cdn.shopify.com/s/files/1/1810/9023/t/2/assets/' + image + '_200x.png?1'" vm-on:click ="userInfo['image'] = image">
						</label>
					</div>
				</div>
			  </div>
			  <div class="navButtons">
			  	<button type="button" class="backButton" v-on:click="back">Back</button>
                <button v-on:click="toPayment">Send Postcards</button>
			  </div>
			</div>
            <div id="page5" v-bind:class = "isPage5">
              <div class="pageTitle">
              	<h3> Great Job! Just One Final Step</h3>
			  	<hr class="hr--small">

              </div>
                 <table class="invoice">
                   <tr class="invoiceHeader">
                     <td class="letter">Item</td>
                     <td class="qty">Qty</td>
                     <td class="price">Price</td>
                   </tr>
                   <tr v-for="(value, key, index) in repSelected" class="invoiceRow">
                     <td class="letter"><span class="letterTitle" v-text = "'Postcard to ' + formatFullTitle(value)"></span><span class="preview"> (<a v-bind:href ="'letterGen/letters/' + getLetterUrl(value) +'f.pdf'" target="_blank">Preview</a>)</span></td>
                     <td class="qty">1</td>
                     <td class="price" v-text="0.95"></td>
                   </tr>
                   <tr class="totalRow">
                     <td><b>Total: $</b><span v-text ="totalCost"></span></td>
                     <td></td><td></td>
                   </tr>
              	</table>
              	<div id="payment">
					<form action="charge.php" method="post" id="payment-form">
					  <div class="form-row">
					  	<div id="cardSection">
							<label for="card-element" class="cardLabel">
							  Credit Card
							</label>
							<div id="card-element">
							  <!-- a Stripe Element will be inserted here. -->
							</div>
						</div>
						<div class="button-wrapper">
					  		<button id="payButton">Pay Now</button>
					  		<img id="loadingGif" src= "img/mail.gif">
					  	</div>
					  </div>
						<!-- Used to display Element errors -->
						<div id="card-errors" role="alert"></div>
					</form>
					<div id="card-success">
						Payment Succesful --Your Postcards are on their way!!
					</div>
				</div>   
				<div class="invoiceInfo">
                  <p>*While we wish we could offer this service for free, we must charge a small 
                    amount to cover the cost of postage, ink, stationary (and paying our full-time stamp licker.)
                  </p>
              	</div>    
				<div class="navButtons">
					<button class="backButton" v-on:click="back">Back</button>
				</div>
			</div>

				
			<div id="social">
				<div class="fb-share-button" data-href="http://voxcoalition.org/tools/mailCongress/index.php" data-layout="button_count" data-size="large" data-mobile-iframe="false"><a class="fb-xfbml-parse-ignore" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2Fvoxcoalition.org%2Ftools%2FmailCongress%2F&amp;src=sdkpreparse">Share</a></div>
			</div>

        </div>
        <script src="https://juliancompagniportis.com/mailCongress/vue.js"></script>
       	<script src="https://juliancompagniportis.com/mailCongress/stripe.js"></script>

	</div>
</div>
</div>