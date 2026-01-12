    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>SecScope Tech Store</h5>
                    <p>Your trusted partner for all tech needs. We offer the latest gadgets and electronics at competitive prices.</p>
                    <div class="social-icons">
                        <a href="#" class="text-warning me-2"><i class="fab fa-facebook-f fa-lg"></i></a>
                        <a href="#" class="text-warning me-2"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-warning me-2"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-warning me-2"><i class="fab fa-linkedin-in fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-md-2">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">Home</a></li>
                        <li><a href="products.php" class="text-white">Products</a></li>
                        <li><a href="about.php" class="text-white">About Us</a></li>
                        <li><a href="contact.php" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Categories</h5>
                    <ul class="list-unstyled">
                        <li><a href="products.php?category=Laptops" class="text-white">Laptops</a></li>
                        <li><a href="products.php?category=Smartphones" class="text-white">Smartphones</a></li>
                        <li><a href="products.php?category=Tablets" class="text-white">Tablets</a></li>
                        <li><a href="products.php?category=Accessories" class="text-white">Accessories</a></li>
                        <li><a href="products.php?category=Smart Devices" class="text-white">Smart Devices</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact Info</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-map-marker-alt me-2 text-warning"></i> 123 Tech Street, Silicon Valley, CA</li>
                        <li><i class="fas fa-phone me-2 text-warning"></i> (555) 123-4567</li>
                        <li><i class="fas fa-envelope me-2 text-warning"></i> info@secscope.net</li>
                    </ul>
                </div>
            </div>
            <hr class="bg-warning">
            <div class="text-center">
                <p class="mb-0">&copy; 2023 SecScope Tech Store. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
<?php
// NO whitespace after this line!
// End output buffering and flush if headers haven't been sent
if (ob_get_status() && !headers_sent()) {
    ob_end_flush();
}