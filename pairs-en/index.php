<!DOCTYPE html>
<html>

<head>

<title>Test IT MNF - PAIRS</title>

<style>

body {
    margin: 40px;
}
.contentdiv {
    width: 500px;
}
.contentdiv textarea {
    width: 100%;
}
.contentdiv button {
    margin-top: 10px;
    width: 100%;
}
.contentdiv h4 {
    margin-top: 20px;
    width: 100%;
}

</style>

<script>
let pairstextarea;
let pairsresult;

document.addEventListener("DOMContentLoaded", function() {
    pairstextarea = document.getElementById("pairstextarea");
    pairsresult = document.getElementById("pairsresult");
});

function match(v1, v2) {
    let char1 = v1.toLowerCase().charCodeAt(0);
    let char2 = v2.toLowerCase().charCodeAt(0);
    if(char1 >= 97 && char2 >= 97 && char1 <= 122 && char2 <= 122 && char1 + char2 == 219) 
        return true;
    return false;
}

function cleanPairs(str) {
    const len = str.length;

    var i;
    for(i = 0; i < Math.floor(len / 2); i++) {
        if(!match(str[len - i - 1], str[i]))
            break;
    }

    if(i > 0)
        return str.slice(i, -i);

    return str;
}

function cleanPairs_click() {
    var strs;
    let results = [];

    strs = pairstextarea.value.split('\n');
    for(var str of strs) {
        results.push(cleanPairs(str));   
    }

    pairsresult.innerHTML = results.join('\n');
}

</script>

</head>

<body>

<h2>Welcome to TEST IT MNF</h2>

<div class="contentdiv">

    <textarea name="pairstextarea" id="pairstextarea" placeholder="Write your strings here..." rows="6"></textarea>

    <button id="pairsssubmit" onclick="cleanPairs_click()"> Clean pairs </button>

    <h4> Results </h4>
    
    <pre id="pairsresult"></pre>

</div>

</body>

</html>