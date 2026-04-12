<!DOCTYPE html>
<html>
<head>
<title>AMS - About</title>

<!-- css link -->
<link rel="stylesheet" href="assets/css/style.css">

<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Arial;
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

.nav-links {
    display: flex;
    align-items: center;
}

.nav-links a {
    margin: 0 10px;
    text-decoration: none;
    color: black;
    font-weight: 600;
    padding: 6px 10px;
    border-radius: 6px;
    transition: 0.3s;
}

.nav-links a:hover {
    background: #a5abb3;
}

/* DROPDOWN */
.dropdown {
    position: relative;
    display: flex;
    align-items: center;
}

.dropdown-content {
    display: none;
    position: absolute;
    background: transparent;
    min-width: 150px;
    top: 100%;
    left: 0;
    z-index: 1000;
}

.dropdown-content a {
    display: block;
    padding: 10px;
    margin: 5px 0;
    background: white;
    border-radius: 8px;
    text-align: center;
}

.dropdown-content a:hover {
    background: #dcdcdc;
}

.dropdown:hover .dropdown-content {
    display: block;
}

/* MAIN CONTAINER */
.container {
    display: flex;
    justify-content: center;
    padding: 60px 20px;
}

/* MAIN CARD */
.card {
    width: 800px;
    background: #3A4759;
    color: white;
    padding: 50px;
    border-radius: 25px;
}

/* TEXT */
.card p {
    line-height: 1.7;
    margin-bottom: 20px;
}

/* FEATURES GRID */
.features {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 15px;
    margin-top: 20px;
}

.box {
    background: #626C7B;
    padding: 15px 20px;
    border-radius: 15px;
    min-width: 180px;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    transition: 0.3s;
}

.box:hover {
    transform: translateY(-3px);
}

/* VISION BOX */
.vision {
    background: #626C7B;
    padding: 20px;
    border-radius: 15px;
    margin-top: 30px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

/* DEVELOPERS */
.dev-container {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    grid-gap: 10px; 
    margin-top: 20px;
    justify-items: center;
}

.dev {
    background: #626C7B;
    padding: 10px 18px;
    border-radius: 20px;
    box-shadow: 0 3px 6px rgba(0,0,0,0.3);
    text-align: center;
    width: 180px;
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

<!-- CONTENT -->
<div class="container">

<div class="card">

<h2>About the Project</h2>

<p>
The Academic Management System is a web-based platform designed and developed by students to make academic activities easier and more organized. This system helps manage student records, courses, attendance, and grades in a simple and efficient way.
</p>

<p>
Our main goal is to reduce manual work and provide a digital solution for handling academic data. With this system, students and teachers can easily access important information anytime and from anywhere.
</p>

<!-- FEATURES -->
<div class="features">
    <div class="box">Student Record Management </br> <p> Manage student records </br> and profiles </p> </div>
    <div class="box">Course Handling </br> <p> Organize courses, subjects </br> and schedules </p> </div>
    <div class="box">Result Management </br> <p> Record, update, and analyze </br> student grades </p> </div>
    <div class="box">Secure Data Storage </br> <p> Protect academic data with </br> safe and reliable storage </p> </div>
</div>

<!-- VISION -->
<div class="vision">
<h2>Our Vision</h2>
    Our vision is to simplify and enhance academic management through smart, secure, and </br> user-friendly technology, creating a better experience for students, teachers, and </br> institutions.
</div>

<!-- DEVELOPERS -->
<h2 style="margin-top:40px;">Developed By:</h2>

<div class="dev-container">
    <div class="dev">Diya Bishwokarma</div>
    <div class="dev">Upashana Upreti</div>
    <div class="dev">Jasmine Rai</div>
    <div class="dev">Chetan Singh Thakuri</div>
    <div class="dev">Sujan Chand Thakuri</div>
    <div class="dev">Shirish Gurung</div>
</div>

</div>
</div>

</body>
</html>
