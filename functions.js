document.addEventListener("DOMContentLoaded", function() {
	document.getElementsByClassName('active')[0].addEventListener('click',()=>{
		var url = '/action.php?cmd=save&filename='+document.getElementById('filename').value;
		fetch(url,{
			method:'POST',
			headers: {'Content-type':'application/x-www-form-urlencoded'},
			body: `data=${JSON.stringify({text:btoa(document.getElementsByTagName('textarea')[0].value)})}`
		}).then((...t)=>{
			console.log(t);
			t[0].json().then((a)=>{
				if(a.success){
					var filename = a.success;
					if(filename.indexOf('.txt') !== -1){
						document.getElementById('filename').value = filename;	
					}
				}
			});
			
		});
	});
	let params = new URLSearchParams(window.location.search);
	if (params.has('note')) {
		document.getElementById('filename').value = "./notes/" + params.get('note') + ".txt"
		getTextData(document.getElementById('filename').value);
		function getTextData(filename) {
			fetch('/action.php?cmd=getData&filename=' + filename,{
				method:'GET',
				headers: {'Content-type':'application/x-www-form-urlencoded'}
			}).then((...t)=>{
				t[0].json().then((a)=>{
					if(a.success){
						let data = a.success;
						document.getElementsByTagName('textarea')[0].value = atob(data);
					}
				});
			});
		}
	}

	getDate();
	setInterval(getDate, 750);
	function getDate () {
		var today = new Date();
		document.getElementsByClassName('date')[0].innerText =  [today.getMonth(),today.getDate(),today.getFullYear()].join("/") + " " + today.toString().split(' ').slice(4,6).join(" ");
	}

});

lastKeyPress = []
//when key is pressed...
document.onkeydown = function (e) {

	e = e || window.event;

	switch (e.keyCode) {
		case 13:
			//'enter' is pressed
			//todo code
			break;
		case 67:
			//'c' is pressed
			if (lastKeyPress[lastKeyPress.length - 1] == 'Control') {
				setTimeout(function () {//todo code when alt + ctrl + c is pressed
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

