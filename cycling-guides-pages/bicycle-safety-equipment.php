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
    .check-item {
        color: var(--text-dark);
        font-weight: 500;
        margin-bottom: 8px;
    }
    .img-fluid.rounded-end.h-100.object-cover {
        object-fit: cover;
        width: 100%;
    }
  </style>
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="bg-image p-5 text-center shadow-sm" style="background-image: url('../assets/images/content-image/bicycle-safety-equipment-banner.png'); background-size: cover; background-position: center;">
 <div class="mask" style="background-color: rgba(0, 0, 0, 0.6);">
  <div class="d-flex justify-content-center align-items-center h-100">
   <div class="text-white">
    <h2 class="mb-4" style="font-weight:700;">Bicycle Safety & Equipment: Ride with Confidence and Protection</h2>
    <p class="mb-5" style="max-width:600px; margin:auto;">
   Staying safe on the road starts with having the right equipment and knowing how to use it. This section will guide you through essential bike safety practices and tools to protect yourself during every ride, minimizing risks and maximizing enjoyment.
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
                        <img src="../assets/images/content-image/wear-helmet-gear.png" class="img-fluid rounded-end h-100 object-cover" alt="Cyclist wearing helmet and gear">
                    </div>
                    <div class="col-md-7 order-md-1">
                        <div class="card-body p-4">
                            <h3 class="card-title" style="color: var(--primary);"><i class="bi bi-shield-fill-check me-2"></i>Prioritizing Your Safety on Every Journey</h3>
                            <p class="card-text" style="color: var(--text-dark);">
                                While cycling is an incredibly rewarding activity, being prepared for potential hazards is paramount. Investing in proper safety gear and routinely checking your bicycle's condition are non-negotiable steps. These practices significantly reduce risks and ensure your rides are as safe and enjoyable as possible, giving you peace of mind on every journey. Think of safety equipment as an extension of your own awareness and skill on the road. Remember, even the most experienced riders prioritize safety. It’s not just about protecting yourself, but also about being a responsible road user and reducing risks for others.
                            </p>
                            <p class="card-text"><small class="text-muted">Equip yourself, ride protected, and stay visible.</small></p>
                        </div>
                    </div>
                </div>
            </div>

            <h2 class="mb-4 text-center" style="font-weight:700; color: var(--primary);">Essential Safety Gear and Practices</h2>
            <hr class="mb-5" style="border-top: 3px solid var(--secondary); width: 100px; margin: auto;">

            <div class="row mb-5">
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-headset"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">The Non-Negotiable: Wearing a Helmet</h4>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                A properly fitted helmet is your most crucial piece of safety equipment. It provides vital protection in case of a fall or collision, significantly reducing the risk of serious head injuries. Always ensure it fits snugly and covers your forehead (about two finger-widths above your eyebrows). The straps should form a "V" shape below your ears, and you should be able to open your mouth wide without the helmet shifting. Replace your helmet if it's been in a crash or shows signs of wear and tear (cracks, degraded foam), even if you don't see visible damage. Helmets typically have a lifespan of 3-5 years, depending on usage.
                                <br><br>
                                <strong>Choosing a Helmet:</strong> Look for helmets certified by safety standards bodies (e.g., CPSC in the US, CE in Europe). Consider features like MIPS (Multi-directional Impact Protection System) for added rotational impact protection, good ventilation for comfort in the Philippine climate, and reflective elements for visibility.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-sunglasses"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Additional Protective and Comfort Gear</h4>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                Beyond helmets, consider gloves for improved grip, cushioning, and hand protection in case of a fall. Bright or reflective clothing drastically increases your visibility to motorists, especially in low light. Cycling glasses protect your eyes from sun, wind, dust, insects, and road debris, enhancing both comfort and safety. Knee and elbow pads can also be beneficial, particularly for mountain biking or if you're new to cycling. Padded cycling shorts significantly improve comfort on longer rides, preventing chafing and soreness.
                                <br><br>
                                <strong>Clothing Choices:</strong> Opt for brightly colored, moisture-wicking fabrics that are comfortable and allow for movement. Avoid loose clothing that could get caught in your chain. For night riding, prioritize reflective vests, jackets, or strips on your limbs.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-lightbulb"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Maximizing Visibility: Lights and Reflectors</h4>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                Be seen, day or night! Use a bright white front light (at least 600 lumens for night riding, lower for daytime visibility, but a flashing daytime running light of 200+ lumens is highly recommended) and a flashing red rear light (at least 200 lumens) to ensure you are visible to motorists, especially when riding in low light conditions, at dusk, dawn, or at night. Reflectors on your wheels, pedals, and clothing also significantly increase your passive visibility from various angles, supplementing your active lights.
                                <br><br>
                                <strong>Light Modes:</strong> Many modern bike lights offer various modes (steady, pulse, flash). While steady is good for seeing the road, flashing modes, particularly during the day, are often more effective at grabbing attention. Consider USB-rechargeable lights for convenience.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="section-icon"><i class="bi bi-wrench"></i></div>
                            <h4 class="card-title" style="color: var(--primary);">Pre-Ride Check: The ABC Quick Check</h4>
                            <p class="card-text text-start" style="color: var(--text-dark);">
                                Before every ride, perform the ABC Quick Check to ensure your bike is in safe working order. This quick inspection can prevent common mechanical issues and potential accidents:<br>
                                <span class="check-item"><strong>A</strong> - Air: Check tire pressure. Ensure tires are inflated to the recommended PSI (found on the sidewall of the tire). Squeeze the tire; it should feel very firm. Underinflated tires can lead to flats and poor handling.</span><br>
                                <span class="check-item"><strong>B</strong> - Brakes: Test both front and rear brakes. Roll the bike forward and apply the front brake; the wheel should stop immediately. Repeat for the rear. Ensure brake levers are firm and not spongy, and brake pads make full contact with the rim or rotor without rubbing.</span><br>
                                <span class="check-item"><strong>C</strong> - Chain & Cranks: Ensure your chain is clean, lubricated, and moves smoothly without skipping. Check that your crank arms (where your pedals attach) are tight and don't wobble. Look for any bent teeth on chainrings or cogs.</span><br>
                                <span class="check-item"><strong>Quick Releases/Axles:</strong> Confirm your wheels (and seat post, if applicable) are securely fastened with quick-release levers closed tightly or thru-axles properly torqued. Give your wheels a quick tug to ensure they're seated properly.</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-5 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="section-icon"><i class="bi bi-lock"></i></div>
                    <h4 class="card-title" style="color: var(--primary);">Secure Your Investment: Locks and Theft Prevention</h4>
                    <p class="card-text text-start" style="color: var(--text-dark);">
                        Protect your investment! Always invest in a high-quality lock – a U-lock or a strong chain lock is highly recommended over cable locks for security. Learn proper locking techniques: aim to secure both your frame and at least one wheel to an immovable object like a sturdy bike rack or signpost. Avoid leaving your bike in isolated or high-theft areas for extended periods, and consider registering your bike with local authorities or a national database (if available in the Philippines) to aid recovery if stolen.
                        <br><br>
                        <strong>Locking Strategy:</strong> Use the "Sheldon Brown method" by locking through the rear wheel and rear triangle. This makes it impossible to ride away without destroying the rear wheel. A second, lighter lock for the front wheel can be added.
                    </p>
                </div>
            </div>

            <div class="card mb-5 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="section-icon"><i class="bi bi-tools"></i></div>
                    <h4 class="card-title" style="color: var(--primary);">Essential Repair Kit for the Road</h4>
                    <p class="card-text text-start" style="color: var(--text-dark);">
                        Even with careful maintenance, flats and minor issues can happen. Carry a small but essential repair kit on every ride:
                        <ul class="text-start">
                            <li><strong>Spare tube:</strong> Ensure it's the correct size for your tires. Or a patch kit if you prefer and know how to use it.</li>
                            <li><strong>Tire levers:</strong> Two or three are usually enough to remove the tire from the rim.</li>
                            <li><strong>Mini-pump or CO2 inflator:</strong> To inflate your tire after a repair. CO2 is faster but single-use; a mini-pump is slower but reusable.</li>
                            <li><strong>Multi-tool:</strong> For minor adjustments (e.g., tightening loose bolts, adjusting brake or derailleur cables, adjusting saddle height).</li>
                            <li><strong>Small amount of cash:</strong> For emergency food, water, or public transport.</li>
                            <li><strong>Phone:</strong> Fully charged for emergencies or navigation.</li>
                        </ul>
                        Knowing how to perform a basic flat tire repair is a fundamental skill for any cyclist and can save you from being stranded.
                    </p>
                </div>
            </div>

            <div class="card mb-5 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="section-icon"><i class="bi bi-megaphone"></i></div>
                    <h4 class="card-title" style="color: var(--primary);">Auditory Safety: Bell or Horn</h4>
                    <p class="card-text text-start" style="color: var(--text-dark);">
                        A bell or horn is an often-overlooked but crucial safety device, especially in urban environments or on shared paths. It allows you to alert pedestrians, other cyclists, or even slow-moving vehicles to your presence without having to shout. Use it courteously and in advance, not aggressively. A polite "ding-ding" can often prevent sudden movements from others that might lead to a collision.
                        <br><br>
                        <strong>Choosing a Bell:</strong> There are various types, from classic "ding-dong" bells to more modern, discreet ones. Some even offer air horns for more assertive warnings on busy roads. Choose one that is easy to access and loud enough for your riding environment.
                    </p>
                </div>
            </div>

            <div class="alert alert-info text-center mt-5" role="alert" style="background-color: var(--accent); color: var(--text-light); border-color: var(--accent);">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> Proper equipment and diligent preparation are key to keeping your rides worry-free and safe for yourself and others on the road. Never compromise on safety! Always be prepared for the unexpected.
            </div>

            <div class="card bg-light mb-4" style="background-color: var(--bg-light) !important;">
                <div class="card-body">
                    <h5 class="card-title" style="color: var(--primary);"><i class="bi bi-link-45deg me-2"></i>Resources & References</h5>
                    <p class="card-text text-start" style="color: var(--text-dark);">For more comprehensive guides on bicycle safety and equipment, consider these resources: </p>
                    <a href="https://www.nhtsa.gov/sites/nhtsa.gov/files/documents/812061-bicyclesafetyguide.pdf" class="btn btn-sm btn-outline-primary me-2 mb-2" target="_blank" style="color: var(--primary); border-color: var(--primary);">NHTSA Bicycle Safety Guide <i class="bi bi-box-arrow-up-right"></i></a>
                    <a href="https://www.bicyclesafe.com/" class="btn btn-sm btn-outline-primary me-2 mb-2" target="_blank" style="color: var(--primary); border-color: var(--primary);">BicycleSafe.com Tips <i class="bi bi-box-arrow-up-right"></i></a>
                    <a href="https://www.rei.com/learn/expert-advice/bicycle-safety.html" class="btn btn-sm btn-outline-primary mb-2" target="_blank" style="color: var(--primary); border-color: var(--primary);">REI Bike Safety Checklist <i class="bi bi-box-arrow-up-right"></i></a>
                    <a href="https://www.safewheel.org/philippine-bicycle-laws/" class="btn btn-sm btn-outline-primary mb-2" target="_blank" style="color: var(--primary); border-color: var(--primary);">Philippine Bicycle Laws (SafeWheel) <i class="bi bi-box-arrow-up-right"></i></a>
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