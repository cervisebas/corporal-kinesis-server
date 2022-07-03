<?php
    error_reporting(E_ERROR | E_PARSE);
    $headers = apache_request_headers();
    if (isset($headers['Authorization']) || isset($headers['authorization'])) {
        $autorization = (isset($headers['Authorization'])) ? $headers['Authorization'] : $headers['authorization'] ;
        if (base64_decode($autorization) == 'pFQVXt&yC%aa8e-^&cY4FRtXm&s87$6%3+6D+REGK4bQNLeY') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST');
            header("Access-Control-Allow-Headers: X-Requested-With");
        } else return http_response_code(403);
    } else return http_response_code(403);
    //echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    include_once 'scripts/classes.php';

    header('Content-type: application/json');

    $accounts = new AccountSystem();
    $permissions = new PermissionsSystem();
    $turns = new TurnsSystem();
    $trainings = new TrainingSystem();
    $comments = new CommentsSystem();
    $verifyData = new VerifyData();
    $exercise = new ExerciseSystem();
    $notifications = new NotificationSystem();

    // Accounts
    if (isset($_POST['createAccount'])) {
        if ($verifyData->issetDataPost(array('name', 'email', 'password', 'birthday', 'dni', 'phone'))) {
            $create = $accounts->create($_POST['name'], $_POST['email'], $_POST['password'], $_POST['birthday'], $_POST['dni'], $_POST['phone']);
            echo json_encode($create);
        } else {
            echo json_encode(array('ok' => false, 'cause' => 'Faltan datos a ingresar.'));
        }
        return;
    }
    if (isset($_POST['createAccountForAdmin'])) {
        if ($verifyData->issetDataPost(array('emailAdmin', 'passwordAdmin', 'name', 'dni', 'birthday'))) {
            $accountId = $accounts->getIdUser($_POST['emailAdmin'], $_POST['passwordAdmin']);
            if (is_object($accountId)) {
                echo json_encode($accountId);
                return;
            }
            $create = $accounts->createAdmin($accountId, $_POST['name'], $_POST['dni'], $_POST['birthday']);
            echo json_encode($create);
        } else {
            echo json_encode(array('ok' => false, 'cause' => 'Faltan datos a ingresar.'));
        }
        return;
    }
    if (isset($_POST['openAccount'])) {
        if ($verifyData->issetDataPost(array('email', 'password'))) {
            if (isset($_POST['updateToken'])) {
                if ($verifyData->issetDataPost(array('deviceToken'))) {
                    $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
                    $notifications->suscribe($accountId, $_POST['deviceToken']);
                }
            }
            $open = $accounts->open($_POST['email'], $_POST['password']);
            echo json_encode($open);
        } else {
            echo json_encode(array('ok' => false, 'cause' => 'Faltan datos a ingresar.'));
        }
        return;
    }
    if (isset($_POST['modifyAccount'])) {
        if ($verifyData->issetDataPost(array('email', 'password', 'dataModify', 'valueModify'))) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            if (is_object($accountId)) {
                echo json_encode($accountId);
                return;
            }
            $modify = $accounts->modifyData($accountId, base64_decode($_POST['dataModify']), $_POST['valueModify']);
            echo json_encode($modify);
        } else {
            echo json_encode(array('ok' => false, 'cause' => 'Faltan datos a ingresar.'));
        }
        return;
    }
    if (isset($_POST['modifyImageAccount'])) {
        if ($verifyData->issetFilePost('image') && $verifyData->issetDataPost(array('email', 'password'))) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            if (is_object($accountId)) {
                echo json_encode($accountId);
                return;
            }
            $modify = $accounts->modifyImage($accountId, $_FILES['image']);
            echo json_encode($modify);
        } else {
            echo json_encode(array('ok' => false, 'cause' => 'Faltan datos a ingresar.'));
        }
        return;
    }
    if (isset($_POST['getListUsers'])) {
        if ($verifyData->issetDataPost(array('email', 'password'))) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            if (is_object($accountId)) {
                echo json_encode($accountId);
                return;
            }
            $list = $accounts->listAccounts($accountId);
            echo json_encode($list);
        } else {
            echo json_encode(array('ok' => false, 'cause' => 'Faltan datos a ingresar.'));
        }
        return;
    }
    if (isset($_POST['getDataUser'])) {
        if ($verifyData->issetDataPost(array('email', 'password', 'idUser'))) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            if (is_object($accountId)) {
                echo json_encode($accountId);
                return;
            }
            $dataUser = $accounts->getAdminDataUser($accountId, $_POST['idUser']);
            echo json_encode($dataUser);
        } else {
            echo json_encode(array('ok' => false, 'cause' => 'Faltan datos a ingresar.'));
        }
        return;
    }
    if (isset($_POST['deleteUserAdmin'])) {
        if ($verifyData->issetDataPost(array('email', 'password', 'idAccount'))) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            if (is_object($accountId)) {
                echo json_encode($accountId);
                return;
            }
            $deleteUser = $accounts->deleteAccountAdmin($accountId, $_POST['idAccount']);
            echo json_encode($deleteUser);
        } else {
            $received = 0;
            if (isset($_POST['email'])) $received += 1;
            if (isset($_POST['password'])) $received += 1;
            if (isset($_POST['idAccount'])) $received += 1;
            echo json_encode(array('ok' => false, 'cause' => "Faltan datos a ingresar. ($received/3 recibidos)"));
        }
        return;
    }
    if (isset($_POST['editUser'])) {
        if ($verifyData->issetDataPost(array('email', 'password')) && $verifyData->issetPosts(array('name', 'dni', 'birthday', 'phone'))) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            if (is_object($accountId)) {
                echo json_encode($accountId);
                return;
            }
            $edit = $accounts->modify($accountId, $_POST['name'], $_POST['birthday'], $_POST['dni'], $_POST['phone']);
            echo json_encode($edit);
            return;
        } else {
            $received = 0;
            if (isset($_POST['email'])) $received += 1;
            if (isset($_POST['password'])) $received += 1;
            echo json_encode(array('ok' => false, 'cause' => "Faltan datos a ingresar. ($received/2 recibidos)"));
        }
        return;
    }
    if (isset($_POST['getInfoAccount'])) {
        if ($verifyData->issetDataPost(array('email', 'password'))) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            if (is_object($accountId)) {
                echo json_encode($accountId);
                return;
            }
            $get = $accounts->getAccountInfo($accountId);
            echo json_encode($get);
            return;
        } else {
            $received = 0;
            if (isset($_POST['email'])) $received += 1;
            if (isset($_POST['password'])) $received += 1;
            echo json_encode(array('ok' => false, 'cause' => "Faltan datos a ingresar. ($received/2 recibidos)"));
        }
        return;
    }


    // Training
    if (isset($_POST['getAllTraining'])) {
        if ($verifyData->issetDataPost(array('email', 'password'))) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            if (is_object($accountId)) {
                echo json_encode($accountId);
                return;
            }
            $get = $trainings->getAll($accountId);
            echo json_encode($get);
        } else {
            echo json_encode(array('ok' => false, 'cause' => 'Faltan datos a ingresar.'));
        }
        return;
    }
    if (isset($_POST['setTraining'])) {
        if ($verifyData->issetDataPost(array('email', 'password', 'idUser', 'idExercise', 'date', 'rds', 'rpe', 'pulse', 'repetitions', 'kilage', 'tonnage'))) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            if (is_object($accountId)) {
                echo json_encode($accountId);
                return;
            }
            $set = $trainings->create($accountId, $_POST['idUser'], $_POST['idExercise'], $_POST['date'], base64_encode('0'), $_POST['rds'], $_POST['rpe'], $_POST['pulse'], $_POST['repetitions'], $_POST['kilage'], $_POST['tonnage']);
            echo json_encode($set);
        } else {
            echo json_encode(array('ok' => false, 'cause' => 'Faltan datos a ingresar.'));
        }
        return;
    }
    if (isset($_POST['adminGetAllTrainigs'])) {
        if ($verifyData->issetDataPost(array('email', 'password', 'idAccount'))) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            if (is_object($accountId)) {
                echo json_encode($accountId);
                return;
            }
            $get = $trainings->admin_getAllUser($accountId, $_POST['idAccount']);
            echo json_encode($get);
        } else {
            echo json_encode(array('ok' => false, 'cause' => 'Faltan datos a ingresar.'));
        }
        return;
    }
    if (isset($_POST['adminDeleteTraining'])) {
        if ($verifyData->issetDataPost(array('email', 'password', 'idTraining'))) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            if (is_object($accountId)) {
                echo json_encode($accountId);
                return;
            }
            $delete = $trainings->admin_delete($accountId, $_POST['idTraining']);
            echo json_encode($delete);
        } else {
            echo json_encode(array('ok' => false, 'cause' => 'Faltan datos a ingresar.'));
        }
        return;
    }

    // Comments
    if (isset($_POST['getAllComments'])) {
        if ($verifyData->issetDataPost(array('email', 'password'))) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            if (is_object($accountId)) {
                echo json_encode($accountId);
                return;
            }
            $get = $comments->getAll($accountId);
            echo json_encode($get);
        } else {
            echo json_encode(array('ok' => false, 'cause' => 'Faltan datos a ingresar.'));    
        }
        return;
    }
    if (isset($_POST['setNewComment'])) {
        if ($verifyData->issetDataPost(array('email', 'password', 'idAccount', 'comment', 'date'))) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            if (is_object($accountId)) {
                echo json_encode($accountId);
                return;
            }
            $training = (isset($_POST['idTraining']))?  $_POST['idTraining']: '-1';
            $set = $comments->create($accountId, $training, $_POST['idAccount'], $_POST['comment'], $_POST['date']);
            echo json_encode($set);
        } else {
            $received = 0;
            if (isset($_POST['email'])) $received += 1;
            if (isset($_POST['password'])) $received += 1;
            if (isset($_POST['idAccount'])) $received += 1;
            if (isset($_POST['comment'])) $received += 1;
            if (isset($_POST['date'])) $received += 1;
            echo json_encode(array('ok' => false, 'cause' => "Faltan datos a ingresar. ($received/5 recibidos)")); 
        }
        return;
    }
    if (isset($_POST['getAdminAllComments'])) {
        if ($verifyData->issetDataPost(array('email', 'password')) && isset($_POST['idAccount'])) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            if (is_object($accountId)) {
                echo json_encode($accountId);
                return;
            }
            $get = $comments->admin_getAll($accountId, $_POST['idAccount']);
            echo json_encode($get);
        } else {
            $received = 0;
            if (isset($_POST['email'])) $received += 1;
            if (isset($_POST['password'])) $received += 1;
            if (isset($_POST['idAccount'])) $received += 1;
            echo json_encode(array('ok' => false, 'cause' => "Faltan datos a ingresar. ($received/3 recibidos)")); 
        }
        return;
    }
    if (isset($_POST['deleteAdminComment'])) {
        if ($verifyData->issetDataPost(array('email', 'password')) && isset($_POST['idComment'])) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            if (is_object($accountId)) {
                echo json_encode($accountId);
                return;
            }
            $delete = $comments->admin_deleteComment($accountId, $_POST['idComment']);
            echo json_encode($delete);
        } else {
            $received = 0;
            if (isset($_POST['email'])) $received += 1;
            if (isset($_POST['password'])) $received += 1;
            if (isset($_POST['idComment'])) $received += 1;
            echo json_encode(array('ok' => false, 'cause' => "Faltan datos a ingresar. ($received/3 recibidos)")); 
        }
        return;
    }
    if (isset($_POST['editAdminComment'])) {
        if ($verifyData->issetDataPost(array('email', 'password', 'comment', 'date')) && isset($_POST['idComment'])) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            if (is_object($accountId)) {
                echo json_encode($accountId);
                return;
            }
            $edit = $comments->admin_editComment($accountId, $_POST['idComment'], $_POST['comment'], $_POST['date']);
            echo json_encode($edit);
        } else {
            $received = 0;
            if (isset($_POST['email'])) $received += 1;
            if (isset($_POST['password'])) $received += 1;
            if (isset($_POST['idComment'])) $received += 1;
            if (isset($_POST['comment'])) $received += 1;
            echo json_encode(array('ok' => false, 'cause' => "Faltan datos a ingresar. ($received/4 recibidos)")); 
        }
        return;
    }

    // Exercises
    if (isset($_POST['setExercise'])) {
        if ($verifyData->issetDataPost(array('email', 'password', 'name', 'description'))) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            $set = $exercise->create($accountId, $_POST['name'], $_POST['description']);
            echo json_encode($set);
        } else {
            echo json_encode(array('ok' => false, 'cause' => 'Faltan datos a ingresar.'));    
        }
        return;
    }
    if (isset($_POST['deleteExercise'])) {
        if ($verifyData->issetDataPost(array('email', 'password')) && isset($_POST['idExercise'])) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            $delete = $exercise->delete($accountId, $_POST['idExercise']);
            echo json_encode($delete);
        } else {
            echo json_encode(array('ok' => false, 'cause' => 'Faltan datos a ingresar.'));    
        }
        return;
    }
    if (isset($_POST['editExercise'])) {
        if ($verifyData->issetDataPost(array('email', 'password', 'name', 'description')) && isset($_POST['idExercise'])) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            $edit = $exercise->edit($accountId, $_POST['idExercise'], $_POST['name'], $_POST['description']);
            echo json_encode($edit);
        } else {
            echo json_encode(array('ok' => false, 'cause' => 'Faltan datos a ingresar.'));    
        }
        return;
    }
    if (isset($_POST['getExercises'])) {
        if ($verifyData->issetDataPost(array('email', 'password'))) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            $get = $exercise->show($accountId);
            echo json_encode($get);
        } else {
            echo json_encode(array('ok' => false, 'cause' => 'Faltan datos a ingresar.'));    
        }
        return;
    }
    

    // Permissions
    if (isset($_POST['getPermission'])) {
        if ($verifyData->issetDataPost(array('email', 'password'))) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            $get = $permissions->get($accountId);
            echo json_encode($get);
        } else {
            echo json_encode(array('ok' => false, 'cause' => 'Faltan datos a ingresar.'));    
        }
        return;
    }
    if (isset($_POST['getAllPermission'])) {
        if ($verifyData->issetDataPost(array('email', 'password'))) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            $get = $permissions->getListAdmins($accountId);
            echo json_encode($get);
        } else {
            echo json_encode(array('ok' => false, 'cause' => 'Faltan datos a ingresar.'));    
        }
        return;
    }
    if (isset($_POST['updatePermission'])) {
        if ($verifyData->issetDataPost(array('email', 'password', 'idDest')) && isset($_POST['permission'])) {
            $accountId = $accounts->getIdUser($_POST['email'], $_POST['password']);
            $update = $permissions->update($accountId, $_POST['idDest'], $_POST['permission']);
            echo json_encode($update);
        } else {
            $received = 0;
            if (isset($_POST['email'])) $received += 1;
            if (isset($_POST['password'])) $received += 1;
            if (isset($_POST['idDest'])) $received += 1;
            if (isset($_POST['permission'])) $received += 1;
            echo json_encode(array('ok' => false, 'cause' => "Faltan datos a ingresar. ($received/4 recibidos)")); 
        }
        return;
    }
?>