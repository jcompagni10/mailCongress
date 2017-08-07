//Enable Production Mode
//Vue.config.devtools = true;



var vm = new Vue ({
	el: '#mailCongress',
		data: {
		isLine2: false,
		stateList: {AL: 'Alabama', AK: 'Alaska', AZ: 'Arizona', AR: 'Arkansas', CA: 'California', CO: 'Colorado', CT: 'Connecticut', DE: 'Delaware', DC: 'District of Columbia', FL: 'Florida', GA: 'Georgia', HI: 'Hawaii', ID: 'Idaho', IL: 'Illinois', IN: 'Indiana', IA: 'Iowa', KS: 'Kansas', KY: 'Kentucky', LA: 'Louisiana', ME: 'Maine', MD: 'Maryland', MA: 'Massachusetts', MI: 'Michigan', MN: 'Minnesota', MS: 'Mississippi', MO: 'Missouri', MT: 'Montana', NE: 'Nebraska', NV: 'Nevada', NH: 'New Hampshire', NJ: 'New Jersey', NM: 'New Mexico', NY: 'New York', NC: 'North Carolina', ND: 'North Dakota', OH: 'Ohio', OK: 'Oklahoma', OR: 'Oregon', PA: 'Pennsylvania', RI: 'Rhode Island', SC: 'South Carolina', SD: 'South Dakota', TN: 'Tennessee', TX: 'Texas', UT: 'Utah', VT: 'Vermont', VA: 'Virginia', WA: 'Washington', WV: 'West Virginia', WI: 'Wisconsin', WY: 'Wyoming'},
        userInfo: {name: "", address1: "", address2: "", city: "", state: "", zip: "", email: "", image: "faulkner"},
		congressInfo: {senator1: {}, senator2: {}, representative1: {}},
		postcards: ["faulkner", "flag", "fudt", "lorde", "resist", "thisMachine"],
		page: 1,
        repSelected: ["senator1", "senator2", "representative1"],
		message: "",
        fullAddress: "",
        invalidAddress: false,
        textInNameField: false,
        textInEmailField: false,
        price: 0.95
		},
		mounted: function(){
			$("#mailCongress").show();
			window.onbeforeunload = function() { return "Your data will be lost"; };
		},
		methods: {
			alert: function(msg){
				alert(msg);
			},
			setPage: function(){
				window.history.pushState('page'+this.page, 'Mail Congress', 'page'+this.page)
			},
			
			forward: function(){
				this.page ++;
				this.setPage();
            },
			
			back: function(){
				this.page --;
				this.setPage();

			},
          
            error: function(){
              alert("Something isn't quite right. Please try again later");
            },
          
          	formatFullTitle: function(key){
          		if (key == "senator1" ||key == "senator2"){
              		var title = "Sen. ";
              	}
             	else{
					var title = "Rep. ";
				}
				title = title +  this.congressInfo[key]['name'];
				this.congressInfo[key]['fullTitle'] = title;
				return title;
          	},
          	
          	condenseCongressInfo: function(){
           		var condensedCongress = [];
            	for (i = 0; i < this.repSelected.length; i++){
                  condensedCongress[i] = this.congressInfo[this.repSelected[i]];
                }
              return condensedCongress;
            },
          
          	getBioId: function(name){
          		jQuery.ajax({
                  type: 'POST',
                  url: 'https://congress.api.sunlightfoundation.com/legislators',
                  data: {query: name},
                  dataType: 'jsonp',
                  async: false
                })
                .done(function(data){
                  window.setTimeout(function(){
                   return data['results'][0]['bioguide_id'] ;
                  }, 1000);
                })
                .fail(function(data){ 
                    alert("error");
                  	console.log(a = data);
                });
                  
             },
                          
  	
          		
			extractCongressData: function(data, num){
				var type = "senator";
				var body = "US Senate";
				if (num == 1){
					type = "representative";
					body = "House of Representatives";
				}
				
				for (i = 0; i < num; i++){
					this.congressInfo[type+(i+1)] = 
					{
						name: data['officials'][i]['name'],
						body: body,
						address1: data['officials'][i]['address'][0]['line1'],
						address2: data['officials'][i]['address'][0]['line2'],
						city: data['officials'][i]['address'][0]['city'],
						state: data['officials'][i]['address'][0]['state'],
						zip: data['officials'][i]['address'][0]['zip'],
						photo: data['officials'][i]['photoUrl']
                    };
				}				
			},
			
			
			congressApi: function() {
				if (this.userInfo['address1'] == "" || this.userInfo['zip'] == "" || this.userInfo['city'] == ""){
					vm.invalidAddress = true;
					return "";
				}
				this.fullAddress = this.userInfo['address1']+" "+this.userInfo['city']+", "+this.userInfo['state'];
				var apiKey = "AIzaSyCCs-iIIUs21GnHCjlnAjIIIj5fesfuU5g";
				$.get( "https://www.googleapis.com/civicinfo/v2/representatives", { key: apiKey, address: this.fullAddress, roles: "legislatorUpperBody", levels: "country"} )
					.done(function( status ) {
                  		vm.invalidAddress = false;
						vm.extractCongressData(status, 2);
					 })
					 .fail(function(status) {
                  		if(status.responseJSON['error']['errors'][0]['reason'] == 'parseError'){
                    		vm.invalidAddress = true;
                        }
	                  else{
						vm.error();
                      }
					});
				$.get( "https://www.googleapis.com/civicinfo/v2/representatives", { key: apiKey, address: this.fullAddress, roles: "legislatorLowerBody", levels: "country"} )
					.done(function( status ) {
						vm.extractCongressData(status, 1);
						vm.page = 2;
						vm.setPage();

						

					 })
					 .fail(function(xhr, status) {
						console.log(xhr.status);
					}); 
			
			},
          
            zipToStateApi: function(zip){
              var state = "";
              var apiKey = "AIzaSyCCs-iIIUs21GnHCjlnAjIIIj5fesfuU5g";
              $.get("https://maps.googleapis.com/maps/api/geocode/json", 
                  {key: apiKey, address: zip},
                  function(response){
                      try{
                        var address_components = response.results[0].address_components;
                        $.each(address_components, function(index, component){
                            var types = component.types;
                            $.each(types, function(index, type){
                              if(type == 'locality') {
                                vm.userInfo['city'] = component.long_name;
                              }
                              if(type == 'administrative_area_level_1') {
                                vm.userInfo['state'] = component.short_name;
                              }
                            });
                          });

                      }
                      catch(e){
                          vm.invalidAddress = true;
                      }
                   }
                );
        
          	},

          
          	getPicture: function(rep){
              if(rep['photo']){
                var oldUrl = rep['photo'];
                if (oldUrl.substring(0,5) == "https"){
                  return oldUrl;
                }
                if (oldUrl.substring(0,15) == "http://bioguide"){
                  var bioGuideID = oldUrl.slice(-11).substring(0,7);
                  var base = "https://theunitedstates.io/images/congress/225x275/";
                  return base+bioGuideID+'.jpg';
                }
               }
              	return "https://cdn.shopify.com/s/files/1/1810/9023/t/2/assets/noHeadshot.png";
              },
          
         	checkBoxClick: function(key){
              var repPos = $.inArray(key, this.repSelected);
              if(repPos > -1){
                this.repSelected.splice(repPos,1);
              }
              else{
              	if(key =="representative1"){
                	this.repSelected.push(key);
               	}
				else{
					this.repSelected.unshift(key);
				}
			  }                   
        	},
          
            setCheckBox: function(key){
             if($.inArray(key, this.repSelected)>-1){
                return "checked";
              } 
          	},
          checkForText: function(caller){
            	if(this.userInfo[caller].length > 0){
            		return "isText";
            	}
          },
          
          splitName: function(){
            var name = this.userInfo['name'];
            var space = name.indexOf(" ");
            var splitName = ["",""];
            if (space > -1){
            	splitName[0] = name.substring(0,space);
              	splitName[1] = name.substring(space+1,name.length);
            }
            else{
              splitName[0] = name;
            }
            return splitName;
          },
          
          generateLetters: function(){
          for (i = 0; i < this.repSelected.length; i++){
          		var fileName = i.toString()+Math.floor(Math.random()*100).toString() + new Date().getTime();
          		this.congressInfo[this.repSelected[i]]['letterUrl'] = fileName;
          		var data = {
          			'repInfo' : this.congressInfo[this.repSelected[i]],
          			'userInfo' : this.userInfo,
          			'fName' : fileName,
          			'message' : this.message
          		};
          		$.post("letterGen/letterGen.php", data, function(resp){console.log(a= resp);},'text')
          		.fail(function(xhr, status, error){
          			console.log(a = xhr);
          			});
        		}
        	},
        	
        	toPayment: function(){
        		this.generateLetters();
        		this.forward();
        	},
        	
        	getLetterUrl: function(value){
        		var url = this.congressInfo[value]['letterUrl'];
        		return url;
    		}

    	},
          
            
 		
   		computed: {
   			messageLength: function(){	
   				return this.message.length;	
   			},
		  formatRecipients: function(){
			var fullTitles = this.repSelected.map(this.formatFullTitle);
		
			if (this.repSelected.length == 1){
			  return fullTitles[0];
			}
			 if(this.repSelected.length == 2){
			   return fullTitles[0] + " and " + fullTitles[1];
			 }
			else{
			   return fullTitles[0] + ", " + fullTitles[1] + " and " + fullTitles[2];
			 }
		  },
		  totalCost: function(){
			return Math.ceil((this.price * this.repSelected.length)*100)/100;
		  },
		  isPage5: function(){
			if (this.page == 5){
				return "visible";
			}
			else{ 
				return "";
			}
		  }
		}

});
