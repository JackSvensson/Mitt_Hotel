<?php
$page_title = 'Contact Us - Glass Onion Hotel';
$additional_css = 'css/contact.css';
require_once 'includes/header.php';
?>

<main>
   <div class="contact-container">
       <div class="contact-info">
           <h2>Mysterious Coordinates</h2>
           
           <div class="info-item">
               <h3>Location</h3>
               <p>The Glass Onion Hotel</p>
               <p>Somewhere on Mystery Island</p>
               <p>Coordinates provided upon reservation...</p>
           </div>
   
           <div class="info-item">
               <h3>Contact the Front Desk</h3>
               <p>Concierge: +1 (555) 123-4568</p>
               <p><em>(Available for cryptic inquiries 24/7)</em></p>
           </div>
   
           <div class="info-item">
               <h3>Digital Correspondence</h3>
               <p>mysteries@glassonionhotel.com</p>
               <p><em>(Responses may appear in unexpected ways...)</em></p>
           </div>
   
           <div class="info-item">
               <h3>Hotel Hours</h3>
               <p>Check-in: 13:00 (When the sun reaches its peak)</p>
               <p>Check-out: 11:00 (Before the mysteries deepen)</p>
               <p>Front Desk: Always watching, 24/7</p>
           </div>
       </div>
   
       <div class="contact-form">
           <h2>Send us a Secret Message</h2>
           <form action="process_contact.php" method="POST">
               <div class="form-group">
                   <label for="name">Your Alias:</label>
                   <input type="text" id="name" name="name" required>
               </div>
   
               <div class="form-group">
                   <label for="email">Secret Communication Line (Email):</label>
                   <input type="email" id="email" name="email" required>
               </div>
   
               <div class="form-group">
                   <label for="subject">Nature of Inquiry:</label>
                   <input type="text" id="subject" name="subject" required>
               </div>
   
               <div class="form-group">
                   <label for="message">Your Message (in code or plain text):</label>
                   <textarea id="message" name="message" required></textarea>
               </div>
   
               <button type="submit" class="submit-btn">Dispatch Message</button>
           </form>
       </div>
   </div>
</main>

<footer>
   <p>&copy; 2025 Glass Onion Hotel. All rights reserved.</p>
</footer>
</body>
</html>