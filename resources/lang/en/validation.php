<?php
return [
  'unique' => ':attribute already exists',
    'accepted' => ':attribute is accepted',
    'active_url' => ':attribute must be a valid URL',
    'after' => ':attribute must be a date after :date',
    'alpha' => ':attribute must consist of all alpha characters. ',
    'alpha_dash' => ':attribute must consist of all letters, numbers, dashes or underscore characters',
    'alpha_num' => ':attribute must consist of all letters and numbers',
    'array' => ':attribute must be an array',
    'before' => ':attribute must be a date before :date',
    'between' => [
        'numeric' => ':attribute must be between :min and :max',
        'file' => ':attribute must be between :min and :max KB',
        'string' => 'The :attribute must be between :min and :max characters',
        'array' => ':attribute must be between :min and :max items',
    ],
    'boolean' => 'The :attribute character must be true or false',
    'confirmed' => ':attribute secondary confirmation does not match',
    'date' => ':attribute must be a valid date',
    'date_format' => 'The :attribute does not match the given format :format',
    'different' => ':attribute must be different from :other',
    'digits' => ':attribute must be :digits bits',
    'digits_between' => ':attribute must be between :min and :max digits',
    'email' => ':attribute must be a valid email address. ',
    'filled' => 'The :attribute field is required',
    'exists' => 'The selected :attribute is invalid',
    'image' => ':attribute must be an image (jpeg, png, bmp or gif)',
    'in' => 'The selected :attribute is invalid',
    'integer' => ':attribute must be an integer',
    'ip' => ':attribute must be a valid IP address. ',
    'max' => [
        'numeric' => 'The maximum length of an :attribute is :max bits',
        'file' => 'The maximum value of :attribute is :max',
        'string' => 'The maximum length of an :attribute is :max characters',
        'array' => 'The maximum number of :attribute is :max',
    ],
    'mimes' => 'The file type of :attribute must be :values',
    'min' => [
        'numeric' => 'The minimum length of an :attribute is :min bits',
        'string' => 'The minimum length of an :attribute is :min characters',
        'file' => ':attribute size must be at least:min KB',
        'array' => 'The :attribute has at least :min items',
    ],
    'not_in' => 'The selected :attribute is invalid',
    'numeric' => ':attribute must be a number',
    'regex' => ':attribute format is invalid',
    'required' => 'The :attribute field must be filled',
    'required_if' => 'The :attribute field is required when :other is :value',
    'required_with' => 'The :attribute field is required when :values ​​are present',
    'required_with_all' => 'The :attribute field is required when :values ​​are present',
    'required_without' => 'The :attribute field is required when :values ​​is absent',
    'required_without_all' => 'The :attribute field is required when none of the :values ​​are present',
    'same' => ':attribute and :other must match',
    'size' => [
        'numeric' => ':attribute must be :size bits',
        'file' => ':attribute must be :size KB',
        'string' => ':attribute must be :size characters',
        'array' => 'The :attribute must contain the :size item',
    ],
    'url' => ':attribute invalid format',
    'timezone' => ':attribute must be a valid time zone',
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */
    'custom'               => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */
    'attributes' => [
           'username' => 'username',
           'account' => 'account',
           'captcha' => 'Verification code',
           'mobile' => 'mobile number',
           'password' => 'password',
           'content' => 'content',
           'identity' => 'mobile phone number/username',
    ],
];
