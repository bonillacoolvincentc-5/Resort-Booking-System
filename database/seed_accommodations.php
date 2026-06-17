<?php
/**
 * Seed Accommodations Table with Resort Accommodations
 * Run this file once to populate accommodations
 */

require_once '../phpAuthentication/db_config.php';

try {
    // Clear existing accommodations
    $conn->query("TRUNCATE TABLE accommodations");
    
    // Insert resort accommodations
    $accommodations = [
        [
            'name' => 'Standard Room',
            'type' => 'room',
            'description' => 'Comfortable standard room with basic amenities',
            'capacity' => 2,
            'price_per_night' => 1500.00,
            'price_per_day' => 1500.00,
            'amenities' => 'Air conditioning, TV, WiFi, Private bathroom',
            'image_url' => 'assets/Images/Room1.jpg'
        ],
        [
            'name' => 'Deluxe Room',
            'type' => 'room',
            'description' => 'Spacious deluxe room with premium amenities',
            'capacity' => 3,
            'price_per_night' => 2500.00,
            'price_per_day' => 2500.00,
            'amenities' => 'Air conditioning, TV, WiFi, Private bathroom, Mini bar, Balcony',
            'image_url' => 'assets/Images/Room2.jpg'
        ],
        [
            'name' => 'Family Suite',
            'type' => 'room',
            'description' => 'Large suite perfect for families',
            'capacity' => 6,
            'price_per_night' => 4500.00,
            'price_per_day' => 4500.00,
            'amenities' => '2 bedrooms, Living room, Kitchen, Air conditioning, TV, WiFi, Private bathroom',
            'image_url' => 'assets/Images/Suite.jpg'
        ],
        [
            'name' => 'Small Cottage',
            'type' => 'cottage',
            'description' => 'Cozy cottage for intimate getaways',
            'capacity' => 4,
            'price_per_night' => 2000.00,
            'price_per_day' => 2000.00,
            'amenities' => 'Bedroom, Kitchenette, Living area, BBQ grill, Garden view',
            'image_url' => 'assets/Images/Cottage1.jpg'
        ],
        [
            'name' => 'Large Cottage',
            'type' => 'cottage',
            'description' => 'Spacious cottage for groups and families',
            'capacity' => 8,
            'price_per_night' => 3500.00,
            'price_per_day' => 3500.00,
            'amenities' => '2 bedrooms, Kitchen, Living room, Dining area, BBQ grill, Garden view',
            'image_url' => 'assets/Images/Cottage2.jpg'
        ],
        [
            'name' => 'VIP Stay-In Villa',
            'type' => 'stay-in',
            'description' => 'Luxury villa with exclusive amenities',
            'capacity' => 10,
            'price_per_night' => 8000.00,
            'price_per_day' => 8000.00,
            'amenities' => '4 bedrooms, Private pool, Full kitchen, Living room, Dining area, BBQ grill, Garden view, Personal butler service',
            'image_url' => 'assets/Images/Villa.jpg'
        ]
    ];
    
    $stmt = $conn->prepare("INSERT INTO accommodations (name, type, description, capacity, price_per_night, price_per_day, amenities, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($accommodations as $accommodation) {
        $stmt->execute([
            $accommodation['name'],
            $accommodation['type'],
            $accommodation['description'],
            $accommodation['capacity'],
            $accommodation['price_per_night'],
            $accommodation['price_per_day'],
            $accommodation['amenities'],
            $accommodation['image_url']
        ]);
    }
    
    echo "✓ Accommodations seeded successfully!<br>";
    echo "✓ " . count($accommodations) . " accommodations added to the database.<br>";
    
} catch (Exception $e) {
    die("Error seeding accommodations: " . $e->getMessage());
}
?>
