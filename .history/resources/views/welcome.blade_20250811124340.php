<!-- resources/views/welcome.blade.php -->
    <!-- Hero Section -->
    <section class="bg-blue-500 text-white py-20">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-4">Welcome to Palm Oil Shop</h1>
            <p class="text-lg md:text-xl mb-6">Your trusted source for premium quality palm oil and related products.</p>
            <a href="#about" class="bg-white text-blue-500 px-6 py-3 rounded-lg font-semibold hover:bg-blue-800 hover:text-white transition">Learn More</a>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-16 bg-gray-100">
        <div class="container mx-auto px-4 grid md:grid-cols-2 gap-8 items-center">
            <img src="/images/about.jpg" alt="About Us" class="rounded-lg shadow-lg">
            <div>
                <h2 class="text-3xl font-bold text-blue-500 mb-4">About Us</h2>
                <p class="text-gray-700 mb-4">
                    We are committed to providing fresh, high-quality palm oil, sourced directly from trusted farms.
                </p>
                <a href="#services" class="bg-blue-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-800 transition">Our Services</a>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-16">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold text-blue-500 mb-10">Our Services</h2>
            <div class="grid md:grid-cols-3 gap-8">
                @foreach (['Palm Oil Supply', 'Wholesale & Retail', 'Home Delivery'] as $service)
                    <div class="bg-white rounded-lg shadow-md p-6 hover:bg-blue-500 hover:text-white transition">
                        <h3 class="text-xl font-semibold mb-4">{{ $service }}</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section id="gallery" class="py-16 bg-gray-100">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold text-blue-500 mb-10">Gallery</h2>
            <div class="grid md:grid-cols-4 gap-4">
                @foreach (range(1,8) as $img)
                    <img src="/images/gallery{{ $img }}.jpg" class="rounded-lg shadow hover:scale-105 transition">
                @endforeach
            </div>
        </div>
    </section>

    <!-- News Section -->
    <section id="news" class="py-16">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold text-blue-500 mb-10">Latest News</h2>
            <div class="grid md:grid-cols-3 gap-8">
                @foreach (range(1,3) as $news)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:bg-blue-500 hover:text-white transition">
                        <img src="/images/news{{ $news }}.jpg" alt="News" class="w-full h-48 object-cover">
                        <div class="p-4">
                            <h3 class="text-xl font-semibold mb-2">News Title {{ $news }}</h3>
                            <p>Short description of the news item.</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-blue-500 mb-10 text-center">FAQ</h2>
            <div class="space-y-4">
                @foreach ([['What is your delivery time?', 'We deliver within 24 hours.'],
                           ['Do you offer wholesale prices?', 'Yes, for bulk orders.']] as $faq)
                    <details class="bg-white p-4 rounded-lg shadow">
                        <summary class="cursor-pointer font-semibold">{{ $faq[0] }}</summary>
                        <p class="mt-2">{{ $faq[1] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section id="testimonials" class="py-16">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold text-blue-500 mb-10">Testimonials</h2>
            <div class="grid md:grid-cols-3 gap-8">
                @foreach (['Great service!', 'High quality oil!', 'Fast delivery!'] as $review)
                    <div class="bg-white p-6 rounded-lg shadow hover:bg-blue-500 hover:text-white transition">
                        <p class="mb-4">"{{ $review }}"</p>
                        <p class="font-semibold">⭐⭐⭐⭐⭐</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Contact -->
    <section id="contact" class="py-16 bg-gray-100">
        <div class="container mx-auto px-4 grid md:grid-cols-2 gap-8">
            <div>
                <h2 class="text-3xl font-bold text-blue-500 mb-4">Contact Us</h2>
                <p>Email: info@palmoilshop.com</p>
                <p>Phone: +234 800 000 0000</p>
                <p>Address: Main Street, Abuja, Nigeria</p>
            </div>
            <form class="bg-white p-6 rounded-lg shadow space-y-4">
                <input type="text" placeholder="Your Name" class="w-full border p-2 rounded">
                <input type="email" placeholder="Your Email" class="w-full border p-2 rounded">
                <textarea placeholder="Message" rows="4" class="w-full border p-2 rounded"></textarea>
                <button type="submit" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-800 transition">Send Message</button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-blue-500 text-white py-6 text-center">
        <p>&copy; {{ date('Y') }} Palm Oil Shop. All rights reserved.</p>
    </footer>
