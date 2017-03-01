refreshCart();
function addToCart(p){
	jsonPost('./php/send-ajax-data.php',{"query":[p]}, function(a){
			if(a['Error'] > 0){
				alert("Error");
			}
			else{
	var cart = localStorage.cart;
	if(cart === undefined){
		cart = {};}
	else{	
		cart = JSON.parse(cart);}
	if(cart[p] === undefined){
		cart[p] = {'num':0};
	}
	cart[p]['name']=a['name'][p];
	cart[p]['price']=a['price'][p];
	cart[p]['num'] = parseInt(cart[p]['num']) + 1;
	localStorage.cart = JSON.stringify(cart);
	refreshCart();
}
		}
	);
}

function removeProduct(p){
	var cart = localStorage.cart;
	if(cart === undefined){
		cart = {};
	}
	else	{
		cart = JSON.parse(cart);}
	cart[p] = undefined;
	localStorage.cart = JSON.stringify(cart);
	refreshCart();
}

function inputNumber(p,value){
	var cart = JSON.parse(localStorage.cart);
	if(value > 0){
	   	var num = value;
	    cart[p]['num'] = num;
	}
	else if (value === 0){
		delete cart[p];
	}
	else{
		alert("Please input non-negtive number!");
	}
	localStorage.cart = JSON.stringify(cart);
	refreshCart();
}

function refreshCart(){
	if(localStorage.cart !== undefined && localStorage.cart !== "{}"){
		jsonPost('./php/send-ajax-data.php',{}, function(a){
			if(a['Error'] > 0){
				alert("Error");}
			else{
				var a = JSON.parse(localStorage.cart);
				var ans = "<table><tr><th>Product</th><th>Price</th><th>Quantity</th></tr>";
				var sum = 0;
				for (var p in a){
					ans += "<tr><td>" + a[p]['name'] +"</td><td> $"+ a[p]['price'] + " </td> <td> <input type='number' value = "+a[p]['num']+" onChange =\'inputNumber(\"" + p + "\", this.value)\'> </td> <td> <button onclick=\'event.preventDefault();removeProduct(\"" + p + "\")\'> Remove </td></tr>";
					sum += a[p]['price'] * a[p]['num'];
				}
				ans += "</table> Total: $" + sum;
				ans += "<br><input type=\"submit\" onclick=\"event.preventDefault(); ajaxSendData();\" value=\"Checkout\">";
				document.getElementById("cart-content").innerHTML = ans;
			}
		});
	}else{
		var ans = 'Empty shopping cart!';
		document.getElementById("cart-content").innerHTML = ans;
		return;
	}
}

function mySend(url, req, isGet, callBackFunc){
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
    	if (xhr.readyState === 4 && xhr.status === 200) {
      	var myArr = JSON.parse(xhr.responseText);
			if(callBackFunc){
				callBackFunc(myArr);
			}
    	}else if(xhr.readyState === 4 && xhr.status === 404){
			
		}
	};
		
	if(isGet){
		var parms = [];
		for (var p in req){
			parms.push(encodeURIComponent(p) + "=" + encodeURIComponent(req[p]));
		}
		parms =parms.join('&');
		xhr.open("GET", url + '?' + parms);
		xhr.send();
	}else{
		xhr.open("POST", url);
		xhr.setRequestHeader("Content-type", "application/json");
		req = JSON.stringify(req);
		xhr.send(req);
	}
}


function jsonPost(url, req, callBackFunc){
	mySend(url,req, false,callBackFunc);
}


function addInputField(name, value){
	"use strict";
	var input = document.createElement("input");
	input.setAttribute("type", "hidden");
	input.setAttribute("name", name);
	input.setAttribute("value", value);
	document.getElementById("cartform").appendChild(input);
}

function ajaxSendData(){
	if(localStorage.cart !== undefined && localStorage.cart !== "{}"){
		jsonPost('./php/send-ajax-data.php',{}, function(a){
			if(a['Error'] > 0){
				alert("Error");}
			else{
				var a = JSON.parse(localStorage.cart);
				var count = 1;
				for (var p in a){
					addInputField("item_name_"+count, a[p]['name']);
					addInputField("item_number_"+count, p);
					addInputField("quantity_"+count, a[p]['num']);
					addInputField("amount_"+count, a[p]['price']);
					count++;
				}
			}
		});
	}
	
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
			console.log(xmlhttp.responseText);
			var obj = JSON.parse(xmlhttp.responseText);
			var form = document.getElementById("cartform");
			form.elements.namedItem("invoice").value = obj.id;
			form.elements.namedItem("custom").value = obj.digest;
			localStorage.cart = JSON.stringify({});
			form.submit();
		}
	};
	xmlhttp.open("POST", "./php/getproducts.php", true);
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	var data = "";
	var cart = JSON.parse(localStorage.cart);
	var total = 0;
	for (var p in cart){
		data += p + "," + cart[p]['num'] + "," + cart[p]['price']+",";
		total += cart[p]['num']*cart[p]['price'];
	}
	data += total;
	xmlhttp.send("data="+data);
}
