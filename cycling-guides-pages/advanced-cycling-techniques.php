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
    .img-fluid.rounded-end.h-100.object-cover {
        object-fit: cover;
        width: 100%;
    }
  </style>
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="bg-image p-5 text-center shadow-sm" style="background-image: url('../assets/images/content-image/advanced-cycling-techniques-banner.png'); background-size: cover; background-position: center;">
 <div class="mask" style="background-color: rgba(0, 0, 0, 0.6);">
  <div class="d-flex justify-content-center align-items-center h-100">
   <div class="text-white">
    <h2 class="mb-4" style="font-weight:700;">Advanced Cycling Techniques: Ride Smarter, Ride Stronger, Go Further</h2>
    <p class="mb-5" style="max-width:600px; margin:auto;">
                  Once you're comfortable with the basics, it's time to elevate your skills. Advanced cycling techniques help you ride more efficiently, handle various terrains, improve your speed, and respond better to diverse challenges on the road or trail.
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
                    <div class="col-md-5 order-md-2">
                        <img src="https://via.placeholder.com/600x400?text=Advanced+Cycling+Skills" class="img-fluid rounded-end h-100 object-cover" alt="Cyclist performing advanced maneuver on a winding road">
                    </div>
                    <div class="col-md-7 order-md-1">
                        <div class="card-body p-4">
                            <h3 class="card-title" style="color: var(--primary);"><i class="bi bi-graph-up-arrow me-2"></i>Elevating Your Ride Beyond the Basics: Precision and Control</h3>
                            <p class="card-text" style="color: var(--text-dark);">
                                Moving beyond the basics unlocks new levels of cycling enjoyment and performance. Advanced techniques are not just for competitive riders; they enhance safety and confidence for commuters, touring cyclists, and recreational riders tackling more challenging routes. From smooth descents and efficient group dynamics to mastering technical trail features, these skills transform your riding experience, making you a more capable and confident cyclist in any situation. They involve a deeper understanding of bike dynamics, weight distribution, and anticipating terrain changes, allowing you to flow with the bike rather than just ride it.
                            </p>
                            <p class="card-text"><small class="text-muted">Mastering the art and science of cycling.</small></p>
                        </div>
                    </div>
                </div>
            </div>

            <h2 class="mb-4 text-center" style="font-weight:700; color: var(--primary);">Key Advanced Techniques for Enhanced Performance and Safety</h2>
            <hr class="mb-5" style="border-top: 3px solid var(--secondary); width: 100px; margin: auto;">

            <div class="row mb-5">
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-arrow-up-right-square"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Efficient Hill Climbing Strategies</h4>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                Master body positioning, precise gear shifting, and pacing for steep inclines. Use lower gears *before* the climb starts to maintain a comfortable cadence (pedal revolutions per minute) and conserve energy. Learn when to stay seated for efficient power transfer on gradual climbs and when to stand out of the saddle ("dancing on the pedals") for short bursts of power on very steep sections or to stretch your legs. Maintain a smooth, steady effort rather than "punching" the climb.
                                <br><br>
                                <strong>Techniques for Climbing:</strong>
                                <ul class="text-start">
                                    <li><strong>Seated Climbing:</strong> Ideal for sustained climbs. Stay relaxed, breathe deeply, and maintain a high, smooth cadence. Focus on pulling up on the pedals as much as pushing down.</li>
                                    <li><strong>Standing Climbing (Out of the Saddle):</strong> Use for short, steep sections, to gain momentum, or to relieve pressure. Shift to a slightly harder gear, lean forward over the handlebars, and use your body weight to drive the pedals.</li>
                                    <li><strong>Pacing:</strong> Avoid going out too hard. Find a sustainable pace you can maintain to the top.</li>
                                </ul>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-arrow-down-left-square"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Confident and Safe Descending</h4>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                Descending confidently involves proper braking, precise weight distribution, and intelligent line selection. Keep your weight back and low, with your hands firmly on the brake levers (covering both front and rear). Feather your brakes rather than grabbing them abruptly, especially the front brake, to control speed smoothly. Look far ahead through the turn to anticipate road hazards, apexes, and exits. Avoid staring at your front wheel. Practice in a safe, familiar descent before tackling steeper or more technical ones.
                                <br><br>
                                <strong>Key Descending Tips:</strong>
                                <ul class="text-start">
                                    <li><strong>Braking:</strong> Use both brakes. The front brake provides most of the stopping power, but use it judiciously. "Feather" the brakes, applying and releasing pressure, rather than continuously dragging them.</li>
                                    <li><strong>Body Position:</strong> Lower your center of gravity by bending your elbows and knees. Keep your weight slightly back, hovering over the saddle, especially on steep descents.</li>
                                    <li><strong>Look Ahead:</strong> Look far through the corner to where you want to exit, not just at your front wheel.</li>
                                    <li><strong>Relax:</strong> Keep your body relaxed to absorb bumps and allow the bike to move freely beneath you.</li>
                                </ul>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-arrow-return-right"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Mastering Cornering Techniques</h4>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                Improve your ability to take turns with speed, stability, and confidence. The key is to lean the bike *more* than your body. Lower your outside pedal, apply pressure to the inside handlebar, and look through the turn to where you want to go. Brake *before* the turn, not in it. Maintain a slight pedal pressure through the turn if safe to do so for added stability. Practice in a safe, open area like an empty parking lot, starting with wide, gentle turns and gradually tightening your line.
                                <br><br>
                                <strong>Cornering Steps (Road/Smooth Trail):</strong>
                                <ol class="text-start">
                                    <li><strong>Approach:</strong> Brake before the turn to a manageable speed.</li>
                                    <li><strong>Look:</strong> Look through the turn to your exit point.</li>
                                    <li><strong>Lean:</strong> Lean the *bike* more than your body into the turn.</li>
                                    <li><strong>Pressure:</strong> Put pressure on your outside pedal (the one furthest from the turn) and keep it at the 6 o'clock position.</li>
                                    <li><strong>Exit:</strong> Once through the apex, gently accelerate out of the turn.</li>
                                </ol>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-people-fill"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Effective Group Riding Etiquette and Dynamics</h4>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                Understanding drafting (riding closely behind another rider to reduce wind resistance), rotation, and overall group riding etiquette is crucial for efficient and safe group rides. Maintain a consistent pace, communicate hazards (potholes, cars, glass) with clear hand signals and verbal calls (e.g., "Car back!", "Hole left!"), and hold your line predictably to avoid accidents. Always follow the leader's signals and maintain awareness of riders around you. Learn how to safely take a turn at the front and rotate to the back.
                                <br><br>
                                <strong>Group Riding Rules:</strong>
                                <ul class="text-start">
                                    <li><strong>Hold Your Line:</strong> Ride predictably and avoid sudden movements.</li>
                                    <li><strong>Look Around:</strong> Be aware of riders in front, behind, and beside you.</li>
                                    <li><strong>Communicate:</strong> Use loud, clear verbal calls and hand signals for hazards, slowing, stopping, and turns.</li>
                                    <li><strong>Overlap Wheels:</strong> Never overlap wheels with the rider in front of you. This is a common cause of crashes.</li>
                                    <li><strong>Smooth Braking & Pedaling:</strong> Avoid sudden braking; feather gently. Maintain a consistent cadence.</li>
                                </ul>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-5 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="section-icon"><i class="bi bi-lightning-charge"></i></div>
                    <h4 class="card-title" style="color: var(--primary);">Essential Emergency Maneuvers</h4>
                    <p class="card-text text-start" style="color: var(--text-dark);">
                        Practice quick stops (using both brakes effectively for maximum stopping power without skidding), swerving to avoid unexpected obstacles, and quick dismounts. These skills are vital for reacting safely and instinctually to unexpected situations on the road or trail. Regular drills, like setting up cones in an empty parking lot to practice emergency braking and obstacle avoidance, can turn these reactions into muscle memory. Knowing how to unclip from clipless pedals quickly is also a critical emergency skill if you use them.
                        <br><br>
                        <strong>Emergency Drills:</strong>
                        <ul class="text-start">
                            <li><strong>Emergency Braking:</strong> Practice stopping as quickly and safely as possible without skidding. Get low and slightly back on the bike, apply both brakes firmly.</li>
                            <li><strong>Swerving:</strong> Set up two cones a bike-width apart. Practice steering sharply around one then back around the other without losing speed. Look where you want to go.</li>
                            <li><strong>Quick Dismount:</strong> Essential for clipless pedal users. Practice unclipping one foot quickly and getting off the bike in a hurry.</li>
                        </ul>
                    </p>
                </div>
            </div>

            <div class="card mb-5 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="section-icon"><i class="bi bi-person-bounding-box"></i></div>
                    <h4 class="card-title" style="color: var(--primary);">Body Positioning and Bike Control</h4>
                    <p class="card-text text-start" style="color: var(--text-dark);">
                        Beyond just pedaling, understanding how your body interacts with the bike is crucial. Learn to unweight your saddle over bumps, shift your weight fore and aft for traction on climbs and descents, and use your hips and core to steer the bike. This active body positioning allows you to absorb shocks, maintain control over rough terrain, and improve overall handling. Practice standing on your pedals and hovering over the saddle to develop this "active" riding stance, especially useful for navigating obstacles or uneven surfaces.
                        <br><br>
                        <strong>Active Riding Stance:</strong>
                        <ul class="text-start">
                            <li><strong>Attack Position:</strong> Hips back, knees and elbows bent, ready to react to terrain. This is your default for off-road or unpredictable urban conditions.</li>
                            <li><strong>Weight Distribution:</strong> Shift weight forward for traction on climbs, back for control on descents.</li>
                            <li><strong>Unweighting:</strong> Briefly lift your weight off the saddle when going over bumps or small obstacles, allowing the bike to move freely beneath you.</li>
                        </ul>
                    </p>
                </div>
            </div>

            <div class="card mb-5 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="section-icon"><i class="bi bi-person-workspace"></i></div>
                    <h4 class="card-title" style="color: var(--primary);">Mastering Clipless Pedals (if applicable)</h4>
                    <p class="card-text text-start" style="color: var(--text-dark);">
                        If you've upgraded to clipless pedals, mastering clipping in and out is a crucial advanced skill. They offer greater pedaling efficiency and power transfer, but require practice to avoid embarrassing or dangerous falls.
                        <br><br>
                        <strong>Clipless Pedal Tips:</strong>
                        <ul class="text-start">
                            <li><strong>Practice in a Safe Area:</strong> Lean against a wall or find a grassy area where you can fall safely.</li>
                            <li><strong>Practice Clipping Out:</strong> Focus on the twisting motion to unclip. Make it muscle memory. Practice unclipping one foot before stopping.</li>
                            <li><strong>Anticipate Stops:</strong> Unclip one foot well before you need to stop, especially at traffic lights or busy intersections.</li>
                            <li><strong>Start with Loose Tension:</strong> Many clipless pedals allow you to adjust the release tension. Start with it very loose and tighten as you gain confidence.</li>
                        </ul>
                    </p>
                </div>
            </div>

            <div class="alert alert-info text-center mt-5" role="alert" style="background-color: var(--accent); color: var(--text-light); border-color: var(--accent);">
                <i class="bi bi-star-fill me-2"></i> These techniques are especially helpful for road cyclists, mountain bikers, and commuters who want to ride more skillfully, efficiently, and safely. Dedication to practice will significantly improve your cycling prowess. Consider taking a cycling skills clinic for personalized instruction.
            </div>

            <div class="card bg-light mb-4" style="background-color: var(--bg-light) !important;">
                <div class="card-body">
                    <h5 class="card-title" style="color: var(--primary);"><i class="bi bi-link-45deg me-2"></i>Resources & References</h5>
                    <p class="card-text text-start" style="color: var(--text-dark);">To deepen your understanding of advanced cycling techniques, explore these expert resources: </p>
                    <a href="https://www.cyclingweekly.com/fitness/training/how-to-ride-your-bike-better-166299" class="btn btn-sm btn-outline-primary me-2 mb-2" target="_blank" style="color: var(--primary); border-color: var(--primary);">Cycling Weekly Advanced Skills <i class="bi bi-box-arrow-up-right"></i></a>
                    <a href="https://www.bikeradar.com/advice/skills/mountain-bike-skills/" class="btn btn-sm btn-outline-primary me-2 mb-2" target="_blank" style="color: var(--primary); border-color: var(--primary);">BikeRadar MTB Skills <i class="bi bi-box-arrow-up-right"></i></a>
                    <a href="https://www.roadbikerider.com/master-bike-handling-skills/" class="btn btn-sm btn-outline-primary mb-2" target="_blank" style="color: var(--primary); border-color: var(--primary);">Road Bike Rider Handling Skills <i class="bi bi-box-arrow-up-right"></i></a>
                    <a href="https://www.youtube.com/playlist?list=PLD727ED9B10023608" class="btn btn-sm btn-outline-primary mb-2" target="_blank" style="color: var(--primary); border-color: var(--primary);">Global Cycling Network (GCN) How To's <i class="bi bi-box-arrow-up-right"></i></a>
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