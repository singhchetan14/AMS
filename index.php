<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
<title>AMS - Home</title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">

<!--  CSS LINK -->
<link rel="stylesheet" href="assets/css/style.css">

<style>
body {
margin: 0;
font-family: Arial;
background: #0A1931;
}
/* NAVBAR */
.navbar {
background: #8e949c;
padding: 15px 40px;
display: flex;
justify-content: space-between;
align-items: center;
}
.logo {
font-weight: bold;
font-size: 18px;
}
/* NAV LINKS */
.nav-links {
display: flex;
align-items: center;
}
/* ALL LINKS SAME STYLE */
.nav-links a {
margin: 0 10px;
text-decoration: none;
color: black;
font-weight: bold;
padding: 6px 10px;
border-radius: 6px;
transition: 0.3s;
}
/* HOVER */
.nav-links a:hover {
background: #a5abb3;
}
/* DROPDOWN FIX */
.dropdown {
position: relative;
display: flex;
align-items: center;
}
/* DROPDOWN MENU */
.dropdown-content {
display: none;
position: absolute;
background: transparent;
min-width: 150px;
top: 100%;
left: 0;
z-index: 1000;
}
/* EACH ITEM AS BOX */
.dropdown-content a {
display: block;
padding: 10px;
margin: 5px 0;
background: white;
border-radius: 8px;
text-align: center;
transition: 0.3s;
}
/* HOVER BOX */
.dropdown-content a:hover {
background: #dcdcdc;
}
.dropdown:hover .dropdown-content {
display: block;
}
/* MAIN */
.container {
padding: 60px;
display: flex;
justify-content: center;
}
/* CARD */
.card {
width: 750px;
min-height: 500px;
background: #3A4759;
color: white;
padding: 60px;
border-radius: 25px;
display: flex;
justify-content: space-between;
}

/*image*/
.image {
    align-items: center;
    height: 78%;
    display: flex;
    
}

/* TEXT */
.card p {
margin-top: 15px;
margin-bottom: 25px;
line-height: 1.6;
}
/* BUTTONS */
.btn {
padding: 10px 20px;
border: 1px solid white;
background: transparent;
color: white;
border-radius: 20px;
margin-right: 10px;
cursor: pointer;
transition: 0.3s;
}
.btn:hover {
background: white;
color: black;
}

/* ONLY CHANGE: h1 font and size */
h1 {
font-family: 'Playfair Display', serif;
font-size: 2.8rem;
font-weight: 400;
line-height: 1.2;
}
</style>
</head>
<body>
<!-- NAVBAR -->
<div class="navbar">
<div class="logo">Academic Management System</div>
<div class="nav-links">
<a href="index.php">Home</a>
<a href="about.php">About</a>

<div class="dropdown">
<a href="#">Login</a>
<div class="dropdown-content">
<a href="#">Teacher</a>
<a href="#">Student</a>
</div>
</div>

<a href="#">Sign Up</a>
</div>
</div>

<!-- MAIN -->
<div class="container">
<div class="card">
<div>
<h1>Streamline Your Academic </br>
    Experience</h1>
<p>
Manage student records, grades and courses – all in </br>
one intelligent platform.Built by students, for smarter </br>
institutions.
</p>
<button class="btn" onclick="location.href='about.php'">Learn More</button>
<button class="btn" onclick="location.href='auth/login.php'">Get Started</button>
</div>
<div class="image">
<img src="https://cdn-icons-png.flaticon.com/512/3135/3135755.png" width="150">
</div>
</div>
</div>
</body>
</html>
