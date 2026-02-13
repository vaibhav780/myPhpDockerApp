<!DOCTYPE html>
<html>
<head><title>JavaScript Alerts</title></head>
<body>
    <h3>JavaScript Alerts</h3>
    <p>Click for a JS Alert, Confirm, or Prompt.</p>
    
    <button onclick="jsAlert()">Click for JS Alert</button>
    <button onclick="jsConfirm()">Click for JS Confirm</button>
    <button onclick="jsPrompt()">Click for JS Prompt</button>

    <p id="result" style="color: blue; font-weight: bold;"></p>

    <script>
        function jsAlert() { alert("I am a JS Alert"); document.getElementById('result').innerHTML = "You successfully clicked an alert"; }
        function jsConfirm() { 
            let res = confirm("I am a JS Confirm");
            document.getElementById('result').innerHTML = res ? "You clicked: Ok" : "You clicked: Cancel";
        }
        function jsPrompt() {
            let res = prompt("I am a JS prompt");
            document.getElementById('result').innerHTML = "You entered: " + res;
        }
    </script>
</body>
</html>