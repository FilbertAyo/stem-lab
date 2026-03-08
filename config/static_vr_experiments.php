<?php

use Illuminate\Support\Carbon;

return [
    [
        'slug' => 'passage-of-water',
        'name' => 'Passage of Water',
        'description' => 'Dive into the Passage of Water, a three-part interactive experience that vividly illustrates the reality of our planet\'s freshwater cycle and availability. The experience explores how water moves through rivers, lakes, reservoirs, and rainfall—and why freshwater is such a precious resource. Using NASA data and collaboration with artist Yiyun Kang, you can see how our activities and climate shifts affect water in places like Egypt, Pakistan, and Alaska, and discover how technology can support solutions to secure this vital resource for the future.',
        'image' => 'images/passageofwater.jpg',
        'external_url' => 'https://artsandculture.google.com/experiment/passage-of-water/dAElpEyEjuE9XQ',
        // Optional: set to a URL that opens the simulation directly (e.g. after clicking "Launch" on their site, copy the address bar URL here to skip the launch screen)
        'launch_url' => null,
        'subject_name' => 'Science',
        'created_at' => Carbon::parse('2024-02-22'),
        // Google blocks embedding (403 in iframe). Use false to show image + "Open experience" in new tab only.
        'use_iframe' => false,
    ],
];
