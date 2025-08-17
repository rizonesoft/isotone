<?php
/**
 * AI Model Configuration
 * Complete catalog of AI models with capabilities, pricing, and metadata
 */

return [
    'providers' => [
        'openai' => [
            'name' => 'OpenAI',
            'color' => 'green',
            'api_key_field' => 'openai_api_key',
            'models' => [
                // GPT-5 Family
                'gpt-5-nano' => [
                    'name' => 'GPT-5 Nano',
                    'capabilities' => ['text'],
                    'context_window' => 16384,
                    'max_output' => 4096,
                    'pricing' => [
                        'input' => 0.10,
                        'output' => 0.30,
                        'batch_input' => 0.05,
                        'batch_output' => 0.15
                    ],
                    'suitable_for' => ['simple'],
                    'status' => 'ga'
                ],
                'gpt-5-mini' => [
                    'name' => 'GPT-5 Mini',
                    'capabilities' => ['text'],
                    'context_window' => 32768,
                    'max_output' => 8192,
                    'pricing' => [
                        'input' => 0.25,
                        'output' => 0.75,
                        'batch_input' => 0.125,
                        'batch_output' => 0.375
                    ],
                    'suitable_for' => ['simple', 'standard'],
                    'status' => 'ga'
                ],
                'gpt-5' => [
                    'name' => 'GPT-5',
                    'capabilities' => ['text', 'vision', 'function_calling'],
                    'context_window' => 128000,
                    'max_output' => 16384,
                    'pricing' => [
                        'input' => 3.00,
                        'output' => 9.00,
                        'batch_input' => 1.50,
                        'batch_output' => 4.50
                    ],
                    'suitable_for' => ['standard', 'complex', 'vision'],
                    'status' => 'ga'
                ],
                'gpt-5-turbo' => [
                    'name' => 'GPT-5 Turbo',
                    'capabilities' => ['text', 'vision', 'function_calling'],
                    'context_window' => 128000,
                    'max_output' => 32768,
                    'pricing' => [
                        'input' => 8.00,
                        'output' => 24.00,
                        'batch_input' => 4.00,
                        'batch_output' => 12.00
                    ],
                    'suitable_for' => ['complex'],
                    'status' => 'ga'
                ],
                
                // GPT-4 Family
                'gpt-4.1' => [
                    'name' => 'GPT-4.1',
                    'capabilities' => ['text', 'vision', 'function_calling'],
                    'context_window' => 128000,
                    'max_output' => 16384,
                    'pricing' => [
                        'input' => 2.00,
                        'output' => 6.00,
                        'batch_input' => 1.00,
                        'batch_output' => 3.00
                    ],
                    'suitable_for' => ['standard', 'vision'],
                    'status' => 'ga'
                ],
                'gpt-4o' => [
                    'name' => 'GPT-4o',
                    'capabilities' => ['text', 'vision', 'function_calling'],
                    'context_window' => 128000,
                    'max_output' => 16384,
                    'pricing' => [
                        'input' => 2.50,
                        'output' => 10.00,
                        'batch_input' => 1.25,
                        'batch_output' => 5.00
                    ],
                    'suitable_for' => ['standard', 'vision'],
                    'status' => 'ga'
                ],
                'gpt-4o-mini' => [
                    'name' => 'GPT-4o Mini',
                    'capabilities' => ['text', 'vision', 'function_calling'],
                    'context_window' => 128000,
                    'max_output' => 16384,
                    'pricing' => [
                        'input' => 0.15,
                        'output' => 0.60,
                        'batch_input' => 0.075,
                        'batch_output' => 0.30
                    ],
                    'suitable_for' => ['simple'],
                    'status' => 'ga'
                ],
                'gpt-4-turbo' => [
                    'name' => 'GPT-4 Turbo',
                    'capabilities' => ['text', 'vision', 'function_calling'],
                    'context_window' => 128000,
                    'max_output' => 4096,
                    'pricing' => [
                        'input' => 10.00,
                        'output' => 30.00,
                        'batch_input' => 5.00,
                        'batch_output' => 15.00
                    ],
                    'suitable_for' => ['complex'],
                    'status' => 'ga'
                ],
                
                // O-Series (Reasoning Models)
                'o3' => [
                    'name' => 'o3',
                    'capabilities' => ['text', 'reasoning'],
                    'context_window' => 200000,
                    'max_output' => 100000,
                    'pricing' => [
                        'input' => 20.00,
                        'output' => 80.00
                    ],
                    'suitable_for' => ['complex'],
                    'status' => 'preview'
                ],
                'o3-mini' => [
                    'name' => 'o3-mini',
                    'capabilities' => ['text', 'reasoning'],
                    'context_window' => 128000,
                    'max_output' => 65536,
                    'pricing' => [
                        'input' => 5.00,
                        'output' => 20.00
                    ],
                    'suitable_for' => ['complex'],
                    'status' => 'preview'
                ],
                'o1' => [
                    'name' => 'o1',
                    'capabilities' => ['text', 'reasoning'],
                    'context_window' => 128000,
                    'max_output' => 32768,
                    'pricing' => [
                        'input' => 15.00,
                        'output' => 60.00
                    ],
                    'suitable_for' => ['complex'],
                    'status' => 'ga'
                ],
                'o1-mini' => [
                    'name' => 'o1-mini',
                    'capabilities' => ['text', 'reasoning'],
                    'context_window' => 128000,
                    'max_output' => 65536,
                    'pricing' => [
                        'input' => 3.00,
                        'output' => 12.00
                    ],
                    'suitable_for' => ['complex'],
                    'status' => 'ga'
                ],
                'o4-mini' => [
                    'name' => 'o4-mini',
                    'capabilities' => ['text'],
                    'context_window' => 64000,
                    'max_output' => 16384,
                    'pricing' => [
                        'input' => 0.30,
                        'output' => 1.20
                    ],
                    'suitable_for' => ['simple'],
                    'status' => 'preview'
                ]
            ]
        ],
        
        'anthropic' => [
            'name' => 'Anthropic',
            'color' => 'purple',
            'api_key_field' => 'anthropic_api_key',
            'models' => [
                // Claude 4 Family
                'claude-4-opus' => [
                    'name' => 'Claude 4 Opus',
                    'capabilities' => ['text', 'vision', 'function_calling'],
                    'context_window' => 200000,
                    'max_output' => 4096,
                    'pricing' => [
                        'input' => 12.00,
                        'output' => 60.00,
                        'batch_input' => 6.00,
                        'batch_output' => 30.00
                    ],
                    'suitable_for' => ['complex', 'vision'],
                    'status' => 'preview'
                ],
                
                // Claude 3.5 Family
                'claude-3.5-sonnet-20241022' => [
                    'name' => 'Claude 3.5 Sonnet',
                    'capabilities' => ['text', 'vision', 'function_calling'],
                    'context_window' => 200000,
                    'max_output' => 8192,
                    'pricing' => [
                        'input' => 3.00,
                        'output' => 15.00,
                        'batch_input' => 1.50,
                        'batch_output' => 7.50
                    ],
                    'suitable_for' => ['standard', 'vision'],
                    'status' => 'ga'
                ],
                'claude-3.5-haiku-20241022' => [
                    'name' => 'Claude 3.5 Haiku',
                    'capabilities' => ['text'],
                    'context_window' => 200000,
                    'max_output' => 8192,
                    'pricing' => [
                        'input' => 1.00,
                        'output' => 5.00,
                        'batch_input' => 0.50,
                        'batch_output' => 2.50
                    ],
                    'suitable_for' => ['simple'],
                    'status' => 'ga'
                ],
                
                // Claude 3.7 Family
                'claude-3.7-sonnet' => [
                    'name' => 'Claude 3.7 Sonnet',
                    'capabilities' => ['text', 'vision', 'function_calling'],
                    'context_window' => 200000,
                    'max_output' => 8192,
                    'pricing' => [
                        'input' => 2.00,
                        'output' => 10.00,
                        'batch_input' => 1.00,
                        'batch_output' => 5.00
                    ],
                    'suitable_for' => ['standard', 'vision'],
                    'status' => 'preview'
                ],
                
                // Claude 3 Family
                'claude-3-opus-20240229' => [
                    'name' => 'Claude 3 Opus',
                    'capabilities' => ['text', 'vision', 'function_calling'],
                    'context_window' => 200000,
                    'max_output' => 4096,
                    'pricing' => [
                        'input' => 15.00,
                        'output' => 75.00,
                        'batch_input' => 7.50,
                        'batch_output' => 37.50
                    ],
                    'suitable_for' => ['complex', 'vision'],
                    'status' => 'ga'
                ],
                'claude-3-sonnet-20240229' => [
                    'name' => 'Claude 3 Sonnet',
                    'capabilities' => ['text', 'vision', 'function_calling'],
                    'context_window' => 200000,
                    'max_output' => 4096,
                    'pricing' => [
                        'input' => 3.00,
                        'output' => 15.00,
                        'batch_input' => 1.50,
                        'batch_output' => 7.50
                    ],
                    'suitable_for' => ['standard', 'vision'],
                    'status' => 'ga'
                ],
                'claude-3-haiku-20240307' => [
                    'name' => 'Claude 3 Haiku',
                    'capabilities' => ['text', 'vision'],
                    'context_window' => 200000,
                    'max_output' => 4096,
                    'pricing' => [
                        'input' => 0.25,
                        'output' => 1.25,
                        'batch_input' => 0.125,
                        'batch_output' => 0.625
                    ],
                    'suitable_for' => ['simple'],
                    'status' => 'ga'
                ]
            ]
        ],
        
        'google' => [
            'name' => 'Google AI',
            'color' => 'blue',
            'api_key_field' => 'google_api_key',
            'models' => [
                // Gemini 2.0 Family
                'gemini-2.0-flash-exp' => [
                    'name' => 'Gemini 2.0 Flash',
                    'capabilities' => ['text', 'vision', 'function_calling'],
                    'context_window' => 1048576,
                    'max_output' => 8192,
                    'pricing' => [
                        'input' => 0.00,
                        'output' => 0.00
                    ],
                    'suitable_for' => ['simple', 'standard', 'vision'],
                    'status' => 'experimental'
                ],
                'gemini-2.0-pro' => [
                    'name' => 'Gemini 2.0 Pro',
                    'capabilities' => ['text', 'vision', 'function_calling'],
                    'context_window' => 2097152,
                    'max_output' => 8192,
                    'pricing' => [
                        'input' => 2.00,
                        'output' => 8.00
                    ],
                    'suitable_for' => ['standard', 'complex', 'vision'],
                    'status' => 'preview'
                ],
                
                // Gemini 1.5 Family
                'gemini-1.5-pro-002' => [
                    'name' => 'Gemini 1.5 Pro 002',
                    'capabilities' => ['text', 'vision', 'function_calling'],
                    'context_window' => 2097152,
                    'max_output' => 8192,
                    'pricing' => [
                        'input' => 1.25,
                        'output' => 5.00,
                        'batch_input' => 0.625,
                        'batch_output' => 2.50
                    ],
                    'suitable_for' => ['standard', 'complex', 'vision'],
                    'status' => 'ga'
                ],
                'gemini-1.5-pro' => [
                    'name' => 'Gemini 1.5 Pro',
                    'capabilities' => ['text', 'vision', 'function_calling'],
                    'context_window' => 2097152,
                    'max_output' => 8192,
                    'pricing' => [
                        'input' => 1.25,
                        'output' => 5.00,
                        'batch_input' => 0.625,
                        'batch_output' => 2.50
                    ],
                    'suitable_for' => ['standard', 'vision'],
                    'status' => 'ga'
                ],
                'gemini-1.5-flash' => [
                    'name' => 'Gemini 1.5 Flash',
                    'capabilities' => ['text', 'vision', 'function_calling'],
                    'context_window' => 1048576,
                    'max_output' => 8192,
                    'pricing' => [
                        'input' => 0.075,
                        'output' => 0.30,
                        'batch_input' => 0.0375,
                        'batch_output' => 0.15
                    ],
                    'suitable_for' => ['simple', 'vision'],
                    'status' => 'ga'
                ],
                'gemini-1.5-flash-8b' => [
                    'name' => 'Gemini 1.5 Flash-8B',
                    'capabilities' => ['text', 'vision', 'function_calling'],
                    'context_window' => 1048576,
                    'max_output' => 8192,
                    'pricing' => [
                        'input' => 0.0375,
                        'output' => 0.15,
                        'batch_input' => 0.01875,
                        'batch_output' => 0.075
                    ],
                    'suitable_for' => ['simple'],
                    'status' => 'ga'
                ],
                
                // Gemini Ultra
                'gemini-ultra' => [
                    'name' => 'Gemini Ultra',
                    'capabilities' => ['text', 'vision', 'function_calling', 'reasoning'],
                    'context_window' => 2097152,
                    'max_output' => 32768,
                    'pricing' => [
                        'input' => 10.00,
                        'output' => 40.00
                    ],
                    'suitable_for' => ['complex', 'vision'],
                    'status' => 'coming_soon'
                ]
            ]
        ],
        
        'groq' => [
            'name' => 'Groq',
            'color' => 'orange',
            'api_key_field' => 'groq_api_key',
            'models' => [
                'llama-3.3-70b' => [
                    'name' => 'Llama 3.3 70B',
                    'capabilities' => ['text'],
                    'context_window' => 128000,
                    'max_output' => 8192,
                    'pricing' => [
                        'input' => 0.00,
                        'output' => 0.00
                    ],
                    'suitable_for' => ['simple', 'standard'],
                    'status' => 'ga'
                ],
                'llama-3.1-70b' => [
                    'name' => 'Llama 3.1 70B',
                    'capabilities' => ['text', 'function_calling'],
                    'context_window' => 128000,
                    'max_output' => 8192,
                    'pricing' => [
                        'input' => 0.00,
                        'output' => 0.00
                    ],
                    'suitable_for' => ['simple', 'standard'],
                    'status' => 'ga'
                ],
                'llama-3.1-8b' => [
                    'name' => 'Llama 3.1 8B',
                    'capabilities' => ['text', 'function_calling'],
                    'context_window' => 128000,
                    'max_output' => 8192,
                    'pricing' => [
                        'input' => 0.00,
                        'output' => 0.00
                    ],
                    'suitable_for' => ['simple'],
                    'status' => 'ga'
                ],
                'mixtral-8x7b' => [
                    'name' => 'Mixtral 8x7B',
                    'capabilities' => ['text'],
                    'context_window' => 32768,
                    'max_output' => 8192,
                    'pricing' => [
                        'input' => 0.00,
                        'output' => 0.00
                    ],
                    'suitable_for' => ['simple'],
                    'status' => 'ga'
                ]
            ]
        ]
    ],
    
    'task_categories' => [
        'simple' => [
            'name' => 'Simple Tasks',
            'description' => 'Basic operations like text formatting, simple Q&A, basic summarization',
            'required_capabilities' => ['text'],
            'max_cost_per_million' => 1.00
        ],
        'standard' => [
            'name' => 'Standard Tasks',
            'description' => 'Content generation, complex Q&A, data analysis, code generation',
            'required_capabilities' => ['text'],
            'max_cost_per_million' => 5.00
        ],
        'complex' => [
            'name' => 'Complex Tasks',
            'description' => 'Advanced reasoning, complex code analysis, creative writing, research',
            'required_capabilities' => ['text'],
            'preferred_capabilities' => ['reasoning'],
            'max_cost_per_million' => 25.00
        ],
        'vision' => [
            'name' => 'Vision Tasks',
            'description' => 'Image analysis, OCR, visual question answering, multimodal tasks',
            'required_capabilities' => ['vision'],
            'max_cost_per_million' => 10.00
        ]
    ]
];