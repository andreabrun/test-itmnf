<!DOCTYPE html>
<html>

<head>

<title>Test IT MNF - BRACKETS</title>

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
var cleaned = false;
let bracketstextarea;
let bracketsresult;

document.addEventListener("DOMContentLoaded", function() {
    bracketstextarea = document.getElementById("bracketstextarea");
    bracketsresult = document.getElementById("bracketsresult");

    bracketstextarea.addEventListener("input", () => cleaned = false);
});

function cleanBrackets(str) {
    const len = str.length;

    if(len == 0 || (str[0] != '(' || str[len-1] != ')')) return str;

    var opened = -1;
    var count = 0;
    for (let i = 0; i < len; i++) {

        if(opened == -1) {
            if(str[i] != '(') {
                opened = i;
            } else {
                continue;
            }
        }

        if(str[i] == '(') count--;
        if(str[i] == ')') count++;

        if(count > 0 && i < len - opened) {
            opened -= 1;
            count = 0;

            // In this case there are no more initial open brackets that need to be checked
            if(opened <= 0) break;
        }
    }

    // Remove the external brackets only if they are balanced
    if(opened > 0 && opened == count) {
        return str.slice(opened, -count);
    }

    return str;
}

function cleanBrackets_click() {
    var strs;
    let results = [];

    strs = bracketstextarea.value.split('\n');
    for(var str of strs) {
        results.push(cleanBrackets(str));
    }

    bracketsresult.innerHTML = results.join('\n');
    cleaned = true;
}

function generatePDF() {
    var strs = bracketsresult.innerHTML.split('\n');

    fetch("../utils/printpdf.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(strs)
    }).then(res => res.text())
    .then(res => document.getElementById("generatepdfresult").innerHTML = res);
}

function generatePDF_click() {
    if(cleaned) {
        generatePDF();
    } else {
        alert("Input strings not cleaned!");
    }
}


</script>

</head>

<body>

<h2>Welcome to TEST IT MNF</h2>

<div class="contentdiv">

    <textarea name="bracketstextarea" id="bracketstextarea" placeholder="Write your strings here..." rows="6"></textarea>

    <button id="bracketsssubmit" onclick="cleanBrackets_click()"> Clean brackets </button>

    <h4> Results </h4>
    
    <pre id="bracketsresult"></pre>

    <button id="generatepdfbutton" onclick="generatePDF_click()"> Generate PDF </button>

    <pre id="generatepdfresult"></pre>

</div>

</body>

</html>