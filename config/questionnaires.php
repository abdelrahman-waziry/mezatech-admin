<?php

return [

    'device_trade_in' => [
        'version' => '1.0',
        'wizard_title' => 'Device Trade-In Assessment',
        'steps' => [
            [
                'step_id' => 1,
                'key' => 'power',
                'title' => 'Power & Functionality',
                'question' => 'Is your device functional and able to turn on?',
                'type' => 'single_choice',
                'required' => true,
                'options' => [
                    ['label' => 'Yes, it works perfectly', 'value' => 'yes'],
                    ['label' => "No, it won't turn on", 'value' => 'no', 'hard_stop' => true],
                ],
            ],
            [
                'step_id' => 2,
                'key' => 'repair_history',
                'title' => 'Repair History',
                'question' => 'Has your device been repaired before?',
                'type' => 'single_choice',
                'required' => true,
                'options' => [
                    ['label' => 'Never repaired', 'value' => 'never'],
                    ['label' => 'Authorised Service', 'value' => 'authorized'],
                    ['label' => 'Local / 3rd Party', 'value' => 'local', 'triggers_parts_selection' => true],
                ],
            ],
            [
                'step_id' => 3,
                'key' => 'battery_health',
                'title' => 'Battery Health',
                'question' => 'What is the maximum capacity of your battery?',
                'type' => 'single_choice',
                'required' => true,
                'options' => [
                    ['label' => '90% or higher', 'value' => '90+'],
                    ['label' => '85% – 89%', 'value' => '85-89'],
                    ['label' => '80% – 84%', 'value' => '80-84'],
                    ['label' => 'Below 80% / Service', 'value' => 'below80'],
                ],
            ],
            [
                'step_id' => 4,
                'key' => 'screen_body',
                'title' => 'Screen & Body',
                'question' => 'Is the screen and back glass completely intact with NO cracks?',
                'type' => 'single_choice_with_conditional_checkboxes',
                'required' => true,
                'options' => [
                    ['label' => 'Yes, perfect condition', 'value' => 'yes'],
                    [
                        'label' => 'No, there are cracks',
                        'value' => 'no',
                        'conditional_checkboxes' => [
                            ['label' => 'Front Screen', 'value' => 'Front Screen'],
                            ['label' => 'Back Glass', 'value' => 'Back Glass'],
                        ],
                    ],
                ],
            ],
            [
                'step_id' => 5,
                'key' => 'cosmetics',
                'title' => 'Cosmetics',
                'question' => 'Are there any scratches on the body or screen?',
                'type' => 'single_choice',
                'required' => true,
                'options' => [
                    ['label' => 'No scratches (Mint)', 'value' => 'none'],
                    ['label' => 'Light scratches', 'value' => 'light'],
                    ['label' => 'Visible / Deep', 'value' => 'visible'],
                ],
            ],
            [
                'step_id' => 6,
                'key' => 'hardware',
                'title' => 'Hardware Check',
                'question' => 'Do all buttons, speakers, and ports work properly?',
                'type' => 'single_choice_with_conditional_checkboxes',
                'required' => true,
                'options' => [
                    ['label' => 'Yes, all working', 'value' => 'yes'],
                    [
                        'label' => 'No, some issues',
                        'value' => 'no',
                        'conditional_checkboxes' => [
                            ['label' => 'Charging Port', 'value' => 'Charging Port'],
                            ['label' => 'Volume Buttons', 'value' => 'Volume Buttons'],
                            ['label' => 'Power Button', 'value' => 'Power Button'],
                            ['label' => 'Earpiece', 'value' => 'Earpiece'],
                            ['label' => 'Loudspeaker', 'value' => 'Loudspeaker'],
                            ['label' => 'Vibration Motor', 'value' => 'Vibration Motor'],
                        ],
                    ],
                ],
            ],
            [
                'step_id' => 7,
                'key' => 'cameras',
                'title' => 'Cameras',
                'question' => 'Test your cameras. Do they work without spots or shaking?',
                'type' => 'single_choice_with_conditional_checkboxes',
                'required' => true,
                'options' => [
                    ['label' => 'Yes, crystal clear', 'value' => 'yes'],
                    [
                        'label' => 'No, I see issues',
                        'value' => 'no',
                        'conditional_checkboxes' => [
                            ['label' => 'Front Camera', 'value' => 'Front Camera'],
                            ['label' => 'Back Camera', 'value' => 'Back Camera'],
                        ],
                    ],
                ],
            ],
            [
                'step_id' => 8,
                'key' => 'connectivity',
                'title' => 'Connectivity',
                'question' => 'Do Wi-Fi, Bluetooth, GPS, and NFC work?',
                'type' => 'single_choice_with_conditional_checkboxes',
                'required' => true,
                'options' => [
                    ['label' => 'Yes, all connect', 'value' => 'yes'],
                    [
                        'label' => 'No, connectivity issues',
                        'value' => 'no',
                        'conditional_checkboxes' => [
                            ['label' => 'Network', 'value' => 'Network'],
                            ['label' => 'Wi-Fi', 'value' => 'WiFi'],
                            ['label' => 'Bluetooth', 'value' => 'Bluetooth'],
                            ['label' => 'NFC', 'value' => 'NFC'],
                            ['label' => 'GPS', 'value' => 'GPS'],
                        ],
                    ],
                ],
            ],
            [
                'step_id' => 9,
                'key' => 'biometrics',
                'title' => 'Biometrics',
                'question' => 'Does Face ID or Fingerprint sensor work?',
                'type' => 'single_choice',
                'required' => true,
                'options' => [
                    ['label' => 'Yes, works fine', 'value' => 'yes'],
                    ['label' => 'No, not working', 'value' => 'no'],
                ],
            ],
        ],
    ],

];
