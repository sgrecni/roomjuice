function checkAll(f) {
    for(i = 0; i < f.length; i++) {
        f.elements[i].checked = checkflag;
    }
    if(checkflag == false) {
        checkflag = true;
        return "check all";
    } else {
        checkflag = false;
        return "uncheck all";
    }
}

function displayTime(mode){
    if (!document.layers && !document.all && !document.getElementById) {
        return;
    }

    var curr = new Date();
    var currentSeconds = curr.getHours()*3600+curr.getMinutes()*60+curr.getSeconds();

    var countdownSeconds = times[mode];

    var remainSeconds = countdownSeconds - currentSeconds;
    var hoursLong = remainSeconds / 3600;
    var hours = Math.floor(hoursLong);
    var minsLong = (hoursLong - hours)*60;
    var mins = Math.floor(minsLong);
    var secs = Math.floor((minsLong - mins) * 60);

    window.setTimeout("displayTime('"+ mode +"')", 500);
    output = "";
    if(remainSeconds < 0) {
        output = "0:00";
    } else {
        if (hours > 0) {
            output = hours + ":";
            if (mins < 10) {
                output += "0";
            }
        }
        output += mins +":";
        if (secs < 10) {
            output += "0";
        }
        output = output + secs;
    }
//    if(document.getElementById("countdown")) {
//        document.getElementById("countdown").innerHTML = output;
//    }
    if(document.getElementById(mode)) {
        document.getElementById(mode).innerHTML = output;
    }
}

var times = new Array();
function countdown(mode, secs) {
    var curr = new Date();
    var countdownSeconds = curr.getHours()*3600+curr.getMinutes()*60+curr.getSeconds();
    countdownSeconds += secs;

    times[mode] = countdownSeconds;
    displayTime(mode);
}

function display(filename) {
//    alert(filename);
	window.open('display.php?filename=' + URLEncode(filename), 'rjdisplay',
		'width=680,height=480,location=no,resizable=yes,status=no,scrollbars=yes,toolbar=no');
}


function URLEncode (clearString) {
    var output = '';
    var x = 0;
    clearString = clearString.toString();
    var regex = /(^[a-zA-Z0-9_.]*)/;
    while (x < clearString.length) {
        var match = regex.exec(clearString.substr(x));
        if (match != null && match.length > 1 && match[1] != '') {
            output += match[1];
            x += match[1].length;
        } else {
            if (clearString[x] == ' ') {
                output += '+';
            } else {
                var charCode = clearString.charCodeAt(x);
                var hexVal = charCode.toString(16);
                output += '%' + ( hexVal.length < 2 ? '0' : '' ) + hexVal.toUpperCase();
            }
            x++;
        }
    }
    return output;
}

function URLDecode (encodedString) {
    var output = encodedString;
    var binVal, thisString;
    var myregexp = /(%[^%]{2})/;
    while ((match = myregexp.exec(output)) != null && match.length > 1 && match[1] != '') {
        binVal = parseInt(match[1].substr(1),16);
        thisString = String.fromCharCode(binVal);
        output = output.replace(match[1], thisString);
    }
    return output;
}
