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

<div class="bg-image p-5 text-center shadow-sm" style="background-image: url('../assets/images/content-image/rules-roads-banner.png'); background-size: cover; background-position: center;">
 <div class="mask" style="background-color: rgba(0, 0, 0, 0.6);">
  <div class="d-flex justify-content-center align-items-center h-100">
   <div class="text-white">
    <h2 class="mb-4" style="font-weight:700;">Rules of the Road & Traffic Laws for Cyclists: Share the Road Safely</h2>
    <p class="mb-5" style="max-width:600px; margin:auto;">
   As a cyclist, you are a vehicle on the road, sharing space with motor vehicles, pedestrians, and other cyclists. Knowing and diligently following traffic laws is not just about compliance; it's crucial for your safety and the safety of everyone around you.
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
                        <img src="../assets/images/content-image/traffic-laws.png" class="img-fluid rounded-start h-100 object-cover" alt="Cyclist obeying traffic laws in a city">
                    </div>
                    <div class="col-md-7">
                        <div class="card-body p-4">
                            <h3 class="card-title" style="color: var(--primary);"><i class="bi bi-book me-2"></i>Your Rights and Responsibilities as a Cyclist: Act Like a Vehicle</h3>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                In most places, including the Philippines, cyclists have the same rights and duties as motor vehicle drivers. This means you are expected to follow the same traffic laws, including stopping at lights, signaling turns, yielding where appropriate, and understanding right-of-way. Operating a bicycle on public roads grants you the same privileges and responsibilities as any other vehicle. Understanding these responsibilities is fundamental to safe and respectful road sharing, fostering a more harmonious environment for all road users. By acting predictably and adhering to traffic rules, you enhance your safety and contribute positively to the cycling community's reputation.
                            </p>
                            <p class="card-text"><small class="text-muted">Sharing the road safely and respectfully begins with knowledge.</small></p>
                        </div>
                    </div>
                </div>
            </div>

            <h2 class="mb-4 text-center" style="font-weight:700; color: var(--primary);">Essential Road Rules for Confident Cycling</h2>
            <hr class="mb-5" style="border-top: 3px solid var(--secondary); width: 100px; margin: auto;">

            <div class="row mb-5">
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Obeying Traffic Signals and Signs Religiously</h4>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                Always follow stop signs, traffic lights, and road markings. Running red lights or stop signs as a cyclist is not only illegal but also incredibly dangerous, leading to preventable accidents. It undermines your credibility as a road user and puts yourself and others at severe risk. Treat every signal and sign as if you were driving a car, coming to a complete stop, and proceeding only when safe and lawful. This includes "no U-turn" signs, "one way" signs, and lane markings.
                                <br><br>
                                <strong>Practical Tip:</strong> If a traffic light doesn't detect you, try positioning yourself directly over the sensor loop (often marked with cuts in the asphalt) or use a bike-specific traffic light button if available. If all else fails and it's safe, you may need to wait for a car or consider the specific local law regarding "dead" traffic lights for cyclists.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-map"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Strategic Lane Positioning for Visibility</h4>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                Ride predictably and visibly. Generally, ride on the right side of the road, with traffic flow. In many areas, it's recommended to "take the lane" (ride in the center of the lane, or far enough left to avoid the "door zone" of parked cars) when the lane is too narrow for a car to safely share side-by-side with a bike. This increases your visibility, prevents unsafe passing by motorists, and allows you to avoid road hazards. Position yourself where drivers can clearly see you and anticipate your movements. Avoid the extreme right gutter where debris accumulates and drivers might not expect you.
                                <br><br>
                                <strong>Practical Tip:</strong> If a lane is wide enough for a car and bike side-by-side, ride about 1 meter (3 feet) from the curb or parked cars. If it's too narrow, take the lane to ensure cars pass you completely, rather than squeezing by.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-signpost-fill"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Clear Hand Signals for Intent Communication</h4>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                Clearly communicate your intentions using standardized hand signals well in advance of turning, changing lanes, or stopping. This allows other road users to anticipate your movements and react accordingly, greatly reducing the risk of collisions. Make eye contact with drivers and pedestrians whenever possible to ensure they have seen your signal. Consistency in your signals helps build trust with other road users.
                                <ul class="list-group list-group-flush mb-3 mt-3" style="color: var(--text-dark); text-align: left;">
                                    <li class="list-group-item"><strong>Left Turn:</strong> Extend your left arm straight out to the side.</li>
                                    <li class="list-group-item"><strong>Right Turn:</strong> Extend your right arm straight out to the side, or bend your left arm at the elbow, pointing your hand upwards.</li>
                                    <li class="list-group-item"><strong>Stop/Slowing:</strong> Extend your left arm downwards with your palm facing backward.</li>
                                    <li class="list-group-item"><strong>Hazard Ahead (Left Side):</strong> Point down towards the hazard with your left hand.</li>
                                    <li class="list-group-item"><strong>Hazard Ahead (Right Side):</strong> Point down towards the hazard with your right hand.</li>
                                </ul>
                                <br>
                                <strong>Practical Tip:</strong> Practice signaling in a safe area. You should be able to maintain your balance and control your bike with one hand for several seconds.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-arrow-right-circle"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Understanding Yielding and Right of Way</h4>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                Understand when to yield to pedestrians and motor vehicles. At intersections, follow the same right-of-way rules as cars. This means yielding to traffic already in the intersection or approaching from your right at uncontrolled intersections, and yielding to pedestrians in crosswalks. Be particularly cautious when crossing driveways or entering traffic from a side street, always assuming you are not seen until you've made eye contact. When turning left, if no bike box or dedicated turn lane is present, you may need to act like a car and signal, move to the center of the lane, and wait for a safe gap in oncoming traffic.
                                <br><br>
                                <strong>Practical Tip:</strong> When approaching any intersection, slow down and be ready to stop. Scan for cross-traffic and pedestrians. Make eye contact with drivers, and if in doubt, yield.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-5 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="section-icon"><i class="bi bi-lightbulb"></i></div>
                    <h4 class="card-title" style="color: var(--primary);">Avoiding Common Mistakes and Riding Defensively</h4>
                    <p class="card-text text-start" style="color: var(--text-dark);">
                        Stay alert and predictable to significantly reduce the risk of accidents. Avoid weaving in and out of traffic, which can make you unpredictable to drivers. Riding on sidewalks is often illegal and more dangerous than riding on the road due to conflicts with pedestrians and "right hook" collisions at intersections. Never assume drivers see you; always make eye contact and be prepared to react. Look out for "dooring" incidents (car doors opening suddenly) and other common hazards like potholes, grates, and distracted drivers. Practice defensive cycling by anticipating potential dangers and having an escape plan.
                        <br><br>
                        <strong>Common Mistakes to Avoid:</strong>
                        <ul class="text-start">
                            <li><strong>Riding against traffic:</strong> Highly dangerous and illegal in most places.</li>
                            <li><strong>Ignoring blind spots:</strong> Especially for trucks and buses. Assume they can't see you.</li>
                            <li><strong>Distracted riding:</strong> Avoid using headphones that block out ambient sound or looking at your phone while riding.</li>
                            <li><strong>Riding too close to parked cars:</strong> Always leave at least 1 meter (3 feet) to avoid "dooring."</li>
                            <li><strong>Not using lights during the day:</strong> Daytime running lights increase visibility even in bright conditions.</li>
                        </ul>
                    </p>
                </div>
            </div>

            <div class="card mb-5 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="section-icon"><i class="bi bi-exclamation-octagon"></i></div>
                    <h4 class="card-title" style="color: var(--primary);">Understanding Local Laws and Regulations (Philippines Context)</h4>
                    <p class="card-text text-start" style="color: var(--text-dark);">
                        Traffic laws for cyclists can vary by city, province, or country. In the Philippines, the Department of Transportation (DOTr) and local government units (LGUs) often issue guidelines and ordinances. It's essential to familiarize yourself with the specific regulations in your riding area. This includes laws about:
                        <ul class="text-start">
                            <li><strong>Helmet Use:</strong> Mandatory in many urban areas (e.g., Metro Manila) and recommended everywhere.</li>
                            <li><strong>Lights:</strong> Mandatory for night riding (white front, red rear).</li>
                            <li><strong>Riding on Sidewalks:</strong> Generally prohibited unless specifically designated as shared-use paths. Stick to the road or bike lanes.</li>
                            <li><strong>Minimum Passing Distance:</strong> Some LGUs or national laws may stipulate a minimum safe distance for vehicles passing cyclists.</li>
                            <li><strong>Use of Bike Lanes:</strong> Where available, cyclists are often required to use them.</li>
                            <li><strong>Anti-Distracted Cycling:</strong> Similar to anti-distracted driving, using phones or wearing restrictive headphones while cycling can be prohibited.</li>
                        </ul>
                        Many local government websites or cycling advocacy groups (like the Firefly Brigade, PinoyMTB) provide summaries of relevant laws. Being informed helps you ride legally and safely, and advocating for better cycling infrastructure.
                    </p>
                </div>
            </div>

            <div class="alert alert-info text-center mt-5" role="alert" style="background-color: var(--accent); color: var(--text-light); border-color: var(--accent);">
                <i class="bi bi-info-circle-fill me-2"></i> Understanding and adhering to road laws helps you become a confident, responsible, and respectful cyclist, contributing to a safer environment for everyone. Your actions reflect on the entire cycling community. Be the change you want to see on the road!
            </div>

            <div class="card bg-light mb-4" style="background-color: var(--bg-light) !important;">
                <div class="card-body">
                    <h5 class="card-title" style="color: var(--primary);"><i class="bi bi-link-45deg me-2"></i>Resources & References</h5>
                    <p class="card-text text-start" style="color: var(--text-dark);">For detailed information on traffic laws and cyclist rights, consult these resources: </p>
                    <a href="https://www.law.cornell.edu/wex/bicycle_laws" class="btn btn-sm btn-outline-primary me-2 mb-2" target="_blank" style="color: var(--primary); border-color: var(--primary);">Cornell Law School Bicycle Laws (US general) <i class="bi bi-box-arrow-up-right"></i></a>
                    <a href="https://bikeleague.org/content/traffic-laws" class="btn btn-sm btn-outline-primary me-2 mb-2" target="_blank" style="color: var(--primary); border-color: var(--primary);">League of American Bicyclists Traffic Laws <i class="bi bi-box-arrow-up-right"></i></a>
                    <a href="https://www.lto.gov.ph/news-and-updates/2018-05-24-02-53-48/810-lto-reminds-cyclists-of-bike-laws.html" class="btn btn-sm btn-outline-primary mb-2" target="_blank" style="color: var(--primary); border-color: var(--primary);">LTO Philippines: Bicycle Laws (Local Example) <i class="bi bi-box-arrow-up-right"></i></a>
                    <a href="https://news.abs-cbn.com/news/08/21/20/bike-friendly-metro-manila-a-list-of-ordinances-to-know" class="btn btn-sm btn-outline-primary mb-2" target="_blank" style="color: var(--primary); border-color: var(--primary);">Metro Manila Bike Ordinances (ABS-CBN News) <i class="bi bi-box-arrow-up-right"></i></a>
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