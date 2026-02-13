<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Selenium Test Lab</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f9f9f9; }
        .section { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .hidden-element { display: none; color: blue; font-weight: bold; }
        .disabled-input { background-color: #eee; cursor: not-allowed; }
    </style>
</head>
<body>

<h1>Selenium Automation Test Lab</h1>

<div class="section" id="text-inputs">
    <h3>1. Text & Password Inputs</h3>
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" placeholder="Type something...">
    <br><br>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password">
    <br><br>
    <label for="bio">Bio (Textarea):</label><br>
    <textarea id="bio" rows="3"></textarea>
</div>

<div class="section" id="selection-controls">
    <h3>2. Radio & Checkboxes</h3>
    <p>Choose your favorite fruit:</p>
    <input type="radio" name="fruit" value="apple" id="apple"> <label for="apple">Apple</label>
    <input type="radio" name="fruit" value="orange" id="orange"> <label for="orange">Orange</label>
    
    <p>Interests:</p>
    <input type="checkbox" id="coding" name="interest" value="coding"> <label for="coding">Coding</label>
    <input type="checkbox" id="music" name="interest" value="music"> <label for="music">Music</label>
</div>

<div class="section">
    <h3>3. Select Menus (Dropdowns)</h3>
    <label for="cars">Choose a car:</label>
    <select id="cars" name="cars">
        <option value="volvo">Volvo</option>
        <option value="saab">Saab</option>
        <option value="mercedes">Mercedes</option>
        <option value="audi">Audi</option>
    </select>
</div>

<div class="section">
    <h3>4. Button States & JS Alerts</h3>
    <button id="alert-btn" onclick="alert('Hello from Selenium!')">Click for Alert</button>
    <button id="disabled-btn" disabled>I am Disabled</button>
</div>

<div class="section">
    <h3>5. Delayed Loading (Test Waits)</h3>
    <button id="loader-trigger" onclick="showAfterDelay()">Click to reveal message in 3 seconds</button>
    <p id="delayed-text" class="hidden-element">✅ I appeared after 3 seconds!</p>
</div>

<script>
    function showAfterDelay() {
        setTimeout(function() {
            document.getElementById('delayed-text').style.display = 'block';
        }, 3000);
    }
</script>

</body>
</html>