<?php

return [
    'required' => 'Поле :attribute обязательно.',
    'string' => 'Поле :attribute должно содержать текст.',
    'email' => 'Укажите корректный адрес электронной почты.',
    'integer' => 'Поле :attribute должно быть целым числом.',
    'numeric' => 'Поле :attribute должно быть числом.',
    'min' => ['numeric' => 'Значение поля :attribute должно быть не меньше :min.', 'string' => 'Поле :attribute должно содержать не менее :min символов.', 'file' => 'Размер файла :attribute должен быть не менее :min КБ.'],
    'max' => ['numeric' => 'Значение поля :attribute не должно превышать :max.', 'string' => 'Поле :attribute не должно превышать :max символов.', 'file' => 'Размер файла :attribute не должен превышать :max КБ.'],
    'confirmed' => 'Подтверждение поля :attribute не совпадает.',
    'unique' => 'Такое значение поля :attribute уже используется.',
    'digits' => 'Поле :attribute должно содержать :digits цифр.',
    'image' => 'Файл :attribute должен быть изображением.',
    'mimes' => 'Файл :attribute должен иметь один из типов: :values.',
    'url' => 'Укажите корректную ссылку для поля :attribute.',
    'boolean' => 'Поле :attribute должно иметь значение да или нет.',
    'in' => 'Выбранное значение для поля :attribute недопустимо.',
    'attributes' => [
        'name' => 'имя', 'email' => 'электронная почта', 'password' => 'пароль',
        'password_confirmation' => 'подтверждение пароля', 'code' => 'код подтверждения',
        'rating' => 'оценка', 'comment' => 'отзыв', 'category' => 'категория',
        'price' => 'цена', 'stock' => 'количество на складе', 'image' => 'изображение',
        'receipt' => 'чек', 'phone' => 'номер телефона', 'address' => 'адрес доставки',
        'card_number' => 'номер карты', 'card_holder' => 'владелец карты',
    ],
];
