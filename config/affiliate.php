<?php

return [
    'new_rates' => [
        ['min' => 0,  'max' => 25000,  'rate' => 0.15],  
        ['min' => 25001,  'max' => 50000, 'rate' => 0.18],  
        ['min' => 50001, 'max' => 100000, 'rate' => 0.20],
        ['min' => 100001, 'max' => 200000, 'rate' => 0.22],
        ['min' => 200001, 'max' => 99999999999, 'rate' => 0.25],
    ],
    // tier thresholds = number of recurring clients -> rate
    'tiers' => [
        ['min' => 1,  'max' => 5,  'rate' => 0.05],  
        ['min' => 6,  'max' => 10, 'rate' => 0.07],  
        ['min' => 11, 'max' => 30, 'rate' => 0.08],
        ['min' => 31, 'max' =>99999, 'rate' => 0.10],
    ],
    'min_withdraw_amount' => 3500.00, // example min withdraw
];