<?php
/**
 * Seed Activities Table with Resort Activities
 * Run this file once to populate activities
 */

require_once '../phpAuthentication/db_config.php';

try {
    // Clear existing activities
    $conn->query("TRUNCATE TABLE activities");
    
    // Insert resort activities
    $activities = [
        [
            'name' => 'Billiards',
            'slug' => 'billiards',
            'description' => 'Premium billiards tables with expert coaching available for all skill levels',
            'price_per_hour' => 25.00,
            'min_duration_hours' => 1.0,
            'max_duration_hours' => 4.0,
            'capacity' => 8,
            'icon' => 'bi-8-circle',
            'image_url' => 'assets/Images/BilliardsHDreal.jpg'
        ],
        [
            'name' => 'Basketball Court',
            'slug' => 'basketball',
            'description' => 'State-of-the-art indoor and outdoor courts for basketball enthusiasts',
            'price_per_hour' => 25.00,
            'min_duration_hours' => 1.0,
            'max_duration_hours' => 4.0,
            'capacity' => 20,
            'icon' => 'bi-basketball',
            'image_url' => 'assets/Images/BasketballCourt.jpg'
        ],
        [
            'name' => 'Swimming Pool',
            'slug' => 'swimming',
            'description' => 'Olympic-sized pools with crystal clear water and professional lifeguards',
            'price_per_hour' => 15.00,
            'min_duration_hours' => 1.0,
            'max_duration_hours' => 4.0,
            'capacity' => 50,
            'icon' => 'bi-water',
            'image_url' => 'assets/Images/Pool.jpg'
        ],
        [
            'name' => 'Fitness Gym',
            'slug' => 'gym',
            'description' => 'Modern equipment with personal trainers and group fitness classes',
            'price_per_hour' => 20.00,
            'min_duration_hours' => 1.0,
            'max_duration_hours' => 3.0,
            'capacity' => 30,
            'icon' => 'bi-heart-pulse',
            'image_url' => 'assets/Images/Gym.jfif'
        ],
        [
            'name' => 'Adventure Rides',
            'slug' => 'adventure',
            'description' => 'Thrilling adventure rides and zip lines for adrenaline seekers',
            'price_per_hour' => 45.00,
            'min_duration_hours' => 2.0,
            'max_duration_hours' => 4.0,
            'capacity' => 10,
            'icon' => 'bi-joystick',
            'image_url' => 'assets/Images/ADV.jpg'
        ],
        [
            'name' => 'Kids Playground',
            'slug' => 'playground',
            'description' => 'Safe and fun playground area with activities for children of all ages',
            'price_per_hour' => 10.00,
            'min_duration_hours' => 1.0,
            'max_duration_hours' => 4.0,
            'capacity' => 25,
            'icon' => 'bi-balloon',
            'image_url' => 'assets/Images/Playground.jpg'
        ]
    ];
    
    $stmt = $conn->prepare("INSERT INTO activities (name, slug, description, price_per_hour, min_duration_hours, max_duration_hours, capacity, icon, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($activities as $activity) {
        $stmt->execute([
            $activity['name'],
            $activity['slug'],
            $activity['description'],
            $activity['price_per_hour'],
            $activity['min_duration_hours'],
            $activity['max_duration_hours'],
            $activity['capacity'],
            $activity['icon'],
            $activity['image_url']
        ]);
    }
    
    echo "✓ Activities seeded successfully!<br>";
    echo "✓ " . count($activities) . " activities added to the database.<br>";
    
} catch (Exception $e) {
    die("Error seeding activities: " . $e->getMessage());
}
?>
