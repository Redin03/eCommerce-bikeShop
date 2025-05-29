<?php
// Start the session
session_start();
// Require the database configuration file
require_once __DIR__ . '/../config/db.php'; // Adjust path based on your directory structure
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bong Bicycle Shop</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="../assets/images/favicon/favicon.svg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
        crossorigin="anonymous">
  <style>
    .section-icon {
        font-size: 2.5rem;
        color: var(--secondary);
        margin-bottom: 15px;
    }
    .img-fluid.rounded-start.h-100.object-cover {
        object-fit: cover;
        width: 100%;
    }
  </style>
</head>
<body>
<?php include 'navigation.php'; ?>


<div class="bg-image p-5 text-center shadow-sm" style="background-image: url('../assets/images/content-image/basic-cycling-skills-banner.png'); background-size: cover; background-position: center;">
 <div class="mask" style="background-color: rgba(0, 0, 0, 0.6);">
  <div class="d-flex justify-content-center align-items-center h-100">
   <div class="text-white">
    <h2 class="mb-4" style="font-weight:700;">Basic Cycling Skills: Your Foundation for Every Ride</h2>
    <p class="mb-5" style="max-width:600px; margin:auto;">
   Whether you're new to cycling or getting back on the bike after some time, mastering basic skills is essential for a safe, comfortable, and confident riding experience in any environment.
    </p>
   </div>
  </div>
 </div>
</div>


