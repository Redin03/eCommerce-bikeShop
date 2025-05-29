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

<div class="bg-image p-5 text-center shadow-sm" style="background-image: url('../assets/images/content-image/fitness-health-banner.png'); background-size: cover; background-position: center;">
 <div class="mask" style="background-color: rgba(0, 0, 0, 0.6);">
  <div class="d-flex justify-content-center align-items-center h-100">
   <div class="text-white">
    <h2 class="mb-4" style="font-weight:700;">Cycling for Fitness & Health: Pedal Your Way to a Stronger, Happier You!</h2>
    <p class="mb-5" style="max-width:600px; margin:auto;">
Cycling is not only an enjoyable mode of transportation—it’s also one of the most effective and accessible ways to stay active and healthy. Whether you’re looking to boost your cardiovascular fitness, manage your weight, strengthen your muscles, or improve your mental well-being, cycling is an excellent and sustainable choice.
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
                        <img src="../assets/images/content-image/fitness-health.png" class="img-fluid rounded-end h-100 object-cover" alt="Person joyfully cycling outdoors">
                    </div>
                    <div class="col-md-7 order-md-1">
                        <div class="card-body p-4">
                            <h3 class="card-title" style="color: var(--primary);"><i class="bi bi-activity me-2"></i>The Unrivaled Power of the Pedal: Holistic Health Benefits</h3>
                            <p class="card-text" style="color: var(--text-dark);">
                                Cycling offers a unique blend of cardiovascular benefits, muscle strengthening, and profound mental well-being, making it a holistic approach to health. It's a low-impact activity, gentle on your joints (knees, ankles, hips), yet highly effective in burning calories, improving stamina, and building overall fitness. Embrace the journey to a healthier lifestyle, one invigorating pedal stroke at a time, and discover the joy of movement! From reducing stress to building stronger bones, the benefits extend far beyond just physical fitness.
                            </p>
                            <p class="card-text"><small class="text-muted">A fun, effective, and sustainable path to wellness.</small></p>
                        </div>
                    </div>
                </div>
            </div>

            <h2 class="mb-4 text-center" style="font-weight:700; color: var(--primary);">Comprehensive Health & Fitness Benefits of Cycling</h2>
            <hr class="mb-5" style="border-top: 3px solid var(--secondary); width: 100px; margin: auto;">

            <div class="row mb-5">
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-heart-pulse-fill"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Profound Physical Benefits: A Full-Body Workout</h4>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                Cycling significantly improves cardiovascular health by strengthening your heart muscle and increasing lung capacity, leading to better circulation, lower blood pressure, and a reduced risk of heart disease, stroke, and type 2 diabetes. It's a fantastic full-body workout, building muscle strength and endurance in your legs (quads, hamstrings, glutes, calves), core, and even upper body for stability and bike control. Its low-impact nature makes it excellent for enhancing joint mobility without high impact, and regular rides can boost your immune system, helping your body fight off illnesses. Additionally, cycling can help with bone density, especially when incorporating standing climbs, and improve coordination and balance.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-emoji-smile"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Boost Your Mental Wellness: The Mind-Body Connection</h4>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                Beyond the physical, cycling is a powerful stress reliever and mood enhancer. The rhythmic motion, exposure to fresh air and natural environments, and the release of endorphins (natural mood elevators) during exercise all contribute to reduced anxiety, alleviated symptoms of depression, and improved overall mood. Cycling can also enhance cognitive function, boost creativity, and improve sleep quality. It's a moving meditation for many, offering a sense of freedom and accomplishment. Joining group rides can also foster social connections, further boosting mental health.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-clipboard-data"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Creating an Effective Cycling Workout Plan</h4>
                            <p class="card-text " style="color: var(--text-dark);">
                                Learn how to structure rides for specific fitness goals, adapting to your current level and aiming for progressive overload:
                                <ul class="text-start">
                                    <li><strong>Endurance:</strong> Longer, steady-pace rides (e.g., 60-90 minutes at a comfortable conversational pace, where you can still talk easily) to build stamina and burn fat. Focus on consistent effort.</li>
                                    <li><strong>Strength:</strong> Incorporate hills or higher gear intervals (e.g., 1-2 minute efforts up a climb, 3-5 times) to build leg power and muscle. Maintain good form and a slightly lower cadence.</li>
                                    <li><strong>Interval Training (HIIT):</strong> Alternate between high-intensity bursts (e.g., 30-60 seconds of maximal effort) and recovery periods (e.g., 1-2 minutes of easy spinning). Great for improving speed, power, and cardiovascular fitness in less time.</li>
                                    <li><strong>Recovery Rides:</strong> Short, very easy spins (e.g., 30 minutes light pedaling in an easy gear) to aid muscle recovery, reduce soreness, and improve blood flow after hard efforts.</li>
                                    <li><strong>Cross-Training:</strong> Supplement cycling with strength training (core work, squats, lunges) to prevent imbalances and improve overall athletic performance.</li>
                                </ul>
                                Start slowly and gradually increase duration, intensity, or frequency. Listen to your body and don't push too hard too soon to avoid injury.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-graph-up"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Tracking Your Progress and Staying Motivated</h4>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                Stay motivated and visualize your improvements by tracking your progress! Use cycling apps (like Strava, Komoot, RideWithGPS) or wearables (GPS bike computers, smartwatches) to monitor key metrics such as speed, distance, elevation gain, calories burned, heart rate, and power output (if you have a power meter). This data can help you set new goals, identify areas for improvement, and see your incredible progress over time. Joining online communities or local cycling clubs can also provide motivation, accountability, and a sense of camaraderie. Setting challenging but achievable goals (e.g., riding a certain distance, conquering a specific climb) can also be a powerful motivator.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-md-6 mb-4 mx-auto">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-droplet-half"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Crucial Recovery, Hydration, and Nutrition for Cyclists</h4>
                            <p class="card-text" style="color: var(--text-dark);">
                                Essential for performance, injury prevention, and overall health. What you put into your body is just as important as the miles you log.
                                <ul class="text-start">
                                    <li><strong>Hydration:</strong> Drink water constantly throughout the day, and more before, during, and after rides. For rides over an hour or in hot, humid conditions (like the Philippines!), consider electrolyte drinks to replenish lost salts and minerals. Aim for 500-750ml of fluid per hour of riding.</li>
                                    <li><strong>Pre-Ride Nutrition (1-3 hours before):</strong> Focus on complex carbohydrates for sustained energy (e.g., oatmeal, whole-grain toast with banana, rice cakes). Avoid heavy, fatty, or high-fiber foods that can cause stomach upset.</li>
                                    <li><strong>During Ride Nutrition (for rides >60-90 minutes):</strong> Replenish with easily digestible carbohydrates like energy gels, energy bars, dried fruit, bananas, or a small piece of pandesal. Aim for 30-60 grams of carbs per hour.</li>
                                    <li><strong>Post-Ride Nutrition (within 30-60 minutes):</strong> Crucial for muscle repair and glycogen replenishment. Consume a mix of carbohydrates and protein (e.g., chocolate milk, lean protein with rice, a fruit smoothie with protein powder).</li>
                                    <li><strong>Recovery:</strong> Prioritize post-ride stretching to improve flexibility and prevent muscle stiffness. Get adequate sleep (7-9 hours) for optimal recovery and repair. Incorporate rest days into your training schedule to allow your body to adapt and rebuild. Consider foam rolling or light massage.</li>
                                    <li><strong>Foods to Limit/Avoid:</strong> Excessive processed foods, sugary drinks, deep-fried items, and highly saturated fats can hinder performance and recovery. While enjoying local delicacies in moderation is fine, prioritize nutrient-dense whole foods.</li>
                                </ul>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4 mx-auto">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-people"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Cycling: A Lifelong Activity for Any Age and Ability</h4>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                Cycling is a truly lifelong activity! From teaching children to ride their first balance bike, to adaptive cycling for seniors, its low-impact nature makes it accessible and beneficial for nearly all fitness levels and ages. It's a wonderful way for families to stay active together, create lasting memories, and explore their surroundings. Electric bikes (e-bikes) are also expanding accessibility, allowing more people to enjoy the benefits of cycling regardless of physical limitations or challenging terrain, making climbs easier and commutes faster. Many communities in the Philippines are becoming more bike-friendly, making it easier for people of all ages to integrate cycling into their daily lives.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info text-center mt-5" role="alert" style="background-color: var(--accent); color: var(--text-light); border-color: var(--accent);">
                <i class="bi bi-bicycle me-2"></i> Pedal your way to better health—one ride at a time! Consistent cycling habits, combined with proper nutrition and recovery, lead to significant and sustainable long-term benefits for your physical and mental well-being. Make it a regular part of your routine!
            </div>

            <div class="card bg-light mb-4" style="background-color: var(--bg-light) !important;">
                <div class="card-body">
                    <h5 class="card-title" style="color: var(--primary);"><i class="bi bi-link-45deg me-2"></i>Resources & References</h5>
                    <p class="card-text text-start" style="color: var(--text-dark);">For more detailed guidance on cycling for fitness and health, check out these reputable sources: </p>
                    <a href="https://www.nhs.uk/live-well/exercise/cycling-for-fitness/" class="btn btn-sm btn-outline-primary me-2 mb-2" target="_blank" style="color: var(--primary); border-color: var(--primary);">NHS Cycling for Fitness <i class="bi bi-box-arrow-up-right"></i></a>
                    <a href="https://www.mayoclinic.org/healthy-lifestyle/fitness/in-depth/cycling/art-20045095" class="btn btn-sm btn-outline-primary me-2 mb-2" target="_blank" style="color: var(--primary); border-color: var(--primary);">Mayo Clinic Cycling Benefits <i class="bi bi-box-arrow-up-right"></i></a>
                    <a href="https://www.active.com/cycling/articles/cycling-nutrition-rules-for-riders" class="btn btn-sm btn-outline-primary mb-2" target="_blank" style="color: var(--primary); border-color: var(--primary);">Active.com Cycling Nutrition <i class="bi bi-box-arrow-up-right"></i></a>
                    <a href="https://www.coachmag.co.uk/fitness/cycling/6641/cycling-weight-loss-tips" class="btn btn-sm btn-outline-primary mb-2" target="_blank" style="color: var(--primary); border-color: var(--primary);">CoachMag Cycling Weight Loss <i class="bi bi-box-arrow-up-right"></i></a>
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