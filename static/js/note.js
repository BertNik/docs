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
			const params = new URLSearchParams(window.location.search);
			if (params.has('note')) {
				document.getElementById('filename').value = params.get('note');
				Module.getData();
			}
			window.onhashchange = Module.hashChange;
		})
	}
	Module.getData = (filename) => {
		(async function getData(){
			const getTextData = await (function getTextData(filename) {
				return fetch('/action.php?cmd=getData&filename=' + filename,{
					method:'GET',
				});
			})(filename !== undefined ? filename : document.getElementById('filename').value);
			let getJsonData;
			try{
				getJsonData = await getTextData.json();
				document.getElementById('filename').value = filename !== undefined ? filename : document.getElementById('filename').value;
			}catch(e){
				throw "Response could not be JSON parsed.";
			}
			if(getJsonData.success || getJsonData.success === ""){
				document.getElementsByClassName('textarea')[0].innerText = atob(getJsonData.success).trim();
			}else{
				//throw "Unable to get note.";
			}
		})();

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
					return `<a href="/#/${val}"><li><span class="getNote" data="${val}">${val}</span><i class="fa fa-trash-o fa-lg delete" data="${val}"></i></li></a>`;
				}).join("")}`;
				[...document.querySelectorAll('.getNote')].map((a)=>{
					a.addEventListener('click',(e)=>{
						Module.getData(e.target.getAttribute('data'));

					})
				});
				[...document.querySelectorAll('.delete')].map((a)=>{
					a.addEventListener('click',(e)=>{
						e.preventDefault();
						Module.delete(e.target.getAttribute('data'));
					})
				});
			}else{
				throw "Unable to get List Items.";
			}
		})();
	}
	Module.delete = (filename) => {
		const url = '/action.php?cmd=delete&filename='+ (filename !== undefined ? filename : document.getElementById('filename').value);
		(async function del(){
			const getData = await (function(){
				return fetch(url,{
					method:'DELETE',
					headers: {'Content-type':'application/x-www-form-urlencoded'},
				})
			})();
			let result;
			try{
				result = await getData.json();
			}catch(e){
				throw "Result could not be JSON parsed."; 
			} 
			if(result.success){
				document.getElementById('filename').value = result.success;
				if(result.warning){
					console.log(result.warning);
				}
				Module.getListItems();
			}else if(false && !noQueryParams && !queryParams.has("note")){
				throw "Unable to get file.";
			}
		})()
	}
	Module.hashChange = (e) =>{
		const getHash = (url)=>{
			return url.slice(url.indexOf("#")+2);
		}
		const newPath = getHash(e.newURL);
		const oldPath = getHash( e.oldURL);
		//Module.delete(oldPath);
		Module.save(newPath);
		Module.getListItems();
	}
	Module.save = (filename) => {
		const url = '/action.php?cmd=save&filename='+ (filename !== undefined ? filename : document.getElementById('filename').value);
		(async function sav(){
			const getData = await (function(){
				return fetch(url,{
					method:'POST',
					headers: {'Content-type':'application/x-www-form-urlencoded'},
					body: `data=${JSON.stringify({text:btoa(document.getElementsByClassName('textarea')[0].innerText)})}`
				})
			})();
			let result;
			const queryParams = new URLSearchParams(window.location.search), noQueryParams = queryParams.entries().next().done;
			try{
				result = await getData.json();
			}catch(e){
				throw "Result could not be JSON parsed."; 
			} 
			if(result.success){
				document.getElementById('filename').value = result.success;
				Module.getListItems();
				if(result.warning){
					console.log(result.warning);
				}
			}else if(!noQueryParams && !queryParams.has("note")){
				throw "Unable to get file.";
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
				})() : undefined;
			
		}
		//document.onkeydown(new Event('onkeydown'));

	})()
	return {init:Module.init}
}
window._Note = Note();
_Note.init();