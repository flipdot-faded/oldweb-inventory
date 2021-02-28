var root = "/inventar";

var W3CDOM = (document.createElement && document.getElementsByTagName);

function loadXMLDoc(link, callback) {
	var xmlhttp;
	if (window.XMLHttpRequest) {
		xmlhttp=new XMLHttpRequest();
	} else {
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			callback.call(xmlhttp.responseText);
		}
	}
	xmlhttp.open("GET",link,true);
	xmlhttp.send();
}

function initFileUploads() {
	if (!W3CDOM) return;
	var fakeFileUpload = document.createElement('div');
	fakeFileUpload.className = 'fakefile';
	fakeFileUpload.appendChild(document.createElement('input'));
	var p = document.createElement('p');
	fakeFileUpload.appendChild(p);
	var x = document.getElementsByTagName('input');
	for (var i=0;i<x.length;i++) {
		if (x[i].type != 'file') continue;
		if (x[i].parentNode.className != 'fileinputs') continue;
		x[i].className = 'file hidden';
		var clone = fakeFileUpload.cloneNode(true);
		x[i].parentNode.appendChild(clone);
		x[i].relatedElement = clone.getElementsByTagName('input')[0];
		x[i].onchange = x[i].onmouseout = function () {
			// cut off the chromium garbage
			var tmp = this.value.split("\\");
			this.relatedElement.value = tmp[tmp.length-1];
		}
	}
}

function submitForm() {
	document.getElementById("submitButton").disabled = true;
	document.getElementById("submitButton").style.display = 'none';
	document.getElementById("throbber").style.display = 'block';
}

function showDeleteButton() {
	document.getElementById("deletebox").style.display = 'block';
} 

function getNewID (cb) {
	hint = document.getElementById("newidhint");
	if (cb.checked) {
		// get new value
		loadXMLDoc(root+"/getnewid", function() {
			document.getElementsByName("id")[0].value = this;
		});
		hint.style.display = 'block';
	} else {
		// reset to original value
		document.getElementsByName("id")[0].value = document.getElementsByName("oldid")[0].value
		hint.style.display = 'none';
	}
}

function isNumber(n) {
	return !isNaN(parseFloat(n)) && isFinite(n);
}

function checkLending (input) {
	if (input.value == "n") {
		// show number input
		document.getElementById("numberinput").style.display = "block";
		document.getElementById("lending").value = 7;
		document.getElementById("lendingdays").value = 7;
	} else {
		// hide number input
		document.getElementById("numberinput").style.display = "none";
		document.getElementById("lending").value = input.value;
	}
}

function checkNumDays (input) {
	if (isNumber(input.value)) {
		number = parseInt(input.value,10);
		if (number > 356) number = 356;
		// set hidden variable
		document.getElementById("lending").value = number;
		// check for days / months for display
		if(number > 30) {
			input.value = Math.round(number / 30);
			document.getElementById("lendingdaysdisplay").innerHTML = "Monate";
		} else {
			input.value = number;
			document.getElementById("lendingdaysdisplay").innerHTML = "Tage";
		}
	} else {
		// reset to original value
		input.value = 0;
	}
}

function removeURL (n) {
	if (n == 0) {
		// clear first inputs
		document.getElementById("url0title").value = "";
		document.getElementById("url0url").value = "";
	} else {
		var node = document.getElementById("url"+n);
		if (node.parentNode) {
			node.parentNode.removeChild(node);
		}
		//document.getElementById("url"+n).style.display = "none";
		//document.getElementById("urlwrap").removeChild("url"+n)
	}
	
}

function addURL () {
	var wrap = document.getElementById("urlwrap");
	var n = parseInt(wrap.lastChild.lastChild.id.replace(/url/g,""),10)+1;
	
	// add the new node
	var newli = document.createElement('li');
	newli.setAttribute('id','url'+n);
	newli.setAttribute('class','multi');
	
	newli.innerHTML = '<a onclick="removeURL('+n+')" class="img_minus"></a><input type="text" placeholder="Titel #'+(n+1)+'" id="url'+n+'title" name="url-titles[t'+n+']"><input type="text" placeholder="URL #'+(n+1)+'" id="url'+n+'url" name="url-urls[t'+n+']">';
	
	wrap.appendChild(newli);
}
