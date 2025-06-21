<?php
return [
    'unique'               =>  ':thuộc tính đã tồn tại',
    'accepted'             =>  ':thuộc tính được chấp nhận',
    'active_url'           => ':thuộc tính phải là URL hợp lệ',
    'after'                => ':attribute phải là ngày sau :date',
    'alpha'                => ':attribute phải bao gồm tất cả các ký tự alpha. ',
    'alpha_dash'           =>  ':thuộc tính phải bao gồm tất cả các chữ cái, số, dấu gạch ngang hoặc ký tự gạch dưới',
    'alpha_num'            => ':thuộc tính phải bao gồm tất cả các chữ cái và số',
    'array'                => ':thuộc tính phải là một mảng',
    'before'               => ':attribute phải là ngày trước :date',
    'between'              => [
        'numeric' => ':thuộc tính phải nằm giữa :min và :max',
        'file'    => ':attribute phải nằm giữa :min và :max KB',
        'string'  =>  ':attribute phải nằm giữa :min và :max ký tự',
        'array'   => ':thuộc tính phải nằm giữa :min và :max mục',
    ],
    'boolean'              => 'Ký tự :attribute phải đúng hoặc sai',
    'confirmed'            =>  ':xác nhận phụ thuộc tính không khớp',
    'date'                 => ':thuộc tính phải là một ngày hợp lệ',
    'date_format'          =>  ':thuộc tính không khớp với định dạng đã cho :format',
    'different'            =>  ':thuộc tính phải khác với :khác',
    'digits'               => ':thuộc tính phải là :chữ số bit',
    'digits_between'       => ':thuộc tính phải nằm giữa :min và :max chữ số',
    'email'                =>  ':attribute phải là một địa chỉ email hợp lệ. ',
    'filled'               =>  'Trường :attribute là bắt buộc',
    'exists'               => 'Thuộc tính :đã chọn không hợp lệ',
    'image'                => ':thuộc tính phải là hình ảnh (jpeg, png, bmp hoặc gif)',
    'in'                   => 'Thuộc tính :đã chọn không hợp lệ',
    'integer'              => ':thuộc tính phải là số nguyên',
    'ip'                   =>  ':attribute phải là địa chỉ IP hợp lệ. ',
    'max'                  => [
      'numeric' => 'Độ dài tối đa của :attribute là :max bits',
           'file' => 'Giá trị lớn nhất của :attribute là :max',
           'string' => 'Độ dài tối đa của :attribute là :max ký tự',
           'array' => 'Số :attribute tối đa là :max',
    ],
    'mimes' => 'Loại tập tin của :attribute phải là :values',
       'min' => [
           'numeric' => 'Độ dài tối thiểu của :attribute là :min bits',
           'string' => 'Độ dài tối thiểu của :attribute là :min ký tự',
           'file' => ':kích thước thuộc tính ít nhất phải là:min KB',
           'array' => ':attribute có ít nhất :min item',
    ],
    'not_in' => 'Thuộc tính :đã chọn không hợp lệ',
         'numeric'   => ':thuộc tính phải là số',
         'regex' => ':định dạng thuộc tính không hợp lệ',
         'required' => 'Trường :attribute phải được điền đầy đủ',
         'required_if' => 'Trường :attribute là bắt buộc khi :other là :value',
         'required_with' => 'Trường :attribute là bắt buộc khi :values ​​hiện diện',
         'required_with_all' => 'Trường :attribute là bắt buộc khi có :values',
         'required_without' => 'Trường :attribute là bắt buộc khi :values ​​không có',
         'required_without_all' => 'Trường :attribute là bắt buộc khi không có :value nào',
             'same'   => ':thuộc tính và :khác phải khớp',
    'size'                 => [
      'numeric' => ':attribute phải là :size bits',
         'file' => ':attribute phải là :size KB',
         'string' => ':attribute phải là :size characters',
         'array' => ':attribute phải chứa mục :size',
    ],
    'url' => ':định dạng thuộc tính không hợp lệ',
    'timezone' => ':thuộc tính phải là múi giờ hợp lệ',
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
            'rule-name' => 'thông báo tùy chỉnh',
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
    'attributes'           => [
        'username' =>  'tên người dùng',
        'account'  =>  'tài khoản',
        'captcha'  =>  'Mã xác nhận',
        'mobile'   =>  'số di động',
        'password' =>  'mật khẩu',
        'content'  => 'nội dung',
        'identity' =>  'số điện thoại di động/tên người dùng',
    ],
];