<div class="container my-5">
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="card mb-5 border-0 shadow-sm">
                <div class="row g-0">
                    <div class="col-md-5">
                        <img src="../assets/images/content-image/learn-basic-skills.png" class="img-fluid rounded-start h-100 object-cover" alt="Cyclist learning basic skills">
                    </div>
                    <div class="col-md-7">
                        <div class="card-body p-4">
                            <h3 class="card-title" style="color: var(--primary);"><i class="bi bi-info-circle me-2"></i>Why Basic Skills Matter: Beyond Just Pedaling</h3>
                            <p class="card-text" style="color: var(--text-dark);">
                                Fundamental cycling skills aren't just for beginners; they are the bedrock upon which all advanced riding techniques are built. They enhance your control, improve your reaction time, and significantly boost your confidence, allowing you to enjoy your rides more fully and safely. From navigating busy streets to enjoying serene bike paths, a solid foundation makes all the difference. Mastering these basics early on prevents bad habits and makes learning more complex maneuvers much easier. A strong grasp of these skills ensures you can handle unexpected situations, respond effectively to traffic, and maintain stability on varying surfaces, ultimately leading to a more joyful and less stressful cycling experience.
                            </p>
                            <p class="card-text"><small class="text-muted">Building confidence on two wheels starts here.</small></p>
                        </div>
                    </div>
                </div>
            </div>

            <h2 class="mb-4 text-center" style="font-weight:700; color: var(--primary);">Key Skills to Master for Confident Cycling</h2>
            <hr class="mb-5" style="border-top: 3px solid var(--secondary); width: 100px; margin: auto;">

            <div class="row mb-5">
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-person-walking"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Mounting and Dismounting Gracefully</h4>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                Learning the correct way to get on and off your bike ensures balance and control from the very start and at the end of your ride. Practice a smooth leg swing over the saddle, either from the back or the front, and confidently dismounting by leaning the bike slightly and stepping off, especially when stopping suddenly or navigating tight spaces. A smooth dismount prevents awkward wobbles and potential tumbles.
                                <br><br>
                                <strong>Practical Tip:</strong> For mounting, stand over the bike, hold the handlebars firmly, and push off with one foot as you swing the other leg over the saddle. For dismounting, anticipate your stop, unclip one foot (if using clipless pedals), and bring that foot to the ground as you come to a stop, leaning the bike slightly towards that side. Practice quick dismounts in an emergency stop drill.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-hourglass-split"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Developing Core Balance and Stability</h4>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                Maintaining stability is crucial, whether you're riding at a leisurely pace or picking up speed. Focus on looking ahead, not down at your front wheel, as this helps your body naturally adjust for balance. Use subtle shifts in body weight and gentle steering inputs to keep your center of gravity stable. Gliding drills, where you push off with your feet and try to coast without pedaling, are excellent for developing this core skill and building confidence without the distraction of pedaling.
                                <br><br>
                                <strong>Practical Tip:</strong> Find an empty, flat area. Lower your saddle so your feet can easily touch the ground. Practice pushing off with your feet and gliding, lifting your feet off the ground for as long as possible. As your balance improves, try keeping your gaze further ahead and using minimal handlebar input.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-arrow-repeat"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Efficient Pedaling Techniques and Cadence</h4>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                Efficient pedaling reduces fatigue and allows for smoother rides. Aim for a consistent, circular motion (often called "spinning") rather than just pushing down on the pedals. This engages more leg muscles (quads, hamstrings, glutes, and calves) throughout the entire revolution, conserves energy, and puts less strain on your knees. Practice maintaining a steady cadence (pedal revolutions per minute), even on varying terrain, for optimal power output. A higher cadence (e.g., 80-90 RPM) is generally more efficient than "mashing" a big gear at low RPM.
                                <br><br>
                                <strong>Practical Tip:</strong> Use a bike computer or cycling app to monitor your cadence. Try to maintain a comfortable, steady pace where your legs feel like they are spinning rather than pushing hard. Shift gears to achieve this ideal cadence on flats, climbs, and descents.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-gear"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Mastering Gear Shifting: Anticipation is Key</h4>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                Understanding how and when to shift gears is key to adapting to different road conditions and terrains. Use lower gears for climbing hills to save energy and higher gears for flats or descents to maintain speed. Shift *before* you need the new gear, especially when approaching a hill, to avoid straining your bike's drivetrain and your legs. Practice smooth transitions by pedaling lightly as you shift. Avoid "cross-chaining" (using the big front chainring with the big rear cog, or small front with small rear) as this puts unnecessary stress on your chain.
                                <br><br>
                                <strong>Practical Tip:</strong> Practice shifting in a quiet area. Experiment with both front and rear derailleurs. Listen to the sound of your chain – a smooth, quiet shift means you're doing it right. Shift to an easier gear as you approach a stop sign or red light so you can easily start again.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-md-6 mb-4 mx-auto">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-stop-circle"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Smooth Starting and Controlled Stopping</h4>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                Practice starting from a complete stop smoothly by pushing off with one foot and quickly finding your pedal stroke. For stopping, always use both front and rear brakes for optimal stopping power and stability. Apply the rear brake slightly before the front, and lean slightly back to prevent going over the handlebars, especially when stopping hard. Learn to anticipate stops and slow down gradually in advance, looking over your shoulder before slowing down to ensure no vehicle is directly behind you.
                                <br><br>
                                <strong>Practical Tip:</strong> Find a clear, flat space. Practice rolling slowly, applying both brakes gently but firmly until you come to a smooth, controlled stop. Experiment with applying more pressure to the front brake (which provides about 70% of your stopping power) while maintaining balance. Remember to unclip one foot if using clipless pedals.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4 mx-auto">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-hand-thumbs-up"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Clear Hand Signals for Communication</h4>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                Effective communication is vital when sharing the road with motorists, pedestrians, and other cyclists. Learn basic hand signals to clearly indicate your intentions—turning left or right, and stopping—well in advance of your maneuver. This predictability greatly enhances safety and fosters respectful road sharing. Ensure your signals are clear and held long enough to be seen.
                                <ul class="text-start" style="color: var(--text-dark);">
                                    <p><strong>Left Turn:</strong> Extend your left arm straight out to the side.</p>
                                    <li class="list-group-item"><strong>Right Turn:</strong> Extend your right arm straight out to the side, or bend your left arm at the elbow, pointing your hand upwards.</li>
                                    <li class="list-group-item"><strong>Stop/Slowing:</strong> Extend your left arm downwards with your palm facing backward.</li>
                                    <li class="list-group-item"><strong>Hazard Ahead:</strong> Point down towards the hazard with your left or right hand.</li>
                                </ul>
                                <br>
                                <strong>Practical Tip:</strong> Practice signaling while maintaining a straight line and steady speed. This takes practice to feel comfortable doing with one hand.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-5 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="section-icon"><i class="bi bi-person-lines-fill"></i></div>
                    <h4 class="card-title" style="color: var(--primary);">Riding in a Straight Line & Looking Ahead: The Gaze Rule</h4>
                    <p class="card-text text-start" style="color: var(--text-dark);">
                        One of the most fundamental aspects of control is being able to ride in a straight line consistently. This requires relaxed arms and shoulders, and looking well ahead (5-10 meters or further, depending on speed) where you want to go, rather than at your front wheel. Your bike will naturally follow your gaze. This also helps you spot road hazards and anticipate traffic movements far enough in advance to react safely. Practice riding a straight line in a clear, open space.
                        <br><br>
                        <strong>Practical Tip:</strong> Find a long, straight line (like a parking lot stripe or sidewalk crack). Focus your eyes on a point far ahead on that line, and try to ride directly towards it without looking down. This is called "target fixation" – your bike goes where your eyes go.
                    </p>
                </div>
            </div>

            <div class="card mb-5 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="section-icon"><i class="bi bi-geo-alt"></i></div>
                    <h4 class="card-title" style="color: var(--primary);">Navigating Basic Road Hazards: Awareness and Avoidance</h4>
                    <p class="card-text" style="color: var(--text-dark);">
                        On any ride, you'll encounter hazards. Learn to identify and react to common ones:
                        <ul class="text-start">
                            <li><strong>Potholes:</strong> If you can't avoid them, try to "bunny hop" over small ones or unweight your body by standing on the pedals and lifting slightly as you go over.</li>
                            <li><strong>Grates/Train Tracks:</strong> Approach at a near 90-degree angle to prevent your wheel from getting caught. Slow down.</li>
                            <li><strong>Gravel/Sand:</strong> Reduce speed, avoid sudden turns or braking, and keep your body loose.</li>
                            <li><strong>Wet Surfaces:</strong> Braking distance increases dramatically. Slow down, brake gently, and avoid sharp turns.</li>
                            <li><strong>Glass/Debris:</strong> Scan the road ahead and steer clear. If unavoidable, try to ride over it rather than swerving dangerously.</li>
                        </ul>
                        <br>
                        <strong>Practical Tip:</strong> Always keep your head up and scan the road 10-15 seconds ahead of you. This gives you time to react to potential hazards without abrupt movements.
                    </p>
                </div>
            </div>

            <div class="alert alert-info text-center mt-5" role="alert" style="background-color: var(--accent); color: var(--text-light); border-color: var(--accent);">
                <i class="bi bi-lightbulb-fill me-2"></i> These fundamental skills form the foundation for every cyclist, making riding enjoyable, safe, and empowering. Practice regularly in a safe environment to build muscle memory and confidence! Consider finding an empty parking lot, a quiet park path, or a cycling clinic to hone these skills.
            </div>

            <div class="card bg-light mb-4" style="background-color: var(--bg-light) !important;">
                <div class="card-body">
                    <h5 class="card-title" style="color: var(--primary);"><i class="bi bi-link-45deg me-2"></i>Resources & References</h5>
                    <p class="card-text" style="color: var(--text-dark);">For more in-depth information and external references on basic cycling skills, you can visit the following: </p>
                    <a href="https://www.bike.nyc/education/resources/skills/" class="btn btn-sm btn-outline-primary me-2 mb-2" target="_blank" style="color: var(--primary); border-color: var(--primary);">BikeNewYork Basic Skills Guide <i class="bi bi-box-arrow-up-right"></i></a>
                    <a href="https://www.cyclinguk.org/article/cycling-skills-series" class="btn btn-sm btn-outline-primary mb-2" target="_blank" style="color: var(--primary); border-color: var(--primary);">Cycling UK Skills Series <i class="bi bi-box-arrow-up-right"></i></a>
                    <a href="https://www.active.com/cycling/articles/cycling-skills-for-beginners" class="btn btn-sm btn-outline-primary mb-2" target="_blank" style="color: var(--primary); border-color: var(--primary);">Active.com Cycling for Beginners <i class="bi bi-box-arrow-up-right"></i></a>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
          integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
          crossorigin="anonymous"></script>
</body>
</html>