<?php require 'config.php'; ?>
<?php include 'header.php'; ?>

<!-- Hero Section -->
<div class="hero-section" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://1.bp.blogspot.com/-Nxv1xP7Xzxg/YaQilSLtFvI/AAAAAAAApDg/N9ihb0ah3UcyQ_hOefqpwuwxB4rwMQNbgCNcBGAsYHQ/s1000/DSC02444.JPG'); background-size: cover; background-position: center; height: 500px; display: flex; align-items: center; justify-content: center; color: white; text-align: center;">
    <div class="container">
        <h1 class="display-4">Welcome to CampEase</h1>
        <p class="lead">Book your perfect campsite at Bagan Lalang easily online.</p>
        <a href="sites.php" class="btn btn-primary btn-lg">View Available Campsites</a>
    </div>
</div>

<!-- About Section -->
<section id="about" class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="mb-4">About Bagan Lalang Campsite</h2>
                <p class="lead">Located in Selangor, Bagan Lalang is a beautiful coastal area perfect for camping, picnics, and outdoor activities.</p>
                <p>Our campsite offers a variety of tent spaces surrounded by nature. Whether you're a seasoned camper or a first-timer, we provide a safe and enjoyable environment for families, friends, and solo travelers.</p>
                <p>With CampEase, you can now reserve your spot online and avoid the hassle of walk-in registrations.</p>
            </div>
            <div class="col-lg-6">
                <img src="https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEhEq3KNwtABDqoKKc9erZzmYvRE-DGoTKpXGRPa2RPLGvT9lEO1L9D3HhgoAApzimbTHOr6iHAsFPm7lGxua8r2Hdp5SERuHt2m9VgYtzCnoZQao2OnQz4nYXB5HC4hlbT-2gtT/s1600/SNC02149.jpg" alt="Camping" class="img-fluid rounded shadow" style="max-width: 80%; height: auto;">
            </div>
        </div>
    </div>
</section>

<!-- ========== VISUAL CAMPSITE MAP ========== -->
<section class="map-page-section py-5" id="campsite-map">
    <div class="container">
        <h1 class="text-center mb-4">MAP</h1>
        <p class="text-center mb-5">Click any campsite to view details and book.</p>

        <div class="map-canvas">
            <!-- Background decorations -->
            <div class="map-deco">
                <div class="tree t1"><i class="fas fa-tree"></i></div>
                <div class="tree t2"><i class="fas fa-tree"></i></div>
                <div class="tree t3"><i class="fas fa-tree"></i></div>
                <div class="tree t4"><i class="fas fa-tree"></i></div>
                <div class="river"></div>
                <div class="path"></div>
            </div>

            <!-- Registration marker (left of Sunny Area) -->
            <div class="registration-marker">
                <i class="fas fa-id-card"></i>
                <span>Registration</span>
            </div>

            <!-- Campsites (dynamic) -->
            <?php
            $sites = $pdo->query("SELECT id, name FROM sites WHERE status = 'active' ORDER BY id")->fetchAll();
            if (count($sites) == 0) {
                echo '<div class="alert alert-warning text-center">No campsites found. Please add campsites in the admin panel.</div>';
            } else {
                $positions = ['pos-sunny', 'pos-sunset', 'pos-mangrove', 'pos-campfire', 'pos-rimba'];
                $icons = ['fa-sun', 'fa-cloud-sun', 'fa-water', 'fa-fire', 'fa-tree'];
            ?>
            <div class="campsite-arrangement">
                <?php foreach ($sites as $index => $site): 
                    $pos = $positions[$index] ?? 'pos-extra';
                    $icon = $icons[$index] ?? 'fa-campground';
                ?>
                    <a href="site-details.php?site_id=<?= $site['id'] ?>" class="campsite-marker <?= $pos ?>">
                        <div class="campsite-card">
                            <i class="fas <?= $icon ?>"></i>
                            <h3><?= htmlspecialchars($site['name']) ?></h3>
                            <span class="view-details">View details →</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php } ?>

            <!-- Facility badges -->
            <div class="facility-group">
                <div class="facility-badge"><i class="fas fa-toilet"></i> Toilets</div>
                <div class="facility-badge"><i class="fas fa-mosque"></i> Surau</div>
                <div class="facility-badge"><i class="fas fa-utensils"></i> Dining Area</div>
            </div>

            <!-- Beach label -->
            <div class="beach-tag">
                <i class="fas fa-umbrella-beach"></i> Bagan Lalang Beach
            </div>
        </div>
    </div>
