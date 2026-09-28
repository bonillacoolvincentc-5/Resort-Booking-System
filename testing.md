# YP4K Resort - Testing Guide

Use this guide to test specific features after hosting the website to ensure everything functions properly without errors.

## 1. Testing Extra Rooms Booking Consolidation

**Goal:** Verify that booking a primary room and adding extra rooms creates only ONE pending booking for the admin, with a combined total price and the extra rooms listed in the special requests.

**Steps to Test:**
1. Log in to the User Dashboard as a regular user.
2. Go to **Create Booking** and select a primary room (e.g., VIP Room).
3. Click the **Add Another Room** button and select a different room (e.g., Cottage).
4. Fill out the booking details (guests, dates, payment proof) and submit the form.
5. Log in to the **Admin Dashboard** and go to the Pending Bookings section.
6. Verify the following:
   - There should only be **ONE** pending booking generated from that transaction (not two).
   - The **Total Amount** should correctly equal the price of the VIP Room PLUS the price of the Cottage (it should not be 0.00 for the extra room).
   - Click **View Details** on the booking.
   - Look at the **Special Requests / Notes** section. You should see a text block labeled `[Additional Rooms]:` explicitly listing the Cottage, its guest count, its dates, and its specific price.
