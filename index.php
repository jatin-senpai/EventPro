<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}
$page_title = 'Professional Event Planning';
include 'includes/header.php';
?>

<!-- Hero Section-->
<section class="relative h-screen">
    <!-- Carousel container -->
    <div class="absolute inset-0">
        <div class="carousel relative h-full">
            <div class="slide absolute inset-0 bg-[url('assets/images/image20.jpg')] bg-cover bg-center "></div>
            <div class="slide absolute inset-0 bg-[url('assets/images/image16.jpg')] bg-cover bg-center hidden"></div>
            <div class="slide absolute inset-0 bg-[url('assets/images/image2.jpg')] bg-cover bg-center hidden"></div>
            <div class="slide absolute inset-0 bg-[url('assets/images/image12.jpg')] bg-cover bg-center hidden"></div>
        </div>
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    </div>
    <div class="relative z-10 h-full flex items-center justify-center text-center text-white px-4">
        <div class="max-w-4xl">
            <h1 class="text-5xl md:text-6xl font-bold mb-6">Plan Your Events with Ease</h1>
            <p class="text-xl md:text-2xl mb-8">Streamline your event planning process with our comprehensive management platform</p>
            <a href="events.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg text-lg hover:bg-blue-700 inline-block">Get Started</a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Key Features</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="p-6 bg-gray-50 rounded-lg">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Event Management</h3>
                <p class="text-gray-600">Create and manage multiple events with ease</p>
            </div>
            <div class="p-6 bg-gray-50 rounded-lg">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Guest Management</h3>
                <p class="text-gray-600">Track RSVPs and manage guest lists efficiently</p>
            </div>
            <div class="p-6 bg-gray-50 rounded-lg">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-handshake text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Vendor Coordination</h3>
                <p class="text-gray-600">Connect and communicate with vendors seamlessly</p>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-16 bg-blue-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
            <div class="p-6">
                <div class="text-4xl font-bold text-blue-600 mb-2">500+</div>
                <div class="text-gray-600">Events Managed</div>
            </div>
            <div class="p-6">
                <div class="text-4xl font-bold text-blue-600 mb-2">1000+</div>
                <div class="text-gray-600">Happy Clients</div>
            </div>
            <div class="p-6">
                <div class="text-4xl font-bold text-blue-600 mb-2">50+</div>
                <div class="text-gray-600">Team Members</div>
            </div>
            <div class="p-6">
                <div class="text-4xl font-bold text-blue-600 mb-2">98%</div>
                <div class="text-gray-600">Client Satisfaction</div>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Event Gallery</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div class="relative group overflow-hidden rounded-lg">
                <img src="assets/images/image20.jpg" alt="Event" class="w-full h-64 object-cover transition-transform duration-300 group-hover:scale-110">
                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                    <span class="text-white text-lg font-medium">Wedding </span>
                </div>
            </div>
            <div class="relative group overflow-hidden rounded-lg">
                <img src="assets/images/image5.jpg" alt="Event" class="w-full h-64 object-cover transition-transform duration-300 group-hover:scale-110">
                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                    <span class="text-white text-lg font-medium">Birthday </span>
                </div>
            </div>
            <div class="relative group overflow-hidden rounded-lg">
                <img src="assets/images/image2.jpg" alt="Event" class="w-full h-64 object-cover transition-transform duration-300 group-hover:scale-110">
                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                    <span class="text-white text-lg font-medium">Stage Show</span>
                </div>
            </div>
            <div class="relative group overflow-hidden rounded-lg">
                <img src="assets/images/image17.jpg" alt="Event" class="w-full h-64 object-cover transition-transform duration-300 group-hover:scale-110">
                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                    <span class="text-white text-lg font-medium">Wedding</span>
                </div>
            </div>
            <div class="relative group overflow-hidden rounded-lg">
                <img src="assets/images/image6.webp" alt="Event" class="w-full h-64 object-cover transition-transform duration-300 group-hover:scale-110">
                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                    <span class="text-white text-lg font-medium">Birthday</span>
                </div>
            </div>
            <div class="relative group overflow-hidden rounded-lg">
                <img src="assets/images/image4.jpg" alt="Event" class="w-full h-64 object-cover transition-transform duration-300 group-hover:scale-110">
                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                    <span class="text-white text-lg font-medium">Dance Parties</span>
                </div>
            </div>
            <div class="relative group overflow-hidden rounded-lg">
                <img src="assets/images/image7.jpg" alt="Event" class="w-full h-64 object-cover transition-transform duration-300 group-hover:scale-110">
                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                    <span class="text-white text-lg font-medium">Cocktail parties</span>
                </div>
            </div>
            <div class="relative group overflow-hidden rounded-lg">
                <img src="assets/images/image18.jpg" alt="Event" class="w-full h-64 object-cover transition-transform duration-300 group-hover:scale-110">
                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                    <span class="text-white text-lg font-medium">Dances and Balls</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Meet Our Team</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <img src="assets/images/jatin.jpeg" alt="Team Member" class="w-full h-64 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-semibold mb-2">Jatin Yadav</h3>
                    <p class="text-gray-600 mb-4">Lead Developer</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-blue-600 hover:text-blue-700"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-blue-400 hover:text-blue-500"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <img src="assets/images/saurabh.jpg" alt="Team Member" class="w-full h-64 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-semibold mb-2">Saurabh Kumar</h3>
                    <p class="text-gray-600 mb-4">Lead Developer</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-blue-600 hover:text-blue-700"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-blue-400 hover:text-blue-500"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <img src="assets/images/anjali.jpeg" alt="Team Member" class="w-full h-64 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-semibold mb-2">Anjali</h3>
                    <p class="text-gray-600 mb-4">Lead Developer</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-blue-600 hover:text-blue-700"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-blue-400 hover:text-blue-500"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <img src="assets/images/Akshay.jpeg" alt="Team Member" class="w-full h-64 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-semibold mb-2">Akshay Kumar Shaw</h3>
                    <p class="text-gray-600 mb-4">Lead Developer</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-blue-600 hover:text-blue-700"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-blue-400 hover:text-blue-500"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <div>
                <h2 class="text-3xl font-bold mb-6">Get in Touch</h2>
                <p class="text-gray-600 mb-8">Have questions about our services? Reach out to us!</p>
                <div class="space-y-4">
                    <div class="flex items-start space-x-4">
                        <i class="fas fa-map-marker-alt text-blue-600 mt-1"></i>
                        <div>
                            <h3 class="font-semibold">Address</h3>
                            <p class="text-gray-600">EventPro office, New Delhi, India</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <i class="fas fa-envelope text-blue-600 mt-1"></i>
                        <div>
                            <h3 class="font-semibold">Email</h3>
                            <p class="text-gray-600">eventPro@gmail.com</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <i class="fas fa-phone text-blue-600 mt-1"></i>
                        <div>
                            <h3 class="font-semibold">Phone</h3>
                            <p class="text-gray-600">+91 9876543210</p>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <form action="handlers/contact.php" method="POST" class="space-y-6" data-ajax="false">
                    <div>
                        <label for="contact_name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" 
                               id="contact_name" 
                               name="name" 
                               required 
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3">
                    </div>
                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" 
                               id="contact_email" 
                               name="email" 
                               required 
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3">
                    </div>
                    <div>
                        <label for="contact_message" class="block text-sm font-medium text-gray-700">Message</label>
                        <textarea id="contact_message" 
                                  name="message" 
                                  rows="4" 
                                  required 
                                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3"></textarea>
                    </div>
                    <button type="submit" 
                            class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                        Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Updated Footer -->
