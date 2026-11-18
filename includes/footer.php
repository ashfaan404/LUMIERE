</main>
<footer class="site-footer" style="background-color: #111111; border-top: 1px solid #222222; padding: 4rem 2rem 2rem; font-family: 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #e0e0e0;">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 2rem;">
        
        <!-- Column 1: Brand Info -->
        <div style="flex: 1.5; min-width: 250px; max-width: 350px;">
            <h3 style="font-size: 1.2rem; font-weight: 800; font-family: 'Montserrat', sans-serif; letter-spacing: 0.5px; margin-bottom: 1.2rem; color: #ffffff;">LUMIERE</h3>
            <p style="font-size: 0.85rem; color: #a0a0a0; line-height: 1.8; margin-bottom: 2rem;">
                Discover the world's most exquisite fragrances. Curated collections of luxury perfumes for those who appreciate the finer things in life.
            </p>
            <div style="display: flex; gap: 1.2rem;">
                <a href="#" style="color: #d0d0d0; font-size: 1.1rem; transition: color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#d0d0d0'"><i class="fab fa-instagram"></i></a>
                <a href="#" style="color: #d0d0d0; font-size: 1.1rem; transition: color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#d0d0d0'"><i class="fab fa-facebook-f"></i></a>
                <a href="#" style="color: #d0d0d0; font-size: 1.1rem; transition: color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#d0d0d0'"><i class="fab fa-twitter"></i></a>
                <a href="#" style="color: #d0d0d0; font-size: 1.1rem; transition: color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#d0d0d0'"><i class="fab fa-youtube"></i></a>
            </div>
        </div>

        <!-- Column 2: Shop Links -->
        <div style="flex: 1; min-width: 150px;">
            <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 1.5rem; color: #ffffff;">Shop</h4>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 1rem;">
                <li><a href="<?php echo $pages_base; ?>shop.php" style="color: #a0a0a0; font-size: 0.85rem; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#a0a0a0'">All Perfumes</a></li>
                <li><a href="<?php echo $pages_base; ?>shop.php?category=1" style="color: #a0a0a0; font-size: 0.85rem; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#a0a0a0'">Men's Collection</a></li>
                <li><a href="<?php echo $pages_base; ?>shop.php?category=2" style="color: #a0a0a0; font-size: 0.85rem; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#a0a0a0'">Women's Collection</a></li>
                <li><a href="<?php echo $pages_base; ?>shop.php?new=1" style="color: #a0a0a0; font-size: 0.85rem; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#a0a0a0'">New Arrivals</a></li>
                <li><a href="<?php echo $pages_base; ?>shop.php?best=1" style="color: #a0a0a0; font-size: 0.85rem; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#a0a0a0'">Best Sellers</a></li>
            </ul>
        </div>

        <!-- Column 3: Quick Links -->
        <div style="flex: 1; min-width: 150px;">
            <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 1.5rem; color: #ffffff;">Quick Links</h4>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 1rem;">
                <li><a href="<?php echo $pages_base; ?>home.php" style="color: #a0a0a0; font-size: 0.85rem; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#a0a0a0'">Home</a></li>
                <li><a href="<?php echo $pages_base; ?>shop.php" style="color: #a0a0a0; font-size: 0.85rem; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#a0a0a0'">Shop</a></li>
                <li><a href="<?php echo $pages_base; ?>promotions.php" style="color: #ffffff; font-size: 0.85rem; text-decoration: none; transition: color 0.2s; font-weight: 600;">Offers</a></li>
                <li><a href="<?php echo $pages_base; ?>support.php" style="color: #a0a0a0; font-size: 0.85rem; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#a0a0a0'">Support</a></li>
                <li><a href="<?php echo $pages_base; ?>cart.php" style="color: #a0a0a0; font-size: 0.85rem; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#a0a0a0'">Cart</a></li>
                <li><a href="<?php echo $pages_base; ?>compare.php" style="color: #a0a0a0; font-size: 0.85rem; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#a0a0a0'">Compare</a></li>
            </ul>
        </div>

        <!-- Column 4: Payment Methods -->
        <div style="flex: 1; min-width: 150px; display: flex; gap: 0.5rem; justify-content: flex-end; align-items: flex-start;">
            <div style="border: 1px solid #333333; padding: 0.4rem 0.8rem; border-radius: 4px; font-size: 0.65rem; font-weight: 700; color: #bbbbbb; background: #1a1a1a; letter-spacing: 0.5px;">VISA</div>
            <div style="border: 1px solid #333333; padding: 0.4rem 0.8rem; border-radius: 4px; font-size: 0.65rem; font-weight: 700; color: #bbbbbb; background: #1a1a1a; letter-spacing: 0.5px;">MC</div>
        </div>

    </div>

    <div style="max-width: 1200px; margin: 4rem auto 0; padding-top: 1.5rem; border-top: 1px solid #222222; font-size: 0.75rem; color: #777777; display: flex; justify-content: flex-start; align-items: center;">
        <p style="margin: 0;">&copy; 2026 Lumiere Perfumes. All rights reserved.</p>
    </div>
</footer>

<script src="../assets/js/script.js"></script>
</body>
</html>
