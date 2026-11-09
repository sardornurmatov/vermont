<?php

return [
    'required' => 'The :attribute field is required.',
    'string' => 'The :attribute field must be text.',
    'email' => 'Enter a valid email address.',
    'integer' => 'The :attribute field must be an integer.',
    'numeric' => 'The :attribute field must be a number.',
    'min' => ['numeric' => 'The :attribute must be at least :min.', 'string' => 'The :attribute must be at least :min characters.', 'file' => 'The :attribute must be at least :min kilobytes.'],
    'max' => ['numeric' => 'The :attribute may not be greater than :max.', 'string' => 'The :attribute may not be greater than :max characters.', 'file' => 'The :attribute may not be greater than :max kilobytes.'],
    'confirmed' => 'The :attribute confirmation does not match.',
    'unique' => 'The :attribute has already been taken.',
    'digits' => 'The :attribute must be :digits digits.',
    'image' => 'The :attribute must be an image.',
    'mimes' => 'The :attribute must be a file of type: :values.',
    'url' => 'The :attribute must be a valid URL.',
    'boolean' => 'The :attribute field must be true or false.',
    'in' => 'The selected :attribute is invalid.',
    'attributes' => [
        'name' => 'name', 'email' => 'email address', 'password' => 'password',
        'password_confirmation' => 'password confirmation', 'code' => 'verification code',
        'rating' => 'rating', 'comment' => 'review', 'category' => 'category',
        'price' => 'price', 'stock' => 'stock quantity', 'image' => 'image',
        'receipt' => 'receipt', 'phone' => 'phone number', 'address' => 'delivery address',
        'card_number' => 'card number', 'card_holder' => 'cardholder',
    ],
];
