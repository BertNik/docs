"use strict";
const Note = function Note(){
	const Module = {};
	Module.init = function(){
		return document.addEventListener("DOMContentLoaded", function() {
			const getDate = function getDate () {
				const today = new Date();
				document.getElementsByClassName('date')[0].innerText =  [today.getMonth(),today.getDate(),today.getFullYear()].join("/") + " " + today.toString().split(' ').slice(4,6).join(" ");
			}
			setInterval(getDate, 1000*20);
			getDate();
			Module.getListItems();
			let params = new URLSearchParams(window.location.search);
			if (params.has('note')) {
				document.getElementById('filename').value = params.get('note');
				(async function getData(){
					const getTextData = await (function getTextData(filename) {
						return fetch('/action.php?cmd=getData&filename=' + filename,{
							method:'GET',
						});
					})(document.getElementById('filename').value)
					const getJsonData = await getTextData.json();
					if(getJsonData.success || getJsonData.success === ""){
						document.getElementsByClassName('textarea')[0].innerText = atob(getJsonData.success);
					}else{
						throw "Unable to get note.";
					}
				})();
			}

		})
	}
	
	Module.getListItems = () =>{
		const url = '/action.php?cmd=getListItems';
		(async function gl(){
			const getList = await (function() {
					return fetch(url,{
						method:'GET',
					});
			})();
			const result = await getList.json();
			if(result.success){
				document.querySelector('.list').innerHTML = `${JSON.parse(result.success).map((val)=>{
					return `<a href="/?note=${val.replace(".txt","")}"><li>${val}</li></a>`;
				}).join("")}`;
			}else{
				throw "Unable to get List Items.";
			}
		})();
	}
	Module.save = () => {
		const url = '/action.php?cmd=save&filename='+document.getElementById('filename').value;
		(async function sav(){
			const getData = await (function(){
				return fetch(url,{
					method:'POST',
					headers: {'Content-type':'application/x-www-form-urlencoded'},
					body: `data=${JSON.stringify({text:btoa(document.getElementsByClassName('textarea')[0].innerText)})}`
				})
			})();
			const result = await getData.json();
			if(result.success){
				document.getElementById('filename').value = result.success;
			}else{
				throw "Unable to save file.";
			}
		})()
	}

	Module.keyHandler = (() => {
		let lastKeyPress = [];
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
							Module.save();
							lastKeyPress = [];
						}, 500);
					}
			}
			//push key name into array
			lastKeyPress.push(e.key);
			//clear array or leave put
		}
	})()
	return {init:Module.init}
}
window._Note = Note();
_Note.init();