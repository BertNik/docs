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
		let timeStamps = [], canSave = true;
		document.onkeydown = function (e) {
			timeStamps.push(e.timeStamp);
			let diff = (timeStamps.slice(-1) - timeStamps[timeStamps.length-2])/1000;
			diff > .5 || canSave ? (async function a(){
					canSave = false;
					const p = await (function(){
						return new Promise ((res,rej)=>{
							setTimeout(()=>{
								res(Module.save());
							},1000);
						}); 
					})()
					timeStamps.splice(0,timeStamps.length-2);
					canSave = true;
				})() : '';
			
		}
		document.onkeydown(new Event('onkeydown'));

	})()
	return {init:Module.init}
}
window._Note = Note();
_Note.init();