</section>
<!-- ========== END MAP ========== -->

<!-- FAQ Section -->
<section id="faq" class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Frequently Asked Questions</h2>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                1. How do I make a campsite booking?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                To make a booking, choose your preferred campsite option, fill in the required booking details, and submit your reservation through the system.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                2. Can I check campsite availability before booking?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes. The system allows users to check campsite availability before making a reservation.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                3. Do I need to create an account to use the website?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, you need to create an account to make a booking. Registration allows you to manage your bookings and track their status.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                4. Can I choose my tent location?
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes. Users can view the campsite layout and choose their preferred tent location based on availability.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFive">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                5. How will I know if my booking is successful?
                            </button>
                        </h2>
                        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Once the booking is submitted successfully, the system will display a confirmation message or booking status. You can also view your bookings in your dashboard.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingSix">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                6. Can I cancel my booking?
                            </button>
                        </h2>
                        <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes. Users can cancel their booking through the system, subject to the booking terms and conditions. Cancellations are allowed for pending or confirmed bookings (if more than 24 hours before check‑in).
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingSeven">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
                                7. What payment method is available?
                            </button>
                        </h2>
                        <div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingSeven" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Users can make payment using the QR payment method provided by the system and upload the payment receipt for verification.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingEight">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEight" aria-expanded="false" aria-controls="collapseEight">
                                8. How will my payment be verified?
                            </button>
                        </h2>
                        <div id="collapseEight" class="accordion-collapse collapse" aria-labelledby="headingEight" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                After the receipt is uploaded, the booking will remain pending until the admin verifies the payment.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingNine">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNine" aria-expanded="false" aria-controls="collapseNine">
                                9. Can I edit my booking details after submission?
                            </button>
                        </h2>
                        <div id="collapseNine" class="accordion-collapse collapse" aria-labelledby="headingNine" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Currently, editing of booking details is not supported. If you need to make changes, please contact the admin or cancel and re‑book.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTen">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTen" aria-expanded="false" aria-controls="collapseTen">
                                10. Who can I contact if I have a problem with my booking?
                            </button>
                        </h2>
                        <div id="collapseTen" class="accordion-collapse collapse" aria-labelledby="headingTen" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Users can contact the campsite administrator or staff through the contact information provided on the website.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Contact Section -->
<section id="contact" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Contact Us</h2>
        <div class="row">
            <div class="col-md-6">
                <h4>Get in Touch</h4>
                <p><i class="fas fa-map-marker-alt me-2"></i> Bagan Lalang, Selangor, Malaysia</p>
                <p><i class="fas fa-phone me-2"></i> +60 12-345 6789</p>
                <p><i class="fas fa-envelope me-2"></i> info@campease.com</p>
                <div class="mt-4">
                    <a href="#" class="btn btn-outline-primary me-2"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="btn btn-outline-primary me-2"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="btn btn-outline-primary"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            <div class="col-md-6">
                <form>
                    <div class="mb-3">
                        <input type="text" class="form-control" placeholder="Your Name" required>
                    </div>
                    <div class="mb-3">
                        <input type="email" class="form-control" placeholder="Your Email" required>
                    </div>
                    <div class="mb-3">
                        <textarea class="form-control" rows="4" placeholder="Your Message" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>

<style>
/* ===== VISUAL MAP STYLES (FINAL) ===== */
.map-page-section {
    background: #faf0e6;
    border-top: 4px solid #e8d9c5;
    border-bottom: 4px solid #e8d9c5;
    font-family: 'Quicksand', sans-serif;
}

