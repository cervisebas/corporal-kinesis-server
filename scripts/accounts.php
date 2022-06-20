<?php
    include_once 'classes.php';

    class AccountSystem {
        public function create(string $name, string $email, string $password, string $birthday, string $dni, string $phone) {
            try {
                $db = new DBSystem();
                $cryptor = new Cryptor();
                $permissions = new PermissionsSystem();
                $processPassword = $cryptor->password(base64_decode($password));
                $verifyEmail = $this->verifyEmail($email);
                if (is_object($verifyEmail)) return $verifyEmail;
                if (!$verifyEmail) return array('ok' => false, 'cause' => 'El Email utilizado ya esta registrado en otro perfil.');
                $findAccount = $this->findAccount($dni);
                if ($findAccount !== -1) {
                    $consult = $db->Query("UPDATE `accounts` SET `name`='$name',`email`='$email',`password`='$processPassword',`birthday`='$birthday',`phone`='$phone', `type`='1' WHERE `id`=$findAccount");
                    if ($consult) {
                        $permissions->setNew($findAccount);
                        return array('ok' => true, 'cause' => '');
                    } else {
                        return array('ok' => false, 'cause' => 'Ocurrió un error durante la creación del perfil.');
                    }
                    return;                    
                }
                $consult = $db->QueryAndConect("INSERT INTO `accounts`(`id`, `name`, `email`, `password`, `birthday`, `dni`, `phone`, `experience`, `image`, `type`) VALUES (NULL,'$name','$email','$processPassword','$birthday','$dni','$phone','U2luIGRhdG9z', 'ZGVmYXVsdC5qcGc=', '1')");
                if ($consult['exec']) {
                    $permissions->setNew($consult['connection']->insert_id);
                    return array('ok' => true, 'cause' => '');
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error durante la creación del perfil.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function createAdmin(string $idDeclare, string $name, string $dni, string $birthday) {
            try {
                $permission = new PermissionsSystem();
                $verifyAccount = $permission->verifyAccount($idDeclare, 2);
                if (is_object($verifyAccount)) return $verifyAccount;
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                $db = new DBSystem();
                $cryptor = new Cryptor();
                $processPassword = $cryptor->password(base64_decode('-'));
                $email = base64_encode('-');
                $phone = base64_encode('-');
                $consult = $db->QueryAndConect("INSERT INTO `accounts`(`id`, `name`, `email`, `password`, `birthday`, `dni`, `phone`, `experience`, `image`, `type`) VALUES (NULL,'$name','$email','$processPassword','$birthday','$dni','$phone','U2luIGRhdG9z', 'ZGVmYXVsdC5qcGc=', '0')");
                if ($consult) {
                    return array('ok' => true, 'cause' => '');
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error durante la creación del perfil.');
                }
                
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function open(string $email, string $password) {
            try {
                $db = new DBSystem();
                $verifyEmail = $this->verifyEmail($email);
                if (is_object($verifyEmail)) return $verifyEmail;
                if ($verifyEmail) return array('ok' => false, 'cause' => 'No se encontró el Email especificado.');
                $consult = $db->Query("SELECT * FROM `accounts` WHERE `email`='$email'");
                if ($consult) {
                    $arrayUser = $consult->fetch_array();
                    if (password_verify(base64_decode($password), $arrayUser['password'])) {
                        $data = array(
                            'ok' => true,
                            'cause' => '',
                            'datas' => array(
                                'name' => $arrayUser['name'],
                                'email' => $arrayUser['email'],
                                'password' => $password,
                                'image' => $arrayUser['image']
                            )
                        );
                        return $data;
                    } else {
                        return array('ok' => false, 'cause' => 'La contraseña colocada es incorrecta.');
                    }
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                }
                
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function modifyData($idAccount, string $dataModify, string $valueModify) {
            try {
                $db = new DBSystem();
                $consult = $db->Query("UPDATE `accounts` SET `$dataModify`='$valueModify' WHERE `id`=$idAccount");
                if ($consult) {
                    return array('ok' => true, 'cause' => '');
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function modifyImage($idAccount, $file) {
            try {
                $fileSystem = new FileSystem();
                $fileName = $fileSystem->imageAccount($file);
                if (is_object($fileName)) return $fileName;
                $modify = $this->modifyData($idAccount, 'image', base64_encode($fileName));
                return $modify;
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function modify($idAccount, string $name, string $birthday, string $dni, string $phone) {
            try {
                $db = new DBSystem();
                $verifyData = new VerifyData();
                $fileSystem = new FileSystem();
                $edit = "";
                (!$verifyData->is_empty($name)) && $edit = $edit."`name`='$name'";
                (!$verifyData->is_empty($dni)) && $edit = $edit.((strlen($edit) != 0)? ",": "")."`dni`='$dni'";
                (!$verifyData->is_empty($phone)) && $edit = $edit.((strlen($edit) != 0)? ",": "")."`phone`='$phone'";
                (!$verifyData->is_empty($birthday)) && $edit = $edit.((strlen($edit) != 0)? ",": "")."`birthday`='$birthday'";
                if ($verifyData->issetFilePost('image')) {
                    $image = base64_encode($fileSystem->createAccountImage($_FILES['image']));
                    $edit = $edit.((strlen($edit) != 0)? ",": "")."`image`='$image'";
                }
                $consult = $db->Query("UPDATE `accounts` SET $edit WHERE `id`=$idAccount");
                if ($consult) {
                    return array('ok' => true, 'cause' => '');
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function getAccountInfo($idAccount) {
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `accounts` WHERE `id`=$idAccount");
                if ($consult) {
                    $datas = $consult->fetch_array();
                    return array(
                        'ok' => true,
                        'cause' => '',
                        'datas' => array(
                            'id' => $datas['id'],
                            'name' => $datas['name'],
                            'email' => $datas['email'],
                            'birthday' => $datas['birthday'],
                            'dni' => $datas['dni'],
                            'phone' => $datas['phone'],
                            'experience' => $datas['experience'],
                            'image' => $datas['image'],
                            'type' => $datas['type'] 
                        )
                    );
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function getIdUser($email, $password) {
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `accounts` WHERE `email`='$email'");
                if ($consult) {
                    if ($consult->num_rows == 0) return array('ok' => false, 'cause' => 'No se encontró el usuario dado.');
                    $arrayData = $consult->fetch_array();
                    if (password_verify(base64_decode($password), $arrayData['password'])) {
                        return $arrayData['id'];
                    } else {
                        return array('ok' => false, 'cause' => 'La contraseña dada es incorrecta.', 'leave' => true);
                    }
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }

        public function verifyEmail(string $email) {
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `accounts` WHERE `email`='$email'");
                if ($consult) {
                    return ($consult->num_rows == 0)? true: false;
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }

        public function findAccount(string $dni) {
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `accounts` WHERE `dni`='$dni' AND `type`='0'");
                if ($consult) {
                    if ($consult->num_rows !== 0) {
                        $dataUser = $consult->fetch_array();
                        return intval($dataUser['id']);
                    } else {
                        return -1;
                    }
                } else {
                    return -1;
                }
            } catch (\Throwable $th) {
                return -1;
            }
        }

        public function listAccounts($idDeclare) {
            try {
                $db = new DBSystem();
                $permission = new PermissionsSystem();
                $verifyAccount = $permission->verifyAccount($idDeclare, 1);
                if (is_object($verifyAccount)) return $verifyAccount;
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                $consult = $db->Query("SELECT * FROM `accounts`");
                if ($consult) {
                    $arrayUsers = array();
				    while ($user = $consult->fetch_array()) {
                        $type_permission = $permission->get($user['id']);
                        array_push($arrayUsers, array(
                            'id' => $user['id'],
                            'name' => $user['name'],
                            'experience' => $user['experience'],
                            'email' => $user['email'],
                            'image' => $user['image'],
                            'permission' => $type_permission['permission']
                        ));
                    }
                    return array('ok' => true, 'cause' => '', 'data' => $arrayUsers);
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }

        public function system_getDataUser($idUser) {
            try {
                $db = new DBSystem();
                $trainings = new TrainingSystem();
                $consult = $db->Query("SELECT * FROM `accounts` WHERE `id`=$idUser");
                if ($consult) {
				    $user = $consult->fetch_array();
                    $num_trainings = $trainings->system_gerNumbersOfTrainings($idUser);
                    if (is_object($num_trainings)) return $num_trainings;
                    return array('ok' => true, 'cause' => '', 'data' => array(
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'birthday' => $user['birthday'],
                        'dni' => $user['dni'],
                        'phone' => $user['phone'],
                        'experience' => $user['experience'],
                        'image' => $user['image'],
                        'type' => $user['type'],
                        'num_trainings' => $num_trainings
                    ));
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }

        public function getAdminDataUser($idDeclare, $idUser) {
            try {
                $db = new DBSystem();
                $permission = new PermissionsSystem();
                $trainings = new TrainingSystem();
                $verifyAccount = $permission->verifyAccount($idDeclare, 1);
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                $consult = $db->Query("SELECT * FROM `accounts` WHERE `id`=$idUser");
                if ($consult) {
				    $user = $consult->fetch_array();
                    $num_trainings = $trainings->system_gerNumbersOfTrainings($idUser);
                    if (is_object($num_trainings)) return $num_trainings;
                    return array('ok' => true, 'cause' => '', 'data' => array(
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'birthday' => $user['birthday'],
                        'dni' => $user['dni'],
                        'phone' => $user['phone'],
                        'experience' => $user['experience'],
                        'image' => $user['image'],
                        'type' => $user['type'],
                        'num_trainings' => $num_trainings
                    ));
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function deleteAccountAdmin(string $idDeclare, string $idAccount) {
            try {
                $db = new DBSystem();
                $permission = new PermissionsSystem();
                $trainings = new TrainingSystem();
                $comments = new CommentsSystem();
                $verifyAccount = $permission->verifyAccount($idDeclare, 3);
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');

                $getPermission1 = $permission->get($idDeclare);
                $getPermission2 = $permission->get($idAccount);

                if (!$getPermission1['ok'] || !$getPermission2['ok']) return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                if ((int) $getPermission1['permission'] <= (int) $getPermission2['permission']) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');

                $deletePermissions = $permission->deletePermissions($idAccount);
                if (is_object($deletePermissions)) return $deletePermissions;
                $deleteTrainings = $trainings->deleteTrainingsAdmin($idAccount);
                if (is_object($deleteTrainings)) return $deleteTrainings;
                $deleteComments = $comments->deleteCommentsAdmin($idAccount);
                if (is_object($deleteComments)) return $deleteComments;
                
                $consult = $db->Query("SELECT * FROM `accounts` WHERE `id`=$idAccount");
                if ($consult) {
                    $dataUser = $consult->fetch_array();
                    $imageUser = base64_decode($dataUser['image']);
                    if ($imageUser !== 'default.jpg') unlink("../images/accounts/$imageUser");
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                }

                $consult = $db->Query("DELETE FROM `accounts` WHERE `id`=$idAccount");
                if ($consult) {
                    return array('ok' => true, 'cause' => '');
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
    }
    
?>