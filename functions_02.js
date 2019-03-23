"use strict";
document.addEventListener("DOMContentLoaded", function() {
    const getDate = function getDate () {
        const today = new Date();
        document.getElementsByClassName('date')[0].innerText =  [today.getMonth(),today.getDate(),today.getFullYear()].join("/") + " " + today.toString().split(' ').slice(4,6).join(" ");
    }
    setInterval(getDate, 1000*20);
    getDate();
	getListItems();
    let params = new URLSearchParams(window.location.search);
	if (params.has('note')) {
		document.getElementById('filename').value = params.get('note');
		getTextData(document.getElementById('filename').value);
		function getTextData(filename) {
			fetch('/action.php?cmd=getData&filename=' + filename,{
				method:'GET',
				headers: {'Content-type':'application/x-www-form-urlencoded'}
			}).then((...t)=>{
				t[0].json().then((a)=>{
					if(a.success){
						let data = a.success;
						document.getElementsByClassName('textarea')[0].innerText = atob(data);
					}
				});
			});
		}
    }
    
})
const getListItems = () =>{
    const url = '/action.php?cmd=getListItems';
    fetch(url,{
        method:'GET',
    }).then((...t)=>{
        t[0].json().then((a)=>{
            if(a.success){
            	document.querySelector('.list').innerHTML = `${JSON.parse(a.success).map((val)=>{
            		return `<a href="/?note=${val.replace(".txt","")}"><li>${val}</li></a>`;
            	}).join("")}`;
            }
        });
    });
}
const save = () => {
    const url = '/action.php?cmd=save&filename='+document.getElementById('filename').value;
    fetch(url,{
        method:'POST',
        headers: {'Content-type':'application/x-www-form-urlencoded'},
        body: `data=${JSON.stringify({text:btoa(document.getElementsByClassName('textarea')[0].innerText)})}`
    }).then((...t)=>{
        console.log(t);
        t[0].json().then((a)=>{
            if(a.success){
                document.getElementById('filename').value = a.success;
            }
        });
    });
}

const keyHandler = (() => {
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
                        save();
                        lastKeyPress = [];
                    }, 500);
                }
        }
        //push key name into array
        lastKeyPress.push(e.key);
        //clear array or leave put
    }
})()