.map-canvas {
    position: relative;
    background: #e6dbc6;
    background-image: radial-gradient(circle, #dcc9b5 1px, transparent 1px);
    background-size: 25px 25px;
    border-radius: 60px;
    padding: 2rem;
    min-height: 650px;
    box-shadow: inset 0 0 0 2px rgba(255,250,240,0.8), 0 15px 30px rgba(0,0,0,0.1);
    overflow: hidden;
}

.map-deco {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1;
}

.tree {
    position: absolute;
    color: #5c874a;
    font-size: 2rem;
    opacity: 0.5;
}
.t1 { top: 5%; left: 3%; }
.t2 { top: 15%; right: 5%; }
.t3 { bottom: 20%; left: 8%; }
.t4 { bottom: 5%; right: 15%; }

.river {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 70px;
    background: linear-gradient(135deg, #7fb7c2, #5f9eb0);
    opacity: 0.5;
    border-radius: 0 0 60px 60px;
}

.registration-marker {
    position: absolute;
    left: 2%;
    top: 45%;
    transform: translateY(-50%);
    background: rgba(255,250,240,0.9);
    border-radius: 50px;
    padding: 0.6rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    z-index: 20;
    backdrop-filter: blur(4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    font-weight: 500;
    color: #6B4F3C;
    white-space: nowrap;
}
.registration-marker i {
    font-size: 1.2rem;
    color: #a67b5b;
}
.registration-marker span {
    font-size: 0.9rem;
}

.campsite-arrangement {
    position: relative;
    z-index: 10;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    grid-template-rows: auto auto auto;
    gap: 1.8rem;
    padding: 2rem 1rem;
    min-height: 450px;
}

.campsite-marker {
    text-decoration: none;
    transition: transform 0.2s;
}
.campsite-marker:hover {
    transform: scale(1.03);
    z-index: 20;
}

.campsite-card {
    background: rgba(245,230,211,0.95);
    border-radius: 35px;
    padding: 1.2rem 1rem;
    text-align: center;
    box-shadow: 0 8px 18px rgba(107,79,60,0.15);
    backdrop-filter: blur(2px);
    border: 1px solid white;
    transition: all 0.2s;
}
.campsite-card i {
    font-size: 2rem;
    color: #a67b5b;
    display: block;
    margin-bottom: 0.6rem;
}
.campsite-card h3 {
    margin: 0 0 0.6rem;
    font-size: 1.2rem;
    font-weight: 600;
    color: #6B4F3C;
}
.view-details {
    font-size: 0.9rem;
    color: #a67b5b;
    font-weight: 600;
    opacity: 0.8;
    transition: opacity 0.2s;
}
.campsite-marker:hover .view-details {
    opacity: 1;
}

/* Position each campsite in a natural curve */
.pos-sunny {
    grid-column: 2 / 3;
    grid-row: 1 / 2;
    justify-self: center;
}
.pos-sunset {
    grid-column: 3 / 4;
    grid-row: 1 / 2;
    justify-self: start;
}
.pos-mangrove {
    grid-column: 1 / 2;
    grid-row: 2 / 3;
    justify-self: end;
}
.pos-campfire {
    grid-column: 3 / 4;
    grid-row: 2 / 3;
    justify-self: start;
}
.pos-rimba {
    grid-column: 2 / 3;
    grid-row: 3 / 4;
    justify-self: center;
}

.facility-group {
    position: absolute;
    bottom: 1rem;
    right: 1rem;
    background: rgba(255,250,240,0.9);
    border-radius: 50px;
    padding: 0.8rem 1.2rem;
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    z-index: 20;
    backdrop-filter: blur(4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.facility-badge {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.85rem;
    color: #6B4F3C;
    font-weight: 500;
}
.facility-badge i {
    color: #a67b5b;
    font-size: 1rem;
}

.beach-tag {
    position: absolute;
    bottom: 0.5rem;
    left: 50%;
    transform: translateX(-50%);
    background: #a67b5b;
    color: white;
    padding: 0.5rem 2rem;
    border-radius: 40px;
    font-weight: 600;
    font-size: 1rem;
    white-space: nowrap;
    z-index: 15;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.beach-tag i {
    margin-right: 0.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .map-canvas {
        min-height: 750px;
        padding: 1rem;
    }
    .campsite-arrangement {
        grid-template-columns: 1fr;
        gap: 1rem;
        padding: 1rem;
        min-height: auto;
    }
    .pos-sunny, .pos-sunset, .pos-mangrove, .pos-campfire, .pos-rimba {
        grid-column: 1 / 2 !important;
        grid-row: auto !important;
        justify-self: center !important;
    }
    .facility-group {
        bottom: 0.5rem;
        right: 0.5rem;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
    }
    .beach-tag {
        font-size: 0.85rem;
        padding: 0.4rem 1.2rem;
        bottom: 0.3rem;
        white-space: nowrap;
    }
    .registration-marker {
        left: 1%;
        top: 40%;
        padding: 0.4rem 0.8rem;
        gap: 0.4rem;
    }
    .registration-marker i {
        font-size: 1rem;
    }
    .registration-marker span {
        font-size: 0.8rem;
    }
}
</style>