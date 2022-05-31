<?php    
    $response = ['success' => false];
    $error = [];

    $required_fields = ['name', 'teacher', 'description', 'group', 'credits'];

    $unset_errors =
    [
        'name' => 'Името на учебния предмет е задължително поле',
        'teacher' => 'Името на преподавателя е задължително поле',
        'description' => 'Описанието е задължително поле',
        'group' => 'Групата е задължително поле',
        'credits' => 'Кредитите са задължително поле'
    ];

    switch($_SERVER['REQUEST_METHOD']) {
        case 'POST':
        {
            foreach($required_fields as $field) {
                if(empty($_POST[$field])) {
                    $error += [$field => $unset_errors[$field]];
                }
            }

            foreach($required_fields as $field) {
                if(array_key_exists($field, $error)) {
                    continue;
                } else {
                    switch($field) {
                        case 'name':
                        {
                            $count = mb_strlen($_POST[$field]);

                            if($count < 2 || $count > 150) {
                                $error += [$field => "Името на учебния предмет трябва да е между 2 и 150 символа, а Вие сте въвели $count."];
                            }
                            break;
                        }
                        case 'teacher':
                        {
                            $count = mb_strlen($_POST[$field]);

                            if ($count < 3 || $count > 200) {
                                $error += [$field => "Името на учителя трябва да е между 3 и 200 сивмола, а Вие сте въвели $count."];
                            }
                            break;
                        }
                        case 'description':
                        {
                            $count = mb_strlen($_POST[$field]);

                            if ($count < 10) {
                                $error += [$field => "Описанието трябва да е поне 10 символа, а Вие сте въвели $count."];
                            }
                            break;
                        }
                        case 'group':
                        {
                            if ($_POST[$field] != 'М' &&
                                $_POST[$field] != 'ПМ' &&
                                $_POST[$field] != 'ОКН' &&
                                $_POST[$field] != 'ЯКН') {
                                $error += [$field => 'Групата трябва да е една от следните възможности: М, ПМ, ОКН, ЯКН. Буквите трябва да са изписани на кирилица.'];
                            }
                            break;
                        }
                        case 'credits':
                        {   
                            if(str_contains($_POST[$field], '.') || str_contains($_POST[$field], ',')) {
                                $error += [$field => "Кредитите трябва да са цяло число."];
                                break;
                            }

                            if (!intval($_POST[$field]) || !is_numeric($_POST[$field])) {
                                $error += [$field => "Кредитите трябва да са цяло ЧИСЛО."];
                                break;
                            }

                            if (intval($_POST[$field]) <= 0) {
                                $error += [$field => "Кредитите трябва да са число по-голямо от 0."];
                                break;
                            }
                        }
                    }
                }
            }

            if(count($error) === 0) {
                $response['success'] = true;
            } else {
                $response += ['errors' => $error];
            }

            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            break;
        }
        default:
        {
            echo "За съжаление не поддържаме тези заявки :(";
            break;
        }
    }
?>