<footer class="bg-gray-900 text-white py-12">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <h3 class="text-xl font-bold mb-4">EventPro</h3>
                <p class="text-gray-400">Your complete event planning solution</p>
            </div>
            <div>
                <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                <ul class="space-y-2">
                    <li><a href="events.php" class="text-gray-400 hover:text-white">Events</a></li>
                    <li><a href="vendors.php" class="text-gray-400 hover:text-white">Vendors</a></li>
                    <li><a href="budget.php" class="text-gray-400 hover:text-white">Budget</a></li>
                    <li><a href="timeline.php" class="text-gray-400 hover:text-white">Timeline</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-lg font-semibold mb-4">Contact</h4>
                <ul class="space-y-2 text-gray-400">
                    <li>Email: eventPro@gmail.com</li>
                    <li>Phone: +91 9876543210</li>
                    <li>Address: EventPro office, New Delhi, India</li>
                </ul>
            </div>
            <div>
                <h4 class="text-lg font-semibold mb-4">Follow Us</h4>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook text-xl"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter text-xl"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-instagram text-xl"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-linkedin text-xl"></i></a>
                </div>
            </div>
        </div>
        <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
            <p>&copy; EventPro </p>
        </div>
    </div>
</footer>

<!-- Carousel Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.slide');
    let currentSlide = 0;

    function nextSlide() {
        slides[currentSlide].classList.add('hidden');
        currentSlide = (currentSlide + 1) % slides.length;
        slides[currentSlide].classList.remove('hidden');
    }
    setInterval(nextSlide, 5000);
});
</script>

</body>
</html>

