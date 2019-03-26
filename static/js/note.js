"use strict";
const Note = function Note(){
	const Module = {},actionURL = 'action.php';
	Module.init = function init(){
		document.addEventListener("DOMContentLoaded", function() {
			const getDate = function getDate () {
				const {date,hour,min_secs,am_pm} = (()=>{
						const today = new Date(), localeOptions = { hour: 'numeric', hour12: true }, loc = 'en-US',
						localeString = (x) => {
							return today.toLocaleString(loc, localeOptions).split(" ")[x];							
						}, min_seconds_date = (c) => {
							switch(c){
								case 'min_sec':
									return today.toString().split(' ').slice(4,5).join("").split(":").slice(1,3).join(":");
									break;
								case 'date':
									return [today.getMonth()+1,today.getDate(),today.getFullYear()].join("/");
									break;
							}
						}
						return {
							hour: localeString(0),
							am_pm: localeString(1),
							min_secs: min_seconds_date('min_sec'),
							date:min_seconds_date('date'),
						}
					})();
				document.getElementsByClassName('date')[0].innerText = `${date} ${hour}:${min_secs} ${am_pm}`;
			}
			setInterval(getDate, 1000*20);
			getDate();
			Module.getListItems();
			Module.keyHandler();
			window.onhashchange = Module.hashChange;
		})
	}
	Module.getData = (filename) => {
		(async function getData(){
			const getTextData = await (function getTextData(filename) {
				return fetch(`/${actionURL}?cmd=getData&filename=${filename}`,{
					method:'GET',
				});
			})(filename !== undefined ? filename : document.getElementById('filename').value);
			let getJsonData;
			try{
				getJsonData = await getTextData.json();
				document.getElementById('filename').value = filename !== undefined ? filename : document.getElementById('filename').value;
			}catch(e){
				return;
				//throw "Response could not be JSON parsed.";
			}
			if(getJsonData.success || getJsonData.success === ""){
				document.getElementsByClassName('textarea')[0].innerText = atob(decodeURIComponent(getJsonData.success) ).trim();
			}else{
				//throw "Unable to get note.";
			}
		})();
	}
	Module.getListItems = () =>{
		const url = `/${actionURL}?cmd=getListItems`;
		(async function gl(){
			const getList = await (function fet() {
					return fetch(url,{
						method:'GET',
					});
			})();
			const result = await getList.json();
			if(result.success){
				document.querySelector('.list').innerHTML = `${JSON.parse(result.success).map((val,i)=>{
					return `<a href="/#/${val}">
								<li>
									<span id="getNote-${i}" class="getNote" data="${val}">${val}</span>
									 <i class="fa fa-pencil fa-lg edit data="${val}" ref="${i}"></i>
									<i class="fa fa-trash-o fa-lg delete" data="${val}" ref="${i}"></i>
								</li>
							</a>`;
				}).join("")}`;
				const setEvents = (() =>{
					const deleteNoteByNoteNameHandler = (() => {
						const qsa = (cl, e, ca)=>{
							[...document.querySelectorAll(cl)].map((a)=>{
								a.addEventListener(e,(e)=>{
									e.preventDefault();
									switch(ca){
										case 1:
											Module.delete(e.target.getAttribute('data'));
											break;
										case 2:
											if(e.target.tagName === "INPUT") return;
											Module.getData(e.target.getAttribute('data'));
											break;
										case 3:
											e.preventDefault();
											const ele = document.getElementById(`getNote-${e.target.getAttribute('ref')}`),
											inp = document.createElement('input');
											inp.setAttribute('value',ele.innerText);
											ele.innerHTML = inp.outerHTML;
											ele.querySelector('input').onblur = (e)=>{
												Module.save(e.target.value)
											};
											break;
									}
									
								})
							});
						};
						const cases = [
							{cl:'.delete',e:'click',ca:1},
							{cl:'.getNote',e:'click', ca:2},
							{cl:'.edit', e:'click', ca:3}
						];
						cases.map((i)=>{
							let {cl,e,ca} = i;
							qsa(cl,e,ca);
						});
					})();
					
				})();
			}else{
				throw "Unable to get List Items.";
			}
		})();
	}
	Module.delete = (filename) => {
		const url = `/${actionURL}?cmd=delete&filename=${(filename !== undefined ? filename : document.getElementById('filename').value)}`;
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
			}
		})()
	}
	Module.hashChange = (e) =>{
		const getHash = (url)=>{
			return url.slice(url.indexOf("#")+2);
		}
		const newPath = getHash(e.newURL);
		//const oldPath = getHash( e.oldURL);
		//Module.delete(oldPath);
		//Module.save(newPath);
		Module.getListItems();
	}
	Module.save = (filename) => {
		const url = `/${actionURL}?cmd=save&filename=${(filename !== undefined ? filename : document.getElementById('filename').value)}`;
		(async function sav(){
			const getData = await (function(){
				return fetch(url,{
					method:'POST',
					headers: {'Content-type':'application/x-www-form-urlencoded'},
					body: `data=${JSON.stringify({text:encodeURIComponent(btoa(document.getElementsByClassName('textarea')[0].innerText)) })}`
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
				Module.getListItems();
				if(result.warning){
					console.log(result.warning);
				}
			}
		})()
	}
	Module.keyHandler = () => {
		const timeStamps = [];
		let canSave = true;
		document.querySelector('.note').onkeydown = function (e) {
			timeStamps.push(e.timeStamp);
			const diff = (timeStamps.slice(-1) - timeStamps[timeStamps.length-2])/1000;
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
	}
	return {
		init:Module.init,
		allModules: Module
	}
}
window._Note = Note();
_Note.init();