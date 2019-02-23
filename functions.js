document.addEventListener("DOMContentLoaded", function() {

	document.getElementsByClassName('active')[0].addEventListener('click',()=>{
		var http = new XMLHttpRequest();
		var url = '/action.php?cmd=save&filename='+document.getElementById('filename').value;;
		http.open('POST', url, true);

		//Send the proper header information along with the request
		http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

		http.onreadystatechange = function() {//Call a function when the state changes.
		    if(http.readyState == 4 && http.status == 200) {
			console.log(http.responseText);
			try{
			    var filename = JSON.parse(http.responseText).success;
			    if(filename.indexOf('.txt') !== -1){
				document.getElementById('filename').value = filename;	
			    }
			}catch(e){

			}
		    }
		}
		http.send('data='+JSON.stringify({text:encodeURIComponent(document.getElementsByTagName('textarea')[0].value)}));
		});	

 	  
	if(window.location.search.replace('?', '').split('=')[0] === 'note' ){
    	    document.getElementById('filename').value = "./notes/"+window.location.search.replace('?', '').split('=')[1]+".txt"
	    getTextData(document.getElementById('filename').value);
	    function getTextData( filename ){
		var http = new XMLHttpRequest();
	  	http.open('GET', '/action.php?cmd=getData&filename='+filename, true);
		http.onreadystatechange = function() {//Call a function when the state changes.
		    if(http.readyState == 4 && http.status == 200) {
			console.log(http.responseText);
			try{
			    var data = JSON.parse(http.responseText).success;
			    document.getElementsByTagName('textarea')[0].value = data;	
			}catch(e){

			}
		    }
		}
		http.send('data='+JSON.stringify({text:encodeURIComponent(document.getElementsByTagName('textarea')[0].value)}) ) ;
	    }
    	}



	lastKeyPress = []
	//when key is pressed...
	document.onkeydown = function(e) {

	    e = e || window.event;

	    switch (e.keyCode) {

	    case 13:
		//'enter' is pressed
		//todo code
		break;
	    case 83:
		//'c' is pressed
		if (lastKeyPress[lastKeyPress.length - 1] == 'Control') {
		    setTimeout(function() {//todo code when alt + ctrl + c is pressed
		        console.log('saving');
			document.getElementsByClassName('active')[0].click();
			lastKeyPress = [];
		    }, 500);
		}
	    }

	    //push key name into array
	    lastKeyPress.push(e.key)

	    //clear array or leave put
	}



	getDate();
	setInterval(getDate, 750);
	function getDate () {

		var today = new Date();
		document.getElementsByClassName('date')[0].innerText =  [today.getMonth(),today.getDate(),today.getFullYear()].join("/") + " " + today.toString().split(' ').slice(4,6).join(" ");
	}
});
