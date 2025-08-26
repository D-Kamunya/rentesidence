<?php

return [
    'new_rates' => [
        ['min' => 1,  'max' => 5,  'rate' => 0.05],  
        ['min' => 6,  'max' => 10, 'rate' => 0.07],  
        ['min' => 11, 'max' => 99999, 'rate' => 0.10]
    ],
    // tier thresholds = number of recurring clients -> rate
    'tiers' => [
        ['min' => 1,  'max' => 5,  'rate' => 0.05],  // 1-5 recurring clients => 5%
        ['min' => 6,  'max' => 10, 'rate' => 0.07],  // 6-10 => 7%
        ['min' => 11, 'max' => 99999, 'rate' => 0.10]// 11+ => 10%
    ],
    'min_withdraw_amount' => 1000.00, // example min withdraw
];