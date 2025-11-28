<?php

namespace App\Services;

use App\Models\User;
use App\Models\Widget;
use App\Services\UtilityService;


class WidgetService
{


    static function getDefaultConfig()
    {


        return [
            "enabled" => true,
            "strings" => [
                "field_required" => "This field is required",
                "send_action" => "Sending...",
                "error_unexpected" => "Unexpected error, try again later",
                "try_again" => "Try again",
                "close" => "Close",
                "file_size_limit_exceeded" => "File size limit exceeded"
            ],
            "tabs" => [
                [
                    "type" => "form",
                    "heading" => [
                        "title" => "Give feedback",
                        "sub_title" => "Share your thoughts & insights",
                        "icon" => "feedback"
                    ],
                    "form" => [
                        "form_id" => "feedback",
                        "strings" => [
                            "button" => "Send"
                        ],
                        "inputs" => [
                            [
                                "placeholder" => "Name",
                                "name" => "name",
                                "type" => "text",
                                "required" => true,
                                "maxlength" => 100,
                                "minlength" => 2
                            ],
                            [
                                "placeholder" => "Email",
                                "name" => "email",
                                "type" => "email",
                                "required" => true,
                                "maxlength" => 254,
                                "minlength" => 5

                            ],
                            [
                                "placeholder" => "Feedback title",
                                "name" => "title",
                                "type" => "text",
                                "required" => false,
                                "maxlength" => 100,
                                "minlength" => 5
                            ],
                            [
                                "placeholder" => "Write your feedback here...",
                                "name" => "text",
                                "type" => "textarea",
                                "required" => true,
                                "minlength" => 5,
                                "maxlength" => 2000,

                            ],
                            [
                                "accept" => [".jpg", ".jpeg", ".png"],
                                "placeholder" => "Attach images",
                                "name" => "images",
                                "type" => "file",
                                "required" => false,
                                "size_limit" => 1048576
                            ]
                        ]
                    ]
                ],
                [
                    "type" => "form",
                    "heading" => [
                        "title" => "Rate your experience",
                        "sub_title" => "Rate your quality level of interaction",
                        "icon" => "star"
                    ],
                    "form" => [
                        "form_id" => "rating",

                        "strings" => [
                            "button" => "Send"
                        ],
                        "inputs" => [
                            [
                                "placeholder" => "Name",
                                "name" => "name",
                                "type" => "text",
                                "required" => true,
                                "maxlength" => 100,
                                "minlength" => 2
                            ],
                            [
                                "placeholder" => "Email",
                                "name" => "email",
                                "type" => "email",
                                "required" => true,
                                "maxlength" => 254,
                                "minlength" => 5

                            ],
                            [
                                "placeholder" => "Stars",
                                "name" => "rating",
                                "type" => "rate",
                                "values" => ["1", "2", "3", "4", "5"],
                                "required" => true
                            ]
                        ]
                    ]
                ],
                [
                    "type" => "form",
                    "heading" => [
                        "title" => "Feature Request",
                        "sub_title" => "Share your heart with us publicly",

                        "icon" => "feature"
                    ],
                    "form" => [
                        "form_id" => "feature_request",

                        "strings" => [
                            "button" => "Send"
                        ],
                        "inputs" => [
                            [
                                "placeholder" => "Name",
                                "name" => "name",
                                "type" => "text",
                                "required" => false,
                                "maxlength" => 100,
                                "minlength" => 2
                            ],
                            [
                                "placeholder" => "Email",
                                "name" => "email",
                                "type" => "email",
                                "required" => false,
                                "maxlength" => 254,
                                "minlength" => 5




                            ],
                            [
                                "placeholder" => "What is the feature you are looking for?",
                                "name" => "text",
                                "type" => "textarea",
                                "required" => true,
                                "minlength" => 5,
                                "maxlength" => 2000,
                            ],
                            [
                                "accept" => [],
                                "placeholder" => "Attach images",
                                "name" => "images",
                                "type" => "file",
                                "required" => false,
                                "size_limit" => 1048576
                            ]
                        ]
                    ]
                ],
                [
                    "type" => "form",

                    "heading" => [
                        "title" => "Report a bug",
                        "sub_title" => "Help us improve with your catches",

                        "icon" => "bug"
                    ],
                    "form" => [
                        "form_id" => "bug_report",

                        "strings" => [
                            "button" => "Send"
                        ],
                        "inputs" => [
                            [
                                "placeholder" => "Name",
                                "name" => "name",
                                "type" => "text",
                                "required" => false,
                                "maxlength" => 100,
                                "minlength" => 2
                            ],
                            [
                                "placeholder" => "Email",
                                "name" => "email",
                                "type" => "email",
                                "required" => false,
                                "maxlength" => 254,
                                "minlength" => 5


                            ],
                            [
                                "placeholder" => "Where is the bug you are experiencing?",
                                "name" => "text",
                                "type" => "textarea",
                                "required" => true,
                                "minlength" => 5,
                                "maxlength" => 2000,
                            ],
                            [
                                "accept" => [],
                                "placeholder" => "Attach images",
                                "name" => "images",
                                "type" => "file",
                                "required" => false,
                                "size_limit" => 1048576
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }



    static function createWidget(User $user)
    {

        return Widget::create([
            'owner_id' => $user->id,
            'code_snippet' => json_encode(self::getDefaultConfig()),
            'config' => '',
            'uuid' => UtilityService::generateUniqueId(Widget::class, 'uuid', 20),
        ]);
    }